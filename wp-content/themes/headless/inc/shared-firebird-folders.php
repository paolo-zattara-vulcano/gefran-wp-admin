<?php
/**
 * Custom hooks.
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// FILEBIRD
//Funzioni per sincronizzazioni cartelle
function pre_sincro_cartelle($where){
	$id_blog = get_current_blog_id();
	sincro_cartelle($id_blog);
	return $where;
}

function sincro_cartelle($id_blog){

	global $wpdb;
	$folders = FolderModel::allFolders("*",null,"id_asc");
	$attachment = array();

	switch_to_blog($id_blog);
	foreach($folders as $folder){
		$att_ids = Helpers::getAttachmentIdsByFolderId( $folder->id );
		for($i=0; $i<count($att_ids); $i++){
			$attachment[$folder->id][]=wp_get_attachment_url($att_ids[$i]);
		}
	}
	wp_reset_postdata();
	restore_current_blog();

	for($i=1; $i<=wp_count_sites()["all"]; $i++){

		if($id_blog!=$i){
			switch_to_blog($i);
			FolderModel::deleteAll();
			foreach($folders as $folder){
				$wpdb->insert($wpdb->prefix.'fbv', array('id' => $folder->id, 'name' => $folder->name, 'parent'=>$folder->parent));
				if(isset($attachment[$folder->id]) && count($attachment[$folder->id])>0){
					foreach($attachment[$folder->id] as $att){
						$id = attachment_url_to_postid($att);
						$wpdb->insert($wpdb->prefix.'fbv_attachment_folder', array('folder_id' => $folder->id, 'attachment_id' => $id));
					}
				}
			}
			wp_reset_postdata();
			restore_current_blog();
		}
	}

	return true;
}

add_filter( 'fbv_get_count_where_query', 'pre_sincro_cartelle');

//aggiunge id del sito nel back-end
function stampa_id_blog(){
	echo "<input type='hidden' value='".get_current_blog_id()."' name='id_blog'>";
}
add_action( 'in_admin_header', 'stampa_id_blog' );
