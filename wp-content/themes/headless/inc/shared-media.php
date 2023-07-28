<?php
/**
 * Custom hooks.
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// COMMON UPLOAD FOLDER
// https://wordpress.stackexchange.com/questions/147750/shared-upload-folder-in-wordpress-multisite
// https://ilikekillnerds.com/2021/10/shared-uploads-directory-in-wordpress-multisite/
function wpse_147750_upload_dir( $dirs ) {
    $dirs['baseurl'] = network_site_url( '/wp-content/uploads' );
    $dirs['basedir'] = ABSPATH . 'wp-content/uploads';
    $dirs['path'] = $dirs['basedir'] . $dirs['subdir'];
    $dirs['url'] = $dirs['baseurl'] . $dirs['subdir'];

    return $dirs;
}

add_filter( 'upload_dir', 'wpse_147750_upload_dir' );


// UPLOAD FILTER
// Create attachments to all blogs when a media is uploaded
global $nome_file;

function get_name_file_upload( $file ) {
    global $nome_file;
	$nome_file = $file['name'];
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'get_name_file_upload' );

//crea data e metadata dei file caricati sugli altri siti
function custom_upload_filter( $file, $context ){
	global $nome_file;
	$name = $nome_file;
	$ext  = pathinfo( $name, PATHINFO_EXTENSION );
	$name = wp_basename( $name, ".$ext" );

	$name = sanitize_text_field( $name );
	$attachment = array(
			'post_mime_type' => $file['type'],
			'guid'           => $file['url'],
			'post_parent'    => 0, //post id
			'post_title'     => $name,
			'post_content'   => "",
			'post_excerpt'   => "",
	);

	for($i=1; $i<=wp_count_sites()["all"]; $i++){
		if(get_current_blog_id()!=$i){
			switch_to_blog($i);

			$attachment_id = wp_insert_attachment( $attachment, $file['file'], 0, true );
			if ( ! is_wp_error( $attachment_id ) ) {
				if ( ! headers_sent() ) {
					header( 'X-WP-Upload-Attachment-ID: ' . $attachment_id );
				}
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file['file'] ) );
			}

			wp_reset_postdata();
			restore_current_blog();
		}
	}

	return $file;
}
add_filter('wp_handle_upload', 'custom_upload_filter',10,2);


// DELETE FILTER
// Delete the attachment in all blogs when a media is deleted
function delete_multi_attachment($attachment_id, $post){
	$url = wp_get_attachment_url($attachment_id);
	global $id_original;
	if($id_original == get_current_blog_id()){
		for($i=1; $i<=wp_count_sites()["all"]; $i++){
			if(get_current_blog_id()!=$i){
				switch_to_blog($i);
				$id = attachment_url_to_postid($url);
				wp_delete_attachment($id);
				wp_reset_postdata();
				restore_current_blog();
			}
		}
	}
}

global $id_original;
$id_original = get_current_blog_id();
add_action( 'delete_attachment', 'delete_multi_attachment', 10, 2 );





/*
* Below the support for filebird using multisite, pay attention the available hooks are installed on the plugin with the right patch
*/

//post to filebird folder move after
function fbv_folder_relation_callback_after($post_id, $folder_id){
  global $wpdb;
  $current_blog_id = get_current_blog_id();
//  error_log("Moved post $post_id fo filebird folder $folder_id on blog id $current_blog_id");
//  error_log("Try to search and move post with original id $post_id fo filebird folder $folder_id on blog id $current_blog_id");

  //getting original post and folder
  $original_post = get_post($post_id);
//  if(is_null($original_post)){
//    error_log("Post is null");
//  }
//  elseif(is_array($original_post)){
//    error_log("Post is null");
//  }
//  else{
//    error_log("Post is type: " . get_class($original_post));
//  }
//  $original_folder = FileBird\Model\Folder::findById($folder_id);
//  $original_folder_name = $original_folder[0]->name;
//  $original_folder_parent = $original_folder[0]->parent;

  for($target_blog_id=1; $target_blog_id<=wp_count_sites()["all"]; $target_blog_id++){
    if($current_blog_id!=$target_blog_id){
      switch_to_blog($target_blog_id);

      //checking if switched folder exists, if not I gonna create it
      $target_folder = FileBird\Model\Folder::findById($folder_id);
      if(empty($target_folder)){
        fbvUpdateFoldersStructureForBlogId($target_blog_id, $current_blog_id);
      }

      //find target blog post by original post name (slug)
//      error_log(">>>>> Searching posts slug: {$original_post->post_name} on blog id $target_blog_id");




      $searched_rows = $wpdb->get_row(
        $wpdb->prepare(
          "SELECT ID, post_name FROM {$wpdb->posts} WHERE `post_name` = %s AND `post_type` = %d",
          $original_post->post_name,
          'attachment'
        )
      );
      if(!empty($searched_rows)){
//        error_log(print_r($searched_rows, true));
        $target_blog_post = $searched_rows;
      }


      if(is_null($target_blog_post)){
        error_log("XXXXXXXXXXXXXXXXXXXXX Target post is null, searched on blog id {$target_blog_id}");
      }
      elseif(is_array($target_blog_post)){
//        error_log("Target post is array");
        FileBird\Model\Folder::setFoldersForPosts($target_blog_post['ID'], $folder_id, false);
//        error_log("---------------------- Target post {$target_blog_post['ID']} have been moved to folder {$folder_id} on blog id {$target_blog_id}");
      }
      else {
//        error_log("Target post is type: " . get_class($target_blog_post));
        FileBird\Model\Folder::setFoldersForPosts($target_blog_post->ID, $folder_id, false);
//        error_log("---------------------- Target post {$target_blog_post->ID} have been moved to folder {$folder_id} on blog id {$target_blog_id}");
      }

      restore_current_blog();
    }
  }
}
add_action('fbv_after_set_folder', 'fbv_folder_relation_callback_after', 10, 2);

function fbvUpdateFoldersStructureForBlogId($blog_id = null, $from_blog_id = null){
  global $wpdb;
  $from_blog_prefix = ($from_blog_id >= 2) ? "{$from_blog_id}_" : "";
  if(!$blog_id){
    throw new InvalidArgumentException('Invalid target blog updating Filebird table folders structure');
  }
  $target_blog_prefix = ($blog_id === 1 || !$blog_id) ? "" : "{$blog_id}_";
  $wpdb->query( "TRUNCATE TABLE wp_{$target_blog_prefix}fbv" );
  $wpdb->query( "INSERT INTO wp_{$target_blog_prefix}fbv SELECT * FROM wp_{$from_blog_prefix}fbv");

//  error_log("Replaced data of table {$wpdb->prefix}{$target_blog_prefix}fbv with data of table {$wpdb->prefix}{$from_blog_prefix}fbv");
}

function fbv_folder_creation_callback($folder_id = null, $param2 = null, $param3 = null){
//  error_log("Created filebird folder with id {$folder_id}");
  $current_blog_id = get_current_blog_id();
  for($target_blog_id=1; $target_blog_id<=wp_count_sites()["all"]; $target_blog_id++){
    if($current_blog_id!=$target_blog_id){
      switch_to_blog($target_blog_id);
      fbvUpdateFoldersStructureForBlogId($target_blog_id, $current_blog_id);
      restore_current_blog();
    }
  }
}
add_filter('fbv_after_create_folder', 'fbv_folder_creation_callback', 10, 1);
add_filter('fbv_after_update_folder_order_and_parent', 'fbv_folder_creation_callback', 10, 3);
add_filter('fbv_after_rename_folder', 'fbv_folder_creation_callback', 10, 2);
add_filter('fbv_after_update_folder_parent', 'fbv_folder_creation_callback', 10, 2);
add_filter('fbv_after_delete_all', 'fbv_folder_creation_callback', 10);
add_filter('fbv_after_delete_folder', 'fbv_folder_creation_callback', 10, 1);
