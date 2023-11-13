<?php

/**
 * Plugin Name:     Multisite Global Sync
 * Description:     Sync acf fields to multiple sites
 * Text Domain:     multisite-global-sync
 * Version:         1.0.0
 *
 * @package         multisite_global_sync
 */


require_once __DIR__ . '/sync_page.php';
require_once __DIR__ . '/sync_product.php';
require_once __DIR__ . '/sync_application.php';
require_once __DIR__ . '/sync_products_order.php';
require_once __DIR__ . '/edit_products_order.php';
require_once __DIR__ . '/sync_product_category.php';
require_once __DIR__ . '/sync_blog_media.php';

new Global_Sync();
new Product_Order();
new Edit_Products_Order();

class Global_Sync
{

    function __construct()
    {
        if (is_multisite()) {
            add_action('admin_enqueue_scripts', array($this, 'multisite_global_sync_js_init'));

            add_action('add_meta_boxes_product', array($this, 'add_product_custom_box'), 10, 2);
            add_action('add_meta_boxes_application', array($this, 'add_application_custom_box'), 10, 2);
            // add_action('add_meta_boxes_page', array($this, 'add_page_post_box'), 10, 2);

            // taxonomy
            $taxonomy = 'product_category';
            add_action($taxonomy . '_edit_form_fields', array($this, 'product_category_taxonomy_button'), 10, 2);

            add_action('wp_ajax_page_ajax_action', array('Page_Sync', 'acf_ajax_page_handler'));
            add_action('wp_ajax_product_ajax_action', array('Product_Sync', 'acf_ajax_product_handler'));
            add_action('wp_ajax_products_category_update', array('Product_Sync', 'ajax_products_category_handler'));
            add_action('wp_ajax_application_ajax_action', array('Application_Sync', 'acf_ajax_application_handler'));
            add_action('wp_ajax_taxonomy_ajax_handler', array('Product_Category_Sync', 'acf_ajax_product_category_handler'));
        }
    }


    public function multisite_global_sync_js_init()
    {
        // CSS
        wp_enqueue_style('sync-css', get_stylesheet_directory_uri() . '/inc/plugins/multisite-global-sync/assets/css/ms.global.sync.css', array(), '1.0.0');

        // JS
        wp_enqueue_script('multisite-global-sync', get_stylesheet_directory_uri() . '/inc/plugins/multisite-global-sync/assets/js/acf.ajax.action.js', array('jquery'), 2.0);

        // code for sync categories on products page
        $screen = get_current_screen();
        if ('edit' === $screen->base && 'product' === $screen->post_type && get_locale() == 'en_US') {
            //wp_enqueue_style('sync-products-category-css', get_stylesheet_directory_uri() . '/inc/plugins/multisite-global-sync/assets/css/sync.products.category.css', array(), '1.0.0');
            wp_enqueue_script('sync-products-category-js', get_stylesheet_directory_uri() . '/inc/plugins/multisite-global-sync/assets/js/sync.products.category.js', array('jquery'), '1.3', true);
            wp_localize_script('sync-products-category-js', 'syncProductCategory', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('sync-products-category-nonce')
            ));
        }
        // end
    }


    function add_page_post_box()
    {
        if (get_locale() != 'en_US') {
            return;
        }
        add_meta_box(
            'sync_page_box_id',    // Unique ID
            'Multisite Global Sync',  // Box title
            array($this, 'multi_global_box_html'),  // Content callback, must be of type callable
            'page',                 // Post type
            'normal',
            'default',
            array('key' => 'page_ajax_action')
        );
    }


    function product_category_taxonomy_button()
    {
        if (get_locale() != 'en_US') {
            return;
        }
        ?>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('product_category_tax'); ?>" />
        <div class="taxonomy-button button button-primary">Sync taxonomy</div>
        <?php
    }


    public function add_product_custom_box()
    {
      error_log('add_product_custom_box');

        if (get_locale() != 'en_US') {
            return;
        }
        add_meta_box(
            'sync_product_box_id',    // Unique ID
            'Multisite Global Sync',  // Box title
            array($this, 'multi_global_box_html'),  // Content callback, must be of type callable
            'product',                 // Post type
            'normal',
            'default',
            array('key' => 'product_ajax_action')
        );
    }


    public function add_application_custom_box()
    {

        if (get_locale() != 'en_US') {
            return;
        }
        add_meta_box(
            'sync_app_box_id',
            'Multisite Global Sync',
            array($this, 'multi_global_box_html'),
            'application',
            'normal',
            'default',
            array('key' => 'application_ajax_action')
        );
    }


    public function multi_global_box_html($postId, $params)
    {
      ?>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce($params['args']['key']); ?>" />
        <input type="hidden" name="multi_global_action" value="<?php echo $params['args']['key']; ?>" />
        <!--<button class="custom-button button button-primary">Save Custom Fields</button>-->
        <div class="custom-button button button-primary">Sync Custom Fields</div>
      <?php
    }
}
