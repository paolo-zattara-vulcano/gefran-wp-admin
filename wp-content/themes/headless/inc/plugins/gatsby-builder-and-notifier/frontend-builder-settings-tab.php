<?php

// Function to display and save webhooks settings
function display_and_save_webhooks() {
    // Check if the form is submitted and if the nonce is valid
    if (isset($_POST['submit']) && check_admin_referer('save_webhooks', 'webhooks_nonce')) {
        // Save the webhook settings for each blog in the network
        $blogs = get_sites();
        foreach ($blogs as $blog) {
            $blog_id = $blog->blog_id;
            $site_id_for_webhooks = isset($_POST["site_id_for_webhooks_$blog_id"]) ? sanitize_text_field($_POST["site_id_for_webhooks_$blog_id"]) : '';

            // Save the webhooks for the current blog in the main blog's options
            $webhooks_settings = get_option('gatsby_webhooks_settings', array());
            $webhooks_settings[$blog_id] = array(
                'site_id_for_webhooks' => $site_id_for_webhooks,
            );
            update_option('gatsby_webhooks_settings', $webhooks_settings);
        }

        // Add a success message
        echo '<div class="notice notice-success is-dismissible"><p>Webhooks settings saved.</p></div>';
    }

    // Display the form for each blog in the network
    $blogs = get_sites();
    ?>
    <form method="post">
        <?php
        // Add a nonce for security
        wp_nonce_field('save_webhooks', 'webhooks_nonce');

        foreach ($blogs as $blog) {
            $blog_id = $blog->blog_id;
            $webhooks_settings = get_option('gatsby_webhooks_settings', array());
            $site_id_for_webhooks = isset($webhooks_settings[$blog_id]['site_id_for_webhooks']) ? $webhooks_settings[$blog_id]['site_id_for_webhooks'] : '';
            $blog_language = get_blog_option($blog->blog_id, 'WPLANG'); ?>

            <h2><?= get_blog_option($blog->blog_id, 'blogname') . ' ' . $blog_language ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row" style="padding-top: 0;">Gatsby Site id for Webhooks</th>
                    <td style="padding-top: 0;">
                        <input style="width: 100%" type="text" name="site_id_for_webhooks_<?php echo $blog_id; ?>" value="<?php echo esc_attr($site_id_for_webhooks); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            <?php
        }
        ?>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings">
        </p>
    </form>
    <?php
}
