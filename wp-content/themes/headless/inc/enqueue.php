<?php
/**
 * overstrap enqueue scripts
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'overstrap_scripts' ) ) {
	/**
	 * Load theme's JavaScript and CSS sources.
	 */
	function overstrap_scripts() {
		// Get the theme data.
		$the_theme     = wp_get_theme();
		$theme_version = $the_theme->get( 'Version' );

		// $css_version = $theme_version . '.' . filemtime( get_template_directory() . '/dist/styles/theme.min.css' );
		$css_version = rand(0, 99999);
		wp_enqueue_style( 'overstrap-styles', get_stylesheet_directory_uri() . '/dist/styles/theme.min.css', array(), $css_version ); // develop
		// wp_enqueue_style( 'overstrap-styles', get_stylesheet_directory_uri() . '/dist/styles/theme.min.bundle.css', array(), $css_version ); // produzione

		// $js_version = $theme_version . '.' . filemtime( get_template_directory() . '/dist/scripts/theme.min.js' );
		$js_version = rand(0, 99999);
		wp_enqueue_script( 'overstrap-scripts', get_template_directory_uri() . '/dist/scripts/theme.min.js', array(), $js_version, true );


		// if(is_page_template('landing.php')){
		// 	wp_enqueue_script( 'landing_js', get_template_directory_uri() . '/dist/scripts/ind/landing_animations.js', '', $css_version, true );
		// }

		// RW Ajax support con nonce
		// https://eric.blog/2013/06/18/how-to-add-a-wordpress-ajax-nonce/
		$nonce = wp_create_nonce('190205-rw-ajax-nonce');
		wp_localize_script('overstrap-scripts','rw_ajax_obj', array(
			'ajax_url' => admin_url( 'admin-ajax.php'),
			'ajax_nonce' => $nonce
		));

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
} // endif function_exists( 'overstrap_scripts' ).

add_action( 'wp_enqueue_scripts', 'overstrap_scripts' );


//------------------------------------------------
// STILI E JS - Backend

function stile_admin() {
 wp_enqueue_style( 'rw_backend_css', get_template_directory_uri() . '/dist/styles/admin.min.css' );
 wp_enqueue_script( 'rw-admin-js', get_template_directory_uri() . '/dist/scripts/headless-admin-gf.js', __FILE__ );
}
add_action('admin_enqueue_scripts', 'stile_admin');
add_action('login_enqueue_scripts', 'stile_admin');


//------------------------------------------------
// Cleanup <head> section
// https://stackoverflow.com/questions/34750148/how-to-delete-remove-wordpress-feed-urls-in-header
// https://crunchify.com/how-to-clean-up-wordpress-header-section-without-any-plugin/

add_action( 'after_setup_theme', 'prefix_remove_unnecessary_tags' );

function prefix_remove_unnecessary_tags(){

    // REMOVE WP EMOJI
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );


    // remove all tags from header
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wp_generator' );
    remove_action( 'wp_head', 'feed_links', 2 );
    remove_action( 'wp_head', 'index_rel_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'feed_links_extra', 3 );
    remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
    remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
    remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 );
    remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
    remove_action( 'wp_head', 'rest_output_link_wp_head'  );
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    remove_action( 'template_redirect', 'rest_output_link_header', 11 );

		// dns prefetch
		//https://wordpress.stackexchange.com/questions/236705/remove-rel-dns-prefetch-href-maps-google-com-from-wp-head
		remove_action( 'wp_head', 'wp_resource_hints', 2, 99 );

		// remove .recentcomments inline style
		// https://www.isitwp.com/remove-recent-comments-wp_head-css/
		function remove_recent_comments_style() {
		    global $wp_widget_factory;
		    remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
		}
		add_action('widgets_init', 'remove_recent_comments_style');

    // language
    // add_filter('sitepress.hreflang_type', '__return_false');

		global $sitepress;
		remove_action( 'wp_head', array( $sitepress, 'meta_generator_tag' ) );
}
