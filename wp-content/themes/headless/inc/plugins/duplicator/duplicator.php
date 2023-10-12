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
			$source_post_type = get_post_type($source_post_id);

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

			// NEW POST: If the post ID is not given, insert a new post
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

	    // Update post meta
	    foreach ($meta_data as $meta) {
	        if ($meta['meta_key'] && strpos($meta['meta_key'], 'field_') !== 0) {
	            update_post_meta($destination_post_id, $meta['meta_key'], $meta['meta_value']);
	        }
	    }

			// Set the featured image
			if ($featured_image_url) {
					// Get attachements from image url
					$attachment = attachment_url_to_postid($featured_image_url);
					update_post_meta($destination_post_id, '_thumbnail_id', $attachment);
			}

			// Update ACF fields
			foreach ($fields as $name => $value) {
				$field_object = get_field_object($name, $destination_post_id, false, true);

				// fields that need to be cloned by the raw content
				if (
					$field_object && $field_object['type'] == 'textarea' ||
					$field_object && $field_object['type'] == 'acfe_date_range_picker' ||
					$field_object && $field_object['type'] == 'acfe_advanced_link'
				) {
						$raw_field_name = custom_acf_get_field_name_by_key( $field_object['key'] );
						switch_to_blog($source_blog_id);
							$original_raw_content = get_field(	$raw_field_name, $source_post_id, false);
						restore_current_blog();
						update_post_meta($destination_post_id, $name, $original_raw_content);
				}

				// RELATIONSHIP TYPE
				elseif ($field_object && $field_object['type'] == 'relationship') {

					// get the name
					$relationship_field_name = custom_acf_get_field_name_by_key( $field_object['key'] );

					// get original field value
					switch_to_blog($source_blog_id);
						$original_relationship_content = get_field(	$relationship_field_name, $source_post_id, false);
					restore_current_blog();

					// Check if $original_relationship_content is an array
					if (is_array($original_relationship_content)) {
						// Loop through each element in $original_relationship_content and modify the ID
						foreach ($original_relationship_content as $key => $relation) {
							$translations = \Inpsyde\MultilingualPress\translationIds($relation, 'Post', 1);
							// Update the original array
							$original_relationship_content[$key] = isset($translations[$destination_blog_id]) ? $translations[$destination_blog_id] : '0';
						}
					}

					// update field
					update_field($name, $original_relationship_content, $destination_post_id);
				}

				// all the other fields
				else {

					// WARNING
					// The field "original_id" is always copied from the main
					// whenever will be needed to clone from different source, need to add logic to exclude the field

					// Update the ACF image fields with the correct attachment IDs
					$value = update_acf_image_field($value, $source_blog_id, $destination_blog_id, $source_post_id);
					update_field($name, $value, $destination_post_id);
				}
			}

			clone_taxonomy_terms_across_blogs($source_blog_id, $source_post_id, $destination_blog_id, $destination_post_id);

	    // Switch back to the original blog
	    restore_current_blog();

		}
}



function clone_taxonomy_terms_across_blogs($source_blog_id, $source_post_id, $destination_blog_id, $destination_post_id) {

    // Switch to the source blog
    switch_to_blog($source_blog_id);

	    // Get all taxonomies for the post
	    $taxonomies = get_object_taxonomies(get_post_type($source_post_id));

	    // Switch to the destination blog

	    foreach ($taxonomies as $taxonomy) {
	        // Get all terms for this taxonomy and post
	        $terms = wp_get_post_terms($source_post_id, $taxonomy, ['fields' => 'ids']);

	        $translated_terms = [];

	        foreach ($terms as $term_id) {
	            // Use the provided function to find the translated term
	            $translated_term_ids = \Inpsyde\MultilingualPress\translationIds($term_id, 'term', $source_blog_id);

							// error_log('--------------- translated_term_ids');
							// error_log(print_r($translated_term_ids, true));

	            if (isset($translated_term_ids[$destination_blog_id])) {
	                $translated_terms[] = $translated_term_ids[$destination_blog_id];
	            }
	        }


	        // Set the terms to the post in the destination blog
					switch_to_blog($destination_blog_id);
						// Overwrite terms on the destination post
			       if (!empty($translated_terms)) {
							 	// false flag to completely override
			           wp_set_object_terms($destination_post_id, $translated_terms, $taxonomy, false);
			       } else {
			           // If there are no translated terms, make sure to remove any existing terms
			           wp_delete_object_term_relationships($destination_post_id, $taxonomy);
			       }
					restore_current_blog();

	    }

    // Switch back to the original blog
    restore_current_blog();
}




// UTILITY: get acf name by key
function custom_acf_get_field_name_by_key( $key ) {
    $field = acf_maybe_get_field( $key );
    if ( empty( $field ) || ! isset( $field['parent'], $field['name'] ) ) {
        return $field;
    }
    $ancestors = array();
    while ( ! empty( $field['parent'] ) && ! in_array( $field['name'], $ancestors ) ) {
        $parent = acf_get_field( $field['parent'] );
        $ancestors[] = $field['name'];
        $field = $parent;
    }
    $formatted_key = array_reverse( $ancestors );
    $formatted_key = implode( '_', $formatted_key );
    return $formatted_key;
}



// -----------------------------------------------------
// RECURSIVELY CHECK FOR ATTACHMENTS IN ALL POST FIELDS
function update_acf_image_field($field_value, $source_blog_id, $destination_blog_id, $source_post_id, $field_name = '') {
    if (is_array($field_value)) {
        foreach ($field_value as $key => $value) {
            $current_field_name = $field_name ? $field_name . '_' . $key : $key;
            if (is_array($value)) {
                // Recursively check nested arrays
                $field_value[$key] = update_acf_image_field($value, $source_blog_id, $destination_blog_id, $source_post_id, $current_field_name);
            } else {
                if ($key === 'mime_type') {
                    // Single attachment (image, video, file, etc.)
                    return update_acf_image($field_value, $source_blog_id, $destination_blog_id);
                }
                if ($key === 'file') {
                    // Get the original value of the field
                    switch_to_blog($source_blog_id);
                    $original_value = get_post_meta($source_post_id, $current_field_name, true);
                    restore_current_blog();

                    // Update the file value
                    $field_value['file'] = update_acf_file($original_value, $source_blog_id, $destination_blog_id);
                }
								if ($key === 'type') {

									// error_log('--------------- type');
									// error_log(print_r($value, true));

										// Single attachment (image, video, file, etc.)
										return update_acf_image($field_value, $source_blog_id, $destination_blog_id);
								}

            }
        }
    }
    return $field_value;
}


// UPDATE IMAGES
function update_acf_image($image, $source_blog_id, $destination_blog_id) {
    $source_id = $image['ID'];

    // Switch to the source blog to get the attachment URL
    switch_to_blog($source_blog_id);
    $source_url = wp_get_attachment_url($source_id);
    restore_current_blog();

    // Switch to the destination blog to find the corresponding attachment ID
    switch_to_blog($destination_blog_id);
    $new_id = attachment_url_to_postid($source_url);

    // Get the new image data
    $new_image = acf_get_attachment($new_id);
    restore_current_blog();

    return $new_image;
}


// UPDATE FILES
function update_acf_file($file, $source_blog_id, $destination_blog_id) {
    $source_id = $file;

    // Switch to the source blog to get the attachment URL
    switch_to_blog($source_blog_id);
    $source_url = wp_get_attachment_url($source_id);
    restore_current_blog();

    // Switch to the destination blog to find the corresponding attachment ID
    switch_to_blog($destination_blog_id);
    $new_id = attachment_url_to_postid($source_url);

    // Get the new file data
    $new_file = acf_get_attachment($new_id);
    restore_current_blog();

    return $new_file;
}
// END OF ACF SYNC
// -----------------------------------------------------





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
        // error_log('$blog_id: ' . $blog_id);

        // Skip current blog
        if (intval($blog_id) !== intval($current_blog_id)) {

            switch_to_blog($blog_id);

            // Check if the blog has a translation for the current post
            if (isset($translated_blogs[$blog_id])) {
                $slug = get_post_field('post_title', $translated_blogs[$blog_id]);
                $blogDetails = get_blog_details(array('blog_id' => $blog_id));

								// error_log('--------------- $translated_blogs');
								// error_log(print_r($translated_blogs, true));

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

        if (!empty($dropdown_options) && $current_blog_id === 1) {
            echo '<h4>Clone to these translations:</h4>';
            echo '<select id="clone_post_translation" class="duplicator-select" multiple style="min-height: 130px">';
            echo $dropdown_options;
            echo '</select>';
						echo '<button id="clone_post_button" class="button duplicator-btn" style="margin-top: 10px;">Clone Content</button>';
        }

        if (!empty($multiselect_options) && $current_blog_id === 1) {
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

		// error_log('add_clone_postbox() function called');
    add_meta_box(
        'clone_postbox',
        'Translations',
        'display_clone_postbox',
        array('post', 'page', 'product', 'application', 'event'), // Add 'page' to include pages
        'side',
        'core'
    );
		// error_log('add_meta_box() called');
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
