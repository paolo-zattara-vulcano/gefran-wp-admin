<?php
/**
 * Original ID Handler.
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function set_original_id_on_post_save($post_id, $post, $update) {
    // If this is just a revision, don't send the alert.
    if (wp_is_post_revision($post_id)) {
        return;
    }

    // Check if the post type is "product"
    if ($post->post_type == 'product') {
        // Get the current blog ID
        $current_blog_id = get_current_blog_id();

        // Get the current value of the "original_id" field
        $current_original_id = get_field('original_id', $post_id);

        // If the blog ID is 01 (English blog)
        if ($current_blog_id == 1) {
            // If the "original_id" field is empty, set it to "01" followed by the post ID
            if (empty($current_original_id)) {
                $original_id_value = '01' . $post_id;
                update_field('original_id', $original_id_value, $post_id);
            }
				}

				// Commented becouse fire also in product sync
        // } else {
        //     // If the blog ID is not 01 (e.g., the Italian blog), display an alert box
        //     echo "<script type='text/javascript'>alert('You must always create a post in English and then clone it to the other language.');</script>";
        // }
    }
}

// Hook into the 'save_post' action
add_action('save_post', 'set_original_id_on_post_save', 10, 3);
