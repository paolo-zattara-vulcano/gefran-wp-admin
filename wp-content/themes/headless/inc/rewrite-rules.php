<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// -----------------------------------------------------------------------------
// PRODUCT URL

function change_product_link( $permalink, $post ) {

	if( $post->post_type == 'product' ) {

		// PARENT CATEGORY

		$post_id = $post->ID;  // your post id

		// Step 1: Get all the categories assigned to the post
		$categories_assigned_to_post = wp_get_object_terms($post_id, 'product_category');

		// Step 2: Filter the array to get only the parent categories which have child categories assigned to the post
		if($categories_assigned_to_post){

			$parent_categories_with_child_terms_for_post = array_filter($categories_assigned_to_post, function($category) use ($categories_assigned_to_post) {
					// Check if the category is a parent (it has child categories)
					if ($category->parent == 0) {
							foreach ($categories_assigned_to_post as $sub_category) {

									// If one of the child categories is assigned to the post, return true
									if ($sub_category->parent == $category->term_id) {
											return true;
									}
							}
					}
					return false;
			});

			// Now, $parent_categories_with_child_terms_for_post contains the parent categories with child terms assigned to your post


			// Fallback if no parent with child assigned to the post are founded
			// CAT WITHOUT CHILDS
			if(!$parent_categories_with_child_terms_for_post){
				$parent_cat_arg_fallback = array(
					'hide_empty' => true,
					'parent' => 0,
					'object_ids' => $categories_assigned_to_post[0]->term_id,
				);
				$parent_cat_fallback = get_terms( 'product_category', $parent_cat_arg_fallback );
			}

			// Only one cat must be accepted
			$parent_cat = reset($parent_categories_with_child_terms_for_post) ?: reset($parent_cat_fallback);



			// CHILD CATEGORY
			$child_arg = array(
				'hide_empty' => true,
				'parent' => $parent_cat->term_id,
				'object_ids' => $post->ID
			);
			$child_cat = get_terms( 'product_category', $child_arg );

			// POST TYPE REWRITE SLUG
			$post_obj = get_post_type_object('product');
			$rewrite_slug = $post_obj->rewrite["slug"] . '/' ?: 'products/'; // fallback if no rewrite slug is set

			// CATS SLUGS
			// $parent_cat_slug = $parent_cat->slug ?: $post->ID;
			$parent_cat_slug = $parent_cat ? $parent_cat->slug . '/' : '';
			$child_cat_slug = $child_cat ? reset($child_cat)->slug . '/' : '';

			// POST SLUG
			$post_subtitle = get_field('sub_title', $post->ID);
			$post_slug = $post_subtitle ? $post->post_name . '-' . sanitize_title($post_subtitle) . '/' : $post->post_name  . '/';


			// PERMALINK
			$permalink = get_home_url() . '/' . $rewrite_slug . $parent_cat_slug . $child_cat_slug . $post_slug;
		}
	}

	return $permalink;

}
add_filter('post_type_link',"change_product_link",10,2);


// WORDPRESS FRONTEND REWRITE
// for reference, rewrite rules for wp frontend rewrite rules
// https://www.ibenic.com/custom-wordpress-rewrite-rule-combine-taxonomy-post-type/


// -----------------------------------------------------------------------------
// PRODUCT CATEGORIES URL

//https://rudrastyh.com/wordpress/remove-taxonomy-slug-from-urls.html
function remove_tax_slug_link( $link, $term, $taxonomy ) {

    if ( $taxonomy !== 'product_category' ){

			return $link;

		} else {

			$post_obj = get_post_type_object('product');
			$post_slug = $post_obj->rewrite["slug"] . '/' ?: 'products/'; // fallback if no rewrite slug is set

			$prod_category_slug = get_taxonomy( 'product_category' )->rewrite["slug"] . '/' ?: 'product_category/'; // fallback if no rewrite slug is set
			return str_replace( $prod_category_slug, $post_slug, $link );

		}

}
add_filter( 'term_link', 'remove_tax_slug_link', 10, 3 );
