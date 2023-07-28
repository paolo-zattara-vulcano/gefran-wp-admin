<?php
/**
 * Theme basic setup.
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'after_setup_theme', 'overstrap_setup' );

if ( ! function_exists ( 'overstrap_setup' ) ) {
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function overstrap_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /lang/ directory.
		 * If you're building a theme based on overstrap, use a find and replace
		 * to change 'overstrap' to the name of your theme in all the template files
		 */
		load_theme_textdomain( 'overstrap', get_template_directory() . '/lang' );


		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'wp_languages_menu' => __( 'Languages Menu', 'overstrap' ),
			'wp_top_menu' => __( 'Top Menu', 'overstrap' ),
			'wp_top_social_menu' => __( 'Top Social Menu', 'overstrap' ),
			'wp_header_menu' => __( 'Header Menu', 'overstrap' ),
			'wp_header_cat_menu' => __( 'Header Category Menu', 'overstrap' ),
			'wp_footer_menu_1' => __( 'Footer Menu 1', 'overstrap' ),
			'wp_footer_menu_2' => __( 'Footer Menu 2', 'overstrap' ),
			'wp_footer_menu_3' => __( 'Footer Menu 3', 'overstrap' ),
			'wp_footer_menu_4' => __( 'Footer Menu 4', 'overstrap' ),
			'wp_footer_legal_menu' => __( 'Footer Legal Menu', 'overstrap' ),
			'wp_footer_social_menu' => __( 'Footer Social Menu', 'overstrap' )
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		/*
		 * Adding Thumbnail basic support
		 */
		add_theme_support( 'post-thumbnails' );

		/*
		 * Adding support for Widget edit icons in customizer
		 */
		add_theme_support( 'customize-selective-refresh-widgets' );

		/*
		 * Enable support for Post Formats.
		 * See http://codex.wordpress.org/Post_Formats
		 */
		add_theme_support( 'post-formats', array(
			'aside',
			'image',
			'video',
			'quote',
			'link',
		) );

	}
}
