<?php
/**
 * overstrap modify editor
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


function cptui_register_my_cpts() {

	$current_language = get_option('WPLANG');

	/**
	 * Post Type: Prodotti.
	 */

	$slug_map_product = [
		'en_US' => 'products',
		'it_IT' => 'prodotti',
		'fr_FR' => 'produits',
		'de_DE' => 'produkte',
		'es_ES' => 'productos',
		'pt_PT' => 'produtos',
		'zh_CN' => '产品',
		// add more languages here
 	];
	$slug_product = isset($slug_map_product[$current_language]) ? $slug_map_product[$current_language] : $slug_map_product['en_US']; // default to 'products' if language not found


	$labels = [
		"name" => esc_html__( "Prodotti", "overstrap" ),
		"singular_name" => esc_html__( "Prodotto", "overstrap" ),
	];

	$args = [
		"label" => esc_html__( "Prodotti", "overstrap" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => $slug_product, "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "revisions", "page-attributes" ],
		"show_in_graphql" => true,
		"graphql_single_name" => "Product",
		"graphql_plural_name" => "Products",
	];

	register_post_type( "product", $args );

	/**
	 * Post Type: Eventi.
	 */

	$labels = [
		"name" => esc_html__( "Eventi", "overstrap" ),
		"singular_name" => esc_html__( "Evento", "overstrap" ),
	];

	$args = [
		"label" => esc_html__( "Eventi", "overstrap" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "eventi", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "revisions" ],
		"show_in_graphql" => true,
		"graphql_single_name" => "Event",
		"graphql_plural_name" => "Events",
	];

	register_post_type( "event", $args );

	/**
	 * Post Type: Applicazioni.
	 */

	 $slug_map_application = [
 		'en_US' => 'industrial-sectors',
 		'it_IT' => 'settori-industriali',
 		'fr_FR' => 'secteurs-industriels',
 		'de_DE' => 'industriezweige',
 		'es_ES' => 'sectores-industriales',
 		'pt_PT' => 'setores-industriais',
 		'zh_CN' => '工业领域',
 		// add more languages here
  	];
 	$slug_application = isset($slug_map_application[$current_language]) ? $slug_map_application[$current_language] : $slug_map_application['en_US']; // default to 'application' if language not found


	$labels = [
		"name" => esc_html__( "Applicazioni", "overstrap" ),
		"singular_name" => esc_html__( "Applicazione", "overstrap" ),
		"menu_name" => esc_html__( "Applicazioni", "overstrap" ),
	];

	$args = [
		"label" => esc_html__( "Applicazioni", "overstrap" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => true,
		"can_export" => true,
		"rewrite" => [ "slug" => $slug_application, "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "revisions", "page-attributes" ],
		"show_in_graphql" => true,
		"graphql_single_name" => "Application",
		"graphql_plural_name" => "Applications",
	];

	register_post_type( "application", $args );

	/**
	 * Post Type: Investor Docs.
	 */

	$labels = [
		"name" => esc_html__( "Investor Docs", "overstrap" ),
		"singular_name" => esc_html__( "Investor Doc", "overstrap" ),
	];

	$args = [
		"label" => esc_html__( "Investor Docs", "overstrap" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "investor_doc", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title" ],
		"show_in_graphql" => true,
		"graphql_single_name" => "InvestorDoc",
		"graphql_plural_name" => "InvestorDocs",
	];

	register_post_type( "investor_doc", $args );

	/**
	 * Post Type: Distributors.
	 */

	$labels = [
		"name" => esc_html__( "Distributors", "overstrap" ),
		"singular_name" => esc_html__( "Distributor", "overstrap" ),
	];

	$args = [
		"label" => esc_html__( "Distributors", "overstrap" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "distributor", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "page-attributes" ],
		"taxonomies" => [ "product_category", "distributor_brand" ],
		"show_in_graphql" => true,
		"graphql_single_name" => "Distributor",
		"graphql_plural_name" => "Distributors",
	];

	register_post_type( "distributor", $args );
}

add_action( 'init', 'cptui_register_my_cpts' );
