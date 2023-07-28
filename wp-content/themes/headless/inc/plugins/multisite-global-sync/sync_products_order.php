<?php

class Product_Order
{

    public function __construct()
    {
        add_action('admin_footer', array($this, 'add_sync_button'));
        add_action('wp_ajax_product_order_sync', array($this, 'handle_sync_request')); // Add this line
    }

    // Add the handle_sync_request function to handle the AJAX request
    public function handle_sync_request()
    {
        if (check_ajax_referer('product_order_sync', 'security', false)) {
            self::sync();
            wp_send_json_success();
        } else {
            wp_send_json_error('Invalid nonce');
        }
        wp_die();
    }

    public function add_sync_button()
    {
        $screen = get_current_screen();
        if ($screen->id !== 'edit-product' or get_locale() != 'en_US') {
            return;
        }

?>
        <script type="text/javascript">
            (function($) {
                $(document).ready(function() {
                    $(".tablenav.top .actions:first-child").append('<button id="sync-products-order" style="float: inline-end; margin-top: 5px; margin-left: 5px;" type="button" class="button button-primary"><span id="sync-spinner"></span>Sync All sites Products Order</button>');
                    let syncButton = $('#sync-products-order');

                    syncButton.on('click', function(e) {

                        e.preventDefault();
                        $("#sync-spinner").addClass("is-active");

                        // Send an AJAX request to trigger the sync() function
                        $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            headers: {
                                Accept: "application/json, text/javascript, */*; q=0.01"
                            },
                            data: {
                                action: 'product_order_sync',
                                security: '<?php echo wp_create_nonce("product_order_sync"); ?>', // Change the single quotes to double quotes here
                            },
                            beforeSend: function() {
                                console.log('Before sending the AJAX request'); // Ensure the beforeSend function is being called
                                syncButton.attr('disabled', true);
                                syncButton.text('Syncing...');
                                document.body.style.cursor = 'wait';
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert('Product order synced successfully!');
                                } else {
                                    alert('Failed to sync product order: ' + response.data);
                                }
                            },
                            error: function() {
                                alert('An error occurred while syncing the product order.');
                            },
                            complete: function() {
                                $("#sync-spinner").removeClass("is-active");
                                document.body.style.cursor = 'default';
                                syncButton.prop('disabled', false).text('Sync Product Order');
                            },
                        });
                    });
                });
            })(jQuery);
        </script>
<?php
    }

    public static function sync()
    {
        $current_site_id = get_current_blog_id();
        $products = get_posts(array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'fields' => 'all',
        ));

        $site_ids = get_sites(array('fields' => 'ids'));
        foreach ($site_ids as $site_id) {
            if ($site_id != $current_site_id) {
                switch_to_blog($site_id);

                foreach ($products as $prod) {
                    $orig_menu_order = $prod->menu_order;
                    $site_prod = get_post($prod->ID);
                    if ($site_prod != null && $site_prod->menu_order != $orig_menu_order) {
                        wp_update_post(array(
                            'ID'           => $site_prod->ID,
                            'menu_order'   => $orig_menu_order,
                        ));
                    }
                }

                restore_current_blog();
            }
        }
    }
}
