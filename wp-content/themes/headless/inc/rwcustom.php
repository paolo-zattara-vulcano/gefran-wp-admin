<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


//------------------------------------------------
// SICUREZZA

add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'automatic_updater_disabled', '__return_true' );

// error_reporting(0);
// @ini_set(‘display_errors’, 0);


 //------------------------------------------------
// Disabilito ping e commenti su tutto il network

add_filter( 'pings_open', '__return_false', 10, 2 );
add_filter( 'comments_open', '__return_false', 10, 2 );


 //------------------------------------------------
// Disabilito emoji

function disable_emojis() {
 remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
 remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
 remove_action( 'wp_print_styles', 'print_emoji_styles' );
 remove_action( 'admin_print_styles', 'print_emoji_styles' );
 remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
 remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
 remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
 add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
 add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
}
add_action( 'init', 'disable_emojis' );

function disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}

function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
	if ( 'dns-prefetch' == $relation_type ) {
		/** This filter is documented in wp-includes/formatting.php */
		$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
		$urls = array_diff( $urls, array( $emoji_svg_url ) );
	}
	return $urls;
}



 //------------------------------------------------
// ACF -- Aggiungo una pagina Opzioni al Tema

if( function_exists('acf_add_options_page') ) {

  // Add parent.
  $main = acf_add_options_page(array(
    'page_title' 	=> 'ACF Global Options',
    'menu_title'	=> 'Global Options',
    'menu_slug' 	=> 'acfopt_global_options',
    'capability'	=> 'edit_posts',
    'redirect'		=> false,
    'show_in_graphql' => true
  ));

  // Add sub page.
  // $string_translation = acf_add_options_sub_page(array(
  //   'page_title' 	=> 'ACF Localization',
  //   'menu_title'	=> 'Localization',
  //   'menu_slug' 	=> 'acfopt_localization',
  //   'capability'	=> 'edit_posts',
  //   'parent_slug' => $main['menu_slug'],
  //   'redirect'		=> false,
  //   'show_in_graphql' => true
  // ));

	// Add sub page.
	$string_translation = acf_add_options_sub_page(array(
		'page_title' 	=> 'ACF People',
		'menu_title'	=> 'People',
		'menu_slug' 	=> 'acfopt_people',
		'capability'	=> 'edit_posts',
		'parent_slug' => $main['menu_slug'],
		'redirect'		=> false,
		'show_in_graphql' => true
	));

  // Add sub page.
  $string_translation = acf_add_options_sub_page(array(
    'page_title' 	=> 'ACF Offices',
    'menu_title'	=> 'Offices',
    'menu_slug' 	=> 'acfopt_offices',
    'capability'	=> 'edit_posts',
    'parent_slug' => $main['menu_slug'],
    'redirect'		=> false,
    'show_in_graphql' => true
  ));

  // Add sub page.
  $string_translation = acf_add_options_sub_page(array(
    'page_title' 	=> 'RMA Documents',
    'menu_title'	=> 'RMA Documents',
    'menu_slug' 	=> 'acfopt_rma_documents',
    'capability'	=> 'edit_posts',
    'parent_slug' => $main['menu_slug'],
    'redirect'		=> false,
    'show_in_graphql' => true
  ));

}


//------------------------------------------------
// YOAST SEO - remove 'Make primary' in Taxonomy
 add_filter( 'wpseo_primary_term_taxonomies', '__return_false' );


 //------------------------------------------------
// add language to admin body
//
add_filter('admin_body_class', 'add_body_classes');
function add_body_classes($classes) {
        $classes = $classes . ' ' . get_locale();
        return $classes;
}


// add editor the privilege to edit theme

function gfrn_add_role_cap($role,$cap){

	if( $role_object = get_role( $role ) ) {

		if (!$role_object->has_cap( $cap ) ) {
			$role_object->add_cap( $cap );
		}
	}

}


add_action('admin_menu', 'remove_admin_pages');
function remove_admin_pages() {
	if ( current_user_can( 'wpseo_manager' ) && ! current_user_can( 'administrator' ) ) {
		remove_menu_page('admin.php?page=hcms-settings-menu-page');
		remove_menu_page('edit.php?post_type=acf-field-group');
		remove_menu_page('options-general.php');
		remove_menu_page('edit-comments.php');
		remove_menu_page('cptui_main_menu');

		//Hide "CPT UI → Add/Edit Post Types".
		remove_submenu_page('cptui_main_menu', 'cptui_manage_post_types');
		//Hide "CPT UI → Add/Edit Taxonomies".
		remove_submenu_page('cptui_main_menu', 'cptui_manage_taxonomies');
		//Hide "CPT UI → Registered Types/Taxes".
		remove_submenu_page('cptui_main_menu', 'cptui_listings');
		//Hide "CPT UI → Tools".
		remove_submenu_page('cptui_main_menu', 'cptui_tools');
		//Hide "CPT UI → Help/Support".
		remove_submenu_page('cptui_main_menu', 'cptui_support');
		//Hide "CPT UI → About CPT UI".
		remove_submenu_page('cptui_main_menu', 'cptui_main_menu');

		//Hide the "GraphQL" menu.
		remove_menu_page('graphiql-ide');
		//Hide the "GraphQL → GraphiQL IDE" menu.
		remove_submenu_page('graphiql-ide', 'graphiql-ide');
		//Hide the "GraphQL → Settings" menu.
		remove_submenu_page('graphiql-ide', 'graphql-settings');
	}
}

if (is_admin()  && ! current_user_can( 'administrator' )){
	gfrn_add_role_cap("wpseo_manager","edit_theme_options");
	gfrn_add_role_cap("wpseo_manager","manage_options");
}
