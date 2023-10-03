<?php
/**
 * overstrap modify editor
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function cptui_register_my_taxes() {

	/**
	 * Taxonomy: Categorie prodotto.
	 */

	$labels = [
		"name" => esc_html__( "Categorie prodotto", "overstrap" ),
		"singular_name" => esc_html__( "Categoria prodotto", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "Categorie prodotto", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'product_category', 'with_front' => true,  'hierarchical' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "product_category",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => true,
		"show_in_graphql" => true,
		"graphql_single_name" => "ProductCategory",
		"graphql_plural_name" => "ProductCategories",
	];
	register_taxonomy( "product_category", [ "product", "distributor" ], $args );

	/**
	 * Taxonomy: Contenuti applicazioni.
	 */

	$labels = [
		"name" => esc_html__( "Contenuti applicazioni", "overstrap" ),
		"singular_name" => esc_html__( "Contenuto applicazioni", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "Contenuti applicazioni", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'application_content', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "application_content",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "ApplicationContent",
		"graphql_plural_name" => "ApplicationContents",
	];
	register_taxonomy( "application_content", [ "application" ], $args );

	/**
	 * Taxonomy: Categorie applicazioni.
	 */

	$labels = [
		"name" => esc_html__( "Categorie applicazioni", "overstrap" ),
		"singular_name" => esc_html__( "Categoria applicazioni", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "Categorie applicazioni", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'application_category', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "application_category",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "ApplicationCategory",
		"graphql_plural_name" => "ApplicationCategories",
	];
	register_taxonomy( "application_category", [ "product", "application" ], $args );

	/**
	 * Taxonomy: F - Settori applicativi.
	 */

	$labels = [
		"name" => esc_html__( "F - Settori applicativi", "overstrap" ),
		"singular_name" => esc_html__( "F - Settori applicativi", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Settori applicativi", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_application', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_application",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfApplication",
		"graphql_plural_name" => "PfsApplication",
	];
	register_taxonomy( "filter_application", [ "product" ], $args );

	/**
	 * Taxonomy: F - Tipologia.
	 */

	$labels = [
		"name" => esc_html__( "F - Tipologia", "overstrap" ),
		"singular_name" => esc_html__( "F - Tipologia", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Tipologia", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_type', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_type",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfType",
		"graphql_plural_name" => "PfsType",
	];
	register_taxonomy( "filter_type", [ "product" ], $args );

	/**
	 * Taxonomy: F - Caratteristiche Meccaniche.
	 */

	$labels = [
		"name" => esc_html__( "F - Caratteristiche Meccaniche", "overstrap" ),
		"singular_name" => esc_html__( "F - Caratteristiche Meccaniche", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Caratteristiche Meccaniche", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_mechanical_characteristic', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_mechanical_characteristic",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfMechanicalCharacteristic",
		"graphql_plural_name" => "PfsMechanicalCharacteristic",
	];
	register_taxonomy( "filter_mechanical_characteristic", [ "product" ], $args );

	/**
	 * Taxonomy: F - Modalità di fissaggio.
	 */

	$labels = [
		"name" => esc_html__( "F - Modalità di fissaggio", "overstrap" ),
		"singular_name" => esc_html__( "F - Modalità di fissaggio", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Modalità di fissaggio", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_mounting', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_mounting",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfMounting",
		"graphql_plural_name" => "PfsMounting",
	];
	register_taxonomy( "filter_mounting", [ "product" ], $args );

	/**
	 * Taxonomy: F - Variabile.
	 */

	$labels = [
		"name" => esc_html__( "F - Variabile", "overstrap" ),
		"singular_name" => esc_html__( "F - Variabile", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Variabile", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_variable', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_variable",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfVariable",
		"graphql_plural_name" => "PfsVariable",
	];
	register_taxonomy( "filter_variable", [ "product" ], $args );

	/**
	 * Taxonomy: F - Uscita Elettrica.
	 */

	$labels = [
		"name" => esc_html__( "F - Uscita Elettrica", "overstrap" ),
		"singular_name" => esc_html__( "F - Uscita Elettrica", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Uscita Elettrica", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_signal_output', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_signal_output",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfSignalOutput",
		"graphql_plural_name" => "PfsSignalOutput",
	];
	register_taxonomy( "filter_signal_output", [ "product" ], $args );

	/**
	 * Taxonomy: F - Certificazioni di Sicurezza e Conformità.
	 */

	$labels = [
		"name" => esc_html__( "F - Certificazioni di Sicurezza e Conformità", "overstrap" ),
		"singular_name" => esc_html__( "F - Certificazioni di Sicurezza e Conformità", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Certificazioni di Sicurezza e Conformità", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_safety_conformity_cert', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_safety_conformity_cert",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfSafetyConformityCert",
		"graphql_plural_name" => "PfsSafetyConformityCert",
	];
	register_taxonomy( "filter_safety_conformity_cert", [ "product" ], $args );

	/**
	 * Taxonomy: F - Certificazioni EX.
	 */

	$labels = [
		"name" => esc_html__( "F - Certificazioni EX", "overstrap" ),
		"singular_name" => esc_html__( "F - Certificazioni EX", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Certificazioni EX", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_ex_certification', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_ex_certification",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfExCertification",
		"graphql_plural_name" => "PfsExCertification",
	];
	register_taxonomy( "filter_ex_certification", [ "product" ], $args );

	/**
	 * Taxonomy: F - Caratteristiche.
	 */

	$labels = [
		"name" => esc_html__( "F - Caratteristiche", "overstrap" ),
		"singular_name" => esc_html__( "F - Caratteristiche", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Caratteristiche", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_characteristic', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_characteristic",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfCharacteristic",
		"graphql_plural_name" => "PfsCharacteristic",
	];
	register_taxonomy( "filter_characteristic", [ "product" ], $args );

	/**
	 * Taxonomy: F - Lunghezza.
	 */

	$labels = [
		"name" => esc_html__( "F - Lunghezza", "overstrap" ),
		"singular_name" => esc_html__( "F - Lunghezza", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Lunghezza", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_stroke', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_stroke",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfStroke",
		"graphql_plural_name" => "PfsStroke",
	];
	register_taxonomy( "filter_stroke", [ "product" ], $args );

	/**
	 * Taxonomy: F - Accuratezza.
	 */

	$labels = [
		"name" => esc_html__( "F - Accuratezza", "overstrap" ),
		"singular_name" => esc_html__( "F - Accuratezza", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Accuratezza", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_accuracy', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_accuracy",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfAccuracy",
		"graphql_plural_name" => "PfsAccuracy",
	];
	register_taxonomy( "filter_accuracy", [ "product" ], $args );

	/**
	 * Taxonomy: F - Dimensioni e montaggio.
	 */

	$labels = [
		"name" => esc_html__( "F - Dimensioni e montaggio", "overstrap" ),
		"singular_name" => esc_html__( "F - Dimensioni e montaggio", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Dimensioni e montaggio", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_format_mounting', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_format_mounting",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfFormatMounting",
		"graphql_plural_name" => "PfsFormatMounting",
	];
	register_taxonomy( "filter_format_mounting", [ "product" ], $args );

	/**
	 * Taxonomy: F - Numero loop.
	 */

	$labels = [
		"name" => esc_html__( "F - Numero loop", "overstrap" ),
		"singular_name" => esc_html__( "F - Numero loop", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Numero loop", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_loop_controller', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_loop_controller",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfLoopController",
		"graphql_plural_name" => "PfsLoopController",
	];
	register_taxonomy( "filter_loop_controller", [ "product" ], $args );

	/**
	 * Taxonomy: F - Funzionalità.
	 */

	$labels = [
		"name" => esc_html__( "F - Funzionalità", "overstrap" ),
		"singular_name" => esc_html__( "F - Funzionalità", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Funzionalità", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_features', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_features",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfFeatures",
		"graphql_plural_name" => "PfsFeatures",
	];
	register_taxonomy( "filter_features", [ "product" ], $args );

	/**
	 * Taxonomy: F - Fieldbus.
	 */

	$labels = [
		"name" => esc_html__( "F - Fieldbus", "overstrap" ),
		"singular_name" => esc_html__( "F - Fieldbus", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Fieldbus", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_fieldbus', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_fieldbus",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfFieldbus",
		"graphql_plural_name" => "PfsFieldbus",
	];
	register_taxonomy( "filter_fieldbus", [ "product" ], $args );

	/**
	 * Taxonomy: F - Numero fasi.
	 */

	$labels = [
		"name" => esc_html__( "F - Numero fasi", "overstrap" ),
		"singular_name" => esc_html__( "F - Numero fasi", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Numero fasi", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_phase', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_phase",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfPhase",
		"graphql_plural_name" => "PfsPhase",
	];
	register_taxonomy( "filter_phase", [ "product" ], $args );

	/**
	 * Taxonomy: F - Corrente nominale.
	 */

	$labels = [
		"name" => esc_html__( "F - Corrente nominale", "overstrap" ),
		"singular_name" => esc_html__( "F - Corrente nominale", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Corrente nominale", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_current_rate', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_current_rate",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfCurrentRate",
		"graphql_plural_name" => "PfsCurrentRate",
	];
	register_taxonomy( "filter_current_rate", [ "product" ], $args );

	/**
	 * Taxonomy: F - Dissipatore.
	 */

	$labels = [
		"name" => esc_html__( "F - Dissipatore", "overstrap" ),
		"singular_name" => esc_html__( "F - Dissipatore", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Dissipatore", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_heatsink', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_heatsink",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfHeatsink",
		"graphql_plural_name" => "PfsHeatsink",
	];
	register_taxonomy( "filter_heatsink", [ "product" ], $args );

	/**
	 * Taxonomy: F - Tipo di controllo.
	 */

	$labels = [
		"name" => esc_html__( "F - Tipo di controllo", "overstrap" ),
		"singular_name" => esc_html__( "F - Tipo di controllo", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Tipo di controllo", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_type_control', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_type_control",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfTypeControl",
		"graphql_plural_name" => "PfsTypeControl",
	];
	register_taxonomy( "filter_type_control", [ "product" ], $args );

	/**
	 * Taxonomy: F - Certificazioni e normative.
	 */

	$labels = [
		"name" => esc_html__( "F - Certificazioni e normative", "overstrap" ),
		"singular_name" => esc_html__( "F - Certificazioni e normative", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Certificazioni e normative", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_certification_norms', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_certification_norms",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfCertificationNorms",
		"graphql_plural_name" => "PfsCertificationNorms",
	];
	register_taxonomy( "filter_certification_norms", [ "product" ], $args );

	/**
	 * Taxonomy: F - Variabile misurata.
	 */

	$labels = [
		"name" => esc_html__( "F - Variabile misurata", "overstrap" ),
		"singular_name" => esc_html__( "F - Variabile misurata", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Variabile misurata", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_measured_variable', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_measured_variable",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfMeasuredVariable",
		"graphql_plural_name" => "PfsMeasuredVariable",
	];
	register_taxonomy( "filter_measured_variable", [ "product" ], $args );

	/**
	 * Taxonomy: Categoria Investor Doc.
	 */

	// $labels = [
	// 	"name" => esc_html__( "Categoria Investor Doc", "overstrap" ),
	// 	"singular_name" => esc_html__( "Categorie Investor Doc", "overstrap" ),
	// ];
	//
	//
	// $args = [
	// 	"label" => esc_html__( "Categoria Investor Doc", "overstrap" ),
	// 	"labels" => $labels,
	// 	"public" => true,
	// 	"publicly_queryable" => true,
	// 	"hierarchical" => true,
	// 	"show_ui" => true,
	// 	"show_in_menu" => true,
	// 	"show_in_nav_menus" => true,
	// 	"query_var" => true,
	// 	"rewrite" => [ 'slug' => 'investor_doc_category', 'with_front' => true, ],
	// 	"show_admin_column" => true,
	// 	"show_in_rest" => true,
	// 	"show_tagcloud" => false,
	// 	"rest_base" => "investor_doc_category",
	// 	"rest_controller_class" => "WP_REST_Terms_Controller",
	// 	"rest_namespace" => "wp/v2",
	// 	"show_in_quick_edit" => true,
	// 	"sort" => false,
	// 	"show_in_graphql" => true,
	// 	"graphql_single_name" => "InvestorDocCategory",
	// 	"graphql_plural_name" => "InvestorDocCategories",
	// ];
	// register_taxonomy( "investor_doc_category", [ "investor_doc" ], $args );

	/**
	 * Taxonomy: Anni Investor Doc.
	 */

	// $labels = [
	// 	"name" => esc_html__( "Anni Investor Doc", "overstrap" ),
	// 	"singular_name" => esc_html__( "Anno Investor Doc", "overstrap" ),
	// ];
	//
	//
	// $args = [
	// 	"label" => esc_html__( "Anni Investor Doc", "overstrap" ),
	// 	"labels" => $labels,
	// 	"public" => true,
	// 	"publicly_queryable" => true,
	// 	"hierarchical" => true,
	// 	"show_ui" => true,
	// 	"show_in_menu" => true,
	// 	"show_in_nav_menus" => true,
	// 	"query_var" => true,
	// 	"rewrite" => [ 'slug' => 'investor_doc_year', 'with_front' => true, ],
	// 	"show_admin_column" => true,
	// 	"show_in_rest" => true,
	// 	"show_tagcloud" => false,
	// 	"rest_base" => "investor_doc_year",
	// 	"rest_controller_class" => "WP_REST_Terms_Controller",
	// 	"rest_namespace" => "wp/v2",
	// 	"show_in_quick_edit" => true,
	// 	"sort" => false,
	// 	"show_in_graphql" => true,
	// 	"graphql_single_name" => "InvestorDocYear",
	// 	"graphql_plural_name" => "InvestorDocYears",
	// ];
	// register_taxonomy( "investor_doc_year", [ "investor_doc" ], $args );

	/**
	 * Taxonomy: Brands.
	 */

	$labels = [
		"name" => esc_html__( "Brands", "overstrap" ),
		"singular_name" => esc_html__( "Brand", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "Brands", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'distributor_brand', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "distributor_brand",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => true,
		"show_in_graphql" => true,
		"graphql_single_name" => "DistributorBrand",
		"graphql_plural_name" => "DistributorBrands",
	];
	register_taxonomy( "distributor_brand", [ "distributor" ], $args );

	/**
	 * Taxonomy: Countries.
	 */

	$labels = [
		"name" => esc_html__( "Countries", "overstrap" ),
		"singular_name" => esc_html__( "Country", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "Countries", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'country', 'with_front' => true,  'hierarchical' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "country",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "Country",
		"graphql_plural_name" => "Countries",
	];
	register_taxonomy( "country", [ "distributor" ], $args );

	/**
	 * Taxonomy: Categorie eventi.
	 */

	$labels = [
		"name" => esc_html__( "Categorie eventi", "overstrap" ),
		"singular_name" => esc_html__( "Categoria eventi", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "Categorie eventi", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'event_category', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "event_category",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "EventCategory",
		"graphql_plural_name" => "EventCategories",
	];
	register_taxonomy( "event_category", [ "event" ], $args );

	/**
	 * Taxonomy: F - Serie.
	 */

	$labels = [
		"name" => esc_html__( "F - Series", "overstrap" ),
		"singular_name" => esc_html__( "F - Series", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Series", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_series', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_series",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfSeries",
		"graphql_plural_name" => "PfsSeries",
	];
	register_taxonomy( "filter_series", [ "product" ], $args );

	/**
	 * Taxonomy: F - Input/output.
	 */

	$labels = [
		"name" => esc_html__( "F - Input/output", "overstrap" ),
		"singular_name" => esc_html__( "F - Input/output", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Input/output", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_input_output', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_input_output",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfInputOutput",
		"graphql_plural_name" => "PfsInputOutput",
	];
	register_taxonomy( "filter_input_output", [ "product" ], $args );


	/**
	 * Taxonomy: F - System and accessories.
	 */

	$labels = [
		"name" => esc_html__( "F - System and accessories", "overstrap" ),
		"singular_name" => esc_html__( "F - System and accessories", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - System and accessories", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_system_and_accessories', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_system_and_accessories",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfSystemAndAccessories",
		"graphql_plural_name" => "PfsSystemAndAccessories",
	];
	register_taxonomy( "filter_system_and_accessories", [ "product" ], $args );

	/**
	 * Taxonomy: F - Functional modules.
	 */

	$labels = [
		"name" => esc_html__( "F - Functional modules", "overstrap" ),
		"singular_name" => esc_html__( "F - Functional modules", "overstrap" ),
	];


	$args = [
		"label" => esc_html__( "F - Functional modules", "overstrap" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'filter_functional_modules', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "filter_functional_modules",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "PfFunctionalModules",
		"graphql_plural_name" => "PfsFunctionalModules",
	];
	register_taxonomy( "filter_functional_modules", [ "product" ], $args );

}
add_action( 'init', 'cptui_register_my_taxes' );
