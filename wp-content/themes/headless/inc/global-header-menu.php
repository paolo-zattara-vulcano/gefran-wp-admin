<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function get_current_url_description() {
    $current_url = home_url( add_query_arg( NULL, NULL ) );

    if (strpos($current_url, 'gefran.kinsta.cloud') !== false) {
        return 'Production';
    } elseif (strpos($current_url, 'gefranstg.kinsta.cloud') !== false) {
        return 'Staging';
    } elseif (strpos($current_url, 'contacti.kinsta.cloud') !== false) {
        return 'Contacts';
		} elseif (strpos($current_url, 'doc.gefran.com') !== false) {
	      return 'Documents';
	  } elseif (strpos($current_url, 'gefran-admin.dvl') !== false) {
				return 'Local Dev';
		} else {
        return 'Unknown Site';
    }
}

add_action('admin_bar_menu', 'custom_admin_bar_menu', 100);
	function custom_admin_bar_menu($wp_admin_bar) {
		$favicon_url = 'https://gefran.kinsta.cloud/wp-content/themes/headless/dist/images/favicon/gefran-favicon.svg';
		$current_url_description = get_current_url_description();

		$args = array(
				'id' => 'switch_area',
				'title' => '<img src="' . $favicon_url . '" style="width: 18px; height: 18px; vertical-align: middle; margin: 0 3px 3px 0;"> Admin Sites - ' . $current_url_description,
				'meta' => array('class' => 'switch-area-dropdown')
		);
		$wp_admin_bar->add_node($args);

    $link1 = array(
        'id' => 'link_1',
        'title' => 'Production Site Admin',
        'href' => 'https://gefran.kinsta.cloud/wp-admin/',
        'parent' => 'switch_area',
        'meta' => array('class' => 'switch-area-link1')
    );
    $wp_admin_bar->add_node($link1);

		$link2 = array(
        'id' => 'link_2',
				'title' => 'Staging Site Admin',
				'href' => 'https://gefranstg.kinsta.cloud/wp-admin/',
        'parent' => 'switch_area',
        'meta' => array('class' => 'switch-area-link2')
    );
    $wp_admin_bar->add_node($link2);

		$link3 = array(
				'id' => 'link_3',
				'title' => 'Contacts',
        'href' => 'https://contacti.kinsta.cloud/wp-admin/',
				'parent' => 'switch_area',
				'meta' => array('class' => 'switch-area-link3')
		);
		$wp_admin_bar->add_node($link3);

		$link4 = array(
				'id' => 'link_4',
				'title' => 'Documents',
				'href' => 'https://doc.gefran.com/wp-admin/',
				'parent' => 'switch_area',
				'meta' => array('class' => 'switch-area-link4')
		);
		$wp_admin_bar->add_node($link4);
}
