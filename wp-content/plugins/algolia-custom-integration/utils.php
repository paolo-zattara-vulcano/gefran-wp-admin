<?php

function updateFrontedDomain($locale, $url, $blog_id = null)
{
    if($blog_id) $locale = get_blog_locale_direct($blog_id);

    $main_site_url = is_multisite() ? network_home_url() : get_home_url();
    $lang = explode("_", $locale)[0];

    if ($lang !== 'en') {
        // convert zh to ch
        $lang_part = ($lang === 'zh') ? 'ch' : $lang;
        $fixed_url = str_replace($main_site_url . $lang_part, '', $url);
    }
    else $fixed_url = str_replace($main_site_url, '', $url);

    return $fixed_url;
}


function get_blog_locale_direct($blog_id) {
    global $wpdb;
    // Prefix for the blog's tables in the database
    $blog_prefix = $blog_id == 1 ? $wpdb->base_prefix : $wpdb->base_prefix . $blog_id . '_';

    // Query to get the locale setting
    $locale = $wpdb->get_var("SELECT option_value FROM " . $blog_prefix . "options WHERE option_name = 'WPLANG'");

    // Fallback to default locale if the locale setting is not set
    if (empty($locale)) {
        $locale = 'en_US'; // or use get_locale() to get the default locale
    }

    return $locale;
}


/**
 * Add a flash notice to {prefix}options table until a full page refresh is done
 *
 * @param string $notice our notice message
 * @param string $type This can be "info", "warning", "error" or "success", "warning" as default
 * @param boolean $dismissible set this to TRUE to add is-dismissible functionality to your notice
 * @return void
 */

function add_flash_notice($notice = "", $type = "warning", $dismissible = true)
{
    // Here we return the notices saved on our option, if there are not notices, then an empty array is returned
    $notices = get_option("my_flash_notices", array());

    $dismissible_text = ($dismissible) ? "is-dismissible" : "";

    // We add our new notice.
    array_push($notices, array(
        "notice" => $notice,
        "type" => $type,
        "dismissible" => $dismissible_text
    ));

    // Then we update the option with our notices array
    update_option("my_flash_notices", $notices);
}

/**
 * Function executed when the 'admin_notices' action is called, here we check if there are notices on
 * our database and display them, after that, we remove the option to prevent notices being displayed forever.
 * @return void
 */

function display_flash_notices()
{
    $notices = get_option("my_flash_notices", array());

    // Iterate through our notices to be displayed and print them.
    foreach ($notices as $notice) {
        printf(
            '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
            $notice['type'],
            $notice['dismissible'],
            $notice['notice']
        );
    }

    // Now we reset our options to prevent notices being displayed forever.
    if (!empty($notices)) {
        delete_option("my_flash_notices", array());
    }
}

// We add our display_flash_notices function to the admin_notices
add_action('admin_notices', 'display_flash_notices', 12);
