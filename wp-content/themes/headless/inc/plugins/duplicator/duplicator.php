<?php
/**
 * Duplicate post functions
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WordPress Multisite: Copy or Move Pages between Sites
 *
 * @author Paolo Zattara
 */


// ------------------------------------------
// CLONE POSTS ACROSS BLOGS

function clone_posts_across_blogs($relations) {
    global $wpdb;

		foreach ($relations as $item) {
			$source_blog_id = $item['source_blog_id'];
	    $source_post_id = $item['source_post_id'];
	    $destination_blog_id = $item['destination_blog_id'];
	    $destination_post_id = $item['destination_post_id'];


			// Switch to the source blog
	    switch_to_blog($source_blog_id);

	    // Get all ACF fields from the current post
	    $fields = get_fields($source_post_id);

	    // Get the post meta data
	    $meta_query = $wpdb->prepare("
	        SELECT meta_key, meta_value
	        FROM $wpdb->postmeta
	        WHERE post_id = %d
	    ", $source_post_id);
	    $meta_data = $wpdb->get_results($meta_query, ARRAY_A);

	    // Get the post data
	    $post_query = $wpdb->prepare("
	        SELECT *
	        FROM $wpdb->posts
	        WHERE ID = %d
	    ", $source_post_id);
	    $post_data = $wpdb->get_results($post_query, ARRAY_A);

			// Get featured image url from the current post
			$featured_image_url = get_the_post_thumbnail_url($source_post_id, $size='original');

	    // Switch to the destination blog
	    switch_to_blog($destination_blog_id);

	    // If the post ID is not given, insert a new post
	    if (!$destination_post_id || $destination_post_id === 0) {

	        $post_data[0]['ID'] = '';
	        $destination_post_id = wp_insert_post($post_data[0]);

	        // Create a relationship between the original post and the destination post
					$api = \Inpsyde\MultilingualPress\resolve(\Inpsyde\MultilingualPress\Framework\Api\ContentRelations::class);
	        $existing_relationships = \Inpsyde\MultilingualPress\translationIds($source_post_id, 'post', $source_blog_id);

					if($existing_relationships){
						$relationships = $existing_relationships;
						$relationships[$destination_blog_id] = $destination_post_id;
					} else {
						$relationships = array(
								$source_blog_id => $source_post_id,
								$destination_blog_id => $destination_post_id
						);
					}

	        $api->createRelationship($relationships, 'post');
	    }

	    // Update post meta and ACF fields
	    foreach ($meta_data as $meta) {
	        if ($meta['meta_key'] && strpos($meta['meta_key'], 'field_') !== 0) {
	            update_post_meta($destination_post_id, $meta['meta_key'], $meta['meta_value']);
	        }
	    }

	    foreach ($fields as $name => $value) {
	        update_field($name, $value, $destination_post_id);
	    }

			// Set the featured image
			if ($featured_image_url) {
					// Get attachements from image url
					$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $featured_image_url ));
					update_post_meta($destination_post_id, '_thumbnail_id', $attachment[0]);
			}


	    // Switch back to the original blog
	    restore_current_blog();

		}
}



// POSTBOX IN RIGHT SIDEBAR
function display_clone_postbox() {
    global $post, $wpdb;

    $post_id = $post->ID;
    $current_blog_id = get_current_blog_id();

    // Get all blogs
    $blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);

    // Get blogs with translation for the current post
    $translated_blogs = \Inpsyde\MultilingualPress\translationIds($post_id, 'Post', $current_blog_id);

		// Multilingualpress Translations object
		$translations_obj = multilingualpress_get_translations();

		$translations_link = '';
    $dropdown_options = '';
    $multiselect_options = '';

    foreach ($blogs as $blog) {
        $blog_id = $blog['blog_id'];
        error_log('$blog_id: ' . $blog_id);

        // Skip current blog
        if (intval($blog_id) !== intval($current_blog_id)) {

            switch_to_blog($blog_id);

            // Check if the blog has a translation for the current post
            if (isset($translated_blogs[$blog_id])) {
                $slug = get_post_field('post_title', $translated_blogs[$blog_id]);
                $blogDetails = get_blog_details(array('blog_id' => $blog_id));

								error_log('--------------- $translated_blogs');
								error_log(print_r($translated_blogs, true));

								$translations_link .= sprintf(
                    '<div style="display:block; margin-bottom: 5px;"><a href="%s" class="button button-small button-secondary" style="display:inline-block">%s</a><span> - %s</span></div>',
                    get_edit_post_link( $translated_blogs[$blog_id]),
                    $translations_obj[$blog_id]->language()->isoName(),
										get_the_title( $translated_blogs[$blog_id] ),
                );

								$dropdown_options .= sprintf(
										'<option value="%s" postid="%s" sourceBlogId="%s" sourcePostId="%s">%s - %s</option>',
										$blog_id,
										$translated_blogs[$blog_id],
										$current_blog_id,
										$post_id,
										$blogDetails->siteurl,
										$slug
								);
            } else {
                $blogDetails = get_blog_details(array('blog_id' => $blog_id));

                $multiselect_options .= sprintf(
                    '<option value="%s" postid="%s" sourceBlogId="%s" sourcePostId="%s">%s</option>',
                    $blog_id,
                    0, // Set post ID to 0 for blogs without translation
                    $current_blog_id,
                    $post_id,
                    $blogDetails->siteurl
                );
            }

            restore_current_blog();
        }
    }

    if (!empty($dropdown_options) || !empty($multiselect_options)) {
        echo '<div id="clone_postbox">';

				if (!empty($translations_link)) {
						echo '<h4>Edit translations:</h4>';
						echo $translations_link;
				}

        if (!empty($dropdown_options)) {
            echo '<h4>Clone to these translations:</h4>';
            echo '<select id="clone_post_translation" class="duplicator-select" multiple style="min-height: 130px">';
            echo $dropdown_options;
            echo '</select>';
						echo '<button id="clone_post_button" class="button duplicator-btn" style="margin-top: 10px;">Clone Content</button>';
        }

        if (!empty($multiselect_options)) {
            echo '<h4>Add new Translation:</h4>';
            echo '<select id="new_post_translation" class="duplicator-select" multiple style="min-height: 130px">';
            echo $multiselect_options;
            echo '</select>';
						echo '<button id="new_translation_button" class="button duplicator-btn" style="margin-top: 10px;">Create Translation</button>';
        }

        echo '</div>';
    }
}


function add_clone_postbox() {

		error_log('add_clone_postbox() function called');
    add_meta_box(
        'clone_postbox',
        'Clone Post Content',
        'display_clone_postbox',
        array('post', 'page', 'product', 'application', 'event'), // Add 'page' to include pages
        'side',
        'core'
    );
		error_log('add_meta_box() called');
}

add_action('add_meta_boxes', 'add_clone_postbox');



// TRIGGER FUNCTION ON CLONE BUTTON CLICK
add_action('admin_footer', 'add_clone_post_button_script');
function add_clone_post_button_script() {
    $ajax_nonce = wp_create_nonce('clone_post_nonce'); // Generate a nonce

    ?>
		<script>
		    document.addEventListener('DOMContentLoaded', function() {
		        var duplicatorBtn = document.querySelectorAll(".duplicator-btn");
		        var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

		        duplicatorBtn.forEach(function(button) {
		            button.addEventListener('click', function(event) {
		                event.preventDefault();

		                // Show the confirmation alert
		                var confirmation = confirm("The selected posts will be overwritten, are you sure?");
		                if (confirmation) {
		                    // User clicked "Yes"

		                    // Show the spinner
		                    button.innerHTML = '<span class="spinner is-active"></span><span class="processing">Processing...</span>';

		                    var selector = button.previousElementSibling;
		                    var selectedOptions = Array.from(selector.selectedOptions);
		                    var relationsArray = []; // Create an empty array to store the relation data

		                    selectedOptions.forEach(function(option) {
		                        var destinationBlogId = option.value;
		                        var destinationPostId = option.getAttribute('postid');
		                        var sourceBlogId = option.getAttribute('sourceBlogId');
		                        var sourcePostId = option.getAttribute('sourcePostId');

		                        var relationData = { // Create a separate object for relation data
		                            'source_blog_id': sourceBlogId,
		                            'source_post_id': sourcePostId,
		                            'destination_blog_id': destinationBlogId,
		                            'destination_post_id': destinationPostId
		                        };

		                        relationsArray.push(relationData); // Push the relation data object into the array
		                    });

		                    var data = {
		                        'action': 'clone_post',
		                        'security': '<?php echo $ajax_nonce; ?>',
		                        'relationsArray': relationsArray // Assign the array to the 'relationsArray' property
		                    };

		                    jQuery.ajax({
		                        url: ajaxUrl,
		                        method: 'POST',
		                        data: data,
		                        success: function(response) {
		                            console.log(response);

		                            // Show the success box
		                            button.innerHTML = '<span class="success" style="border-left: 4px solid #00a32a; padding-left:4px;">Success!</span>';
		                            setTimeout(function() {
		                                // Reset the button text after a delay
		                                button.innerHTML = 'Clone';
		                            }, 3000);
		                        },
		                        error: function(error) {
		                            console.log(error);

		                            // Show an error message
		                            button.innerHTML = '<span class="error" style="border-left: 4px solid #d63638; padding-left:4px;">Error occurred</span>';
		                            setTimeout(function() {
		                                // Reset the button text after a delay
		                                button.innerHTML = 'Clone';
		                            }, 3000);
		                        }
		                    });


		                } else {
		                    // User clicked "No" or closed the confirmation dialog
		                    console.log("Action canceled by user.");
		                }
		            });
		        });
		    });
		</script>

    <?php
}



// AJAX handler for cloning the post
add_action('wp_ajax_clone_post', 'handle_clone_post_ajax');
add_action('wp_ajax_nopriv_clone_post', 'handle_clone_post_ajax');
function handle_clone_post_ajax() {

		// Verify the nonce
			if ( ! wp_verify_nonce( $_POST['security'], 'clone_post_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce' );
			}

		$relations = $_POST['relationsArray'];
		clone_posts_across_blogs($relations);

    // Log a message after cloning is done
    error_log('Cloning process completed.');

    wp_die();
}
