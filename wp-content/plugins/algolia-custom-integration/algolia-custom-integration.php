<?php

/**
 * Plugin Name:     Algolia Custom Integration
 * Description:     Add Algolia Search feature
 * Text Domain:     algolia-custom-integration
 * Version:         1.0.0
 *
 * @package         Algolia_Custom_Integration
 */

// Check if WP_LOCAL_DEV is defined and set to true - in this case prevent executing
if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV === true) {
    error_log('NO algolia - WP_LOCAL_DEV');
    return;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/wp-cli.php';
require_once __DIR__ . '/algolia.php';

// check if it is a multisite network
if (is_multisite()) {

    function is_algolia_plugin_active() {
        // Check if the plugin is active on the current site.
        $is_active_per_site = in_array('algolia-custom-integration/algolia-custom-integration.php', apply_filters('active_plugins', get_option('active_plugins')));

        // Check if the plugin is network activated.
        $active_sitewide_plugins = get_site_option('active_sitewide_plugins');
        $is_active_network_wide = $active_sitewide_plugins && isset($active_sitewide_plugins['algolia-custom-integration/algolia-custom-integration.php']);

        return $is_active_per_site || $is_active_network_wide;
    }


    if (is_algolia_plugin_active()) {

        function algolia_add_settings_page()
        {
          add_options_page(
            'Algolia Integration',                        // Page title: appears in the title tag of the page when rendered
            'Algolia Integration',                    // Menu title: text for the menu in the admin sidebar
            'edit_posts',                       // Capability: user capability needed to access this page
            'algolia-custom-integration',        // Menu slug: unique identifier for the menu
            'algolia_render_plugin_settings_page'              // Function to output the content for this page
          );
        }
        // show / hide the menu
        add_action('admin_menu', 'algolia_add_settings_page', 999);

        function algolia_render_plugin_settings_page()
        {
?>
            <h2>Algolia integration Settings</h2>
            <form action="options.php" method="post">
                <?php
                settings_fields('algolia_custom_integration_plugin_options');
                do_settings_sections('algolia-custom-integration'); ?>
                <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save'); ?>" />
            </form>
<?php
        }

        function algolia_register_settings()
        {
            register_setting('algolia_custom_integration_plugin_options', 'algolia_custom_integration_plugin_options', 'algolia_custom_integration_plugin_options_validate');
            add_settings_section('api_settings', 'Credentials Settings', 'algolia_plugin_section_text', 'algolia-custom-integration');

            add_settings_field('algolia_plugin_setting_app_id', 'App Id', 'algolia_plugin_setting_app_id', 'algolia-custom-integration', 'api_settings');
            add_settings_field('algolia_plugin_setting_api_key', 'API Key', 'algolia_plugin_setting_api_key', 'algolia-custom-integration', 'api_settings');
        }


        function algolia_plugin_section_text()
        {
            echo '<p>Here you can set all the options for using the API</p>';
        }

        function algolia_plugin_setting_api_key()
        {
            $options = get_option('algolia_custom_integration_plugin_options');
            echo "<input id='algolia_plugin_setting_api_key' name='algolia_custom_integration_plugin_options[api_key]' type='text' value='" . esc_attr($options['api_key']) . "' />";
        }

        function algolia_plugin_setting_app_id()
        {
            $options = get_option('algolia_custom_integration_plugin_options');
            echo "<input id='algolia_plugin_setting_app_id' name='algolia_custom_integration_plugin_options[app_id]' type='text' value='" . esc_attr($options['app_id']) . "' />";
        }

        function hook_options_page_after_save($old_value, $new_value)
        {
            error_log("hook_options_page_after_save: " . $new_value['app_id'] . " " . $new_value['api_key']);
            $algolia = Algolia::instance(array());
            $algolia->initialize(array('app_id' => $new_value['app_id'], 'api_key' => $new_value['api_key']));
        }

        add_action('admin_init', 'algolia_register_settings');
        add_action('update_option_algolia_custom_integration_plugin_options', 'hook_options_page_after_save', 10, 2);
    }
}

require_once __DIR__ . '/index-on-save-product.php';
require_once __DIR__ . '/index-on-save-application.php';
require_once __DIR__ . '/index-on-save-post.php';
require_once __DIR__ . '/reindex-on-save-cat.php';
