<?php
/**
 * Function to retrieve missing translations across network
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function find_missing_translations() {
		global $wpdb;

		// Get the IDs and titles of all products on the main site
		$main_site_products = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->base_prefix}posts WHERE post_type = 'product' AND post_status = 'publish'", ARRAY_A);

		// Get all blog IDs excluding the main site
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs WHERE blog_id != 1");

		$missing_translations = array();

		foreach ($main_site_products as $product) {
				$product_id = $product['ID'];
				$product_title = $product['post_title'];

				$current_blog_id = 1; // Main site ID
				$translated_blogs = \Inpsyde\MultilingualPress\translationIds($product_id, 'Post', $current_blog_id);

				foreach ($blog_ids as $blog_id) {
						if (!array_key_exists($blog_id, $translated_blogs)) {
								$missing_translations[$blog_id][] = array(
									'blog' => $blog_id,
									'ID' => $product_id,
									'Title' => $product_title
								);
						}
				}
		}

		return $missing_translations;
}

var_dump(find_missing_translations());
