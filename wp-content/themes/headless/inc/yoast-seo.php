<?php
/**
 * Yoast SEO functions
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function my_remove_wp_seo_meta_box() {
	remove_meta_box('wpseo_meta', 'investor_doc', 'normal');
}
add_action('add_meta_boxes', 'my_remove_wp_seo_meta_box', 100);
