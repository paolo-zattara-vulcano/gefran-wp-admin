<?php

function sync_existing_media_to_other_blog($target_blog_id) {

  global $wpdb;

  // Switch to main blog
  switch_to_blog(1);

  // Query to get all product post IDs from main blog
  $product_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'product'");

  // Query to get all image attachments related to the products
  $product_images = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND post_parent IN (" . implode(',', $product_ids) . ")", OBJECT);

  // Switch to blog 2
  switch_to_blog($target_blog_id);

  // Array to hold URLs that are not found in blog 2
  $not_found_urls = [];

  // ----
  // $firstImageProcessed = false; // Flag to indicate if the first image is processed

  // Loop through each image attachment object from main blog
  foreach ($product_images as $image) {

      // if ($firstImageProcessed) {
      //     break; // Stop the loop if the first image is already processed
      // }

      $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE guid = %s AND post_type = 'attachment' AND post_mime_type LIKE 'image/%'", $image->guid));

      // If not found, create an attachment in blog 2 with the same properties
      if ($count == 0) {
          $not_found_urls[] = $image->guid;

          switch_to_blog(1);
          $metadata = wp_get_attachment_metadata($image->ID);
          switch_to_blog($target_blog_id);

          unset($image->ID);
          unset($image->post_parent);

          $array = (array) $image;
          $merged = array_merge($array, $metadata);


          $attachment_id = wp_insert_attachment($merged, $image->post_title, 0, true);
          if (!is_wp_error($attachment_id)) {
              require_once(ABSPATH . 'wp-admin/includes/image.php');

              // Generate the necessary metadata for the attachment image
              $attach_data = wp_generate_attachment_metadata($attachment_id, $image->guid);  // Replace $image->guid with the absolute file path if needed

              // Update metadata for the newly created attachment
              wp_update_attachment_metadata($attachment_id, $attach_data);
          }

    			wp_reset_postdata();
          error_log('Inserted ID: ' . $attachment_id);

          // $firstImageProcessed = true; // Set the flag to true after processing the first image
      }
  }

  // Switch back to main blog or original blog
  restore_current_blog();

  // Var dump URLs not found in blog 2, wrapped in <pre> tag
  echo '<pre>';
  var_dump($not_found_urls);
  echo '</pre>';

}

// Hook into 'init' to check for the URL parameter
function check_for_sync_trigger() {
    if (isset($_GET['syncblogmedia'])) {
        $target_blog_id = intval($_GET['syncblogmedia']);
        if ($target_blog_id > 0) {
            sync_existing_media_to_other_blog($target_blog_id);
        }
    }
}

add_action('init', 'check_for_sync_trigger');
