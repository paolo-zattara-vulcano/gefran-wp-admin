<?php

function updateFrontedDomain($locale, $url)
{
    $lang = explode("_", $locale)[0];
    if ($lang !== 'en') {
        return str_replace('https://gefran.kinsta.cloud/' . $lang, '', $url);
    } else return str_replace('https://gefran.kinsta.cloud', '', $url);
    /*
    if ($lang == 'it' || $lang == 'de' || $lang == 'fr' || $lang == 'es' || $lang == 'pt' || $lang == 'zh')
        return str_replace('gefran.kinsta.cloud/' . $lang, 'gefran.' . $lang, $url);
    else return 'gefran.com';
    */
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
