<?php
/**
 * overstrap functions and definitions
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$overstrap_includes = array(
	//'/hooks.php',                           // Custom hooks.
	'/rest.php',                           	// Custom REST APIs
	'/rest-new.php',                           	// Custom REST APIs
	'/setup.php',                           // Theme setup and custom theme supports.
	'/widgets.php',                         // Register widget area.
	'/enqueue.php',                         // Enqueue scripts and styles.
	'/editor.php',                          // Load Editor functions.
	'/rwcustom.php',                       // RW Custom functions.
	'/multilingualpress.php',                       // RW Custom functions.
	'/rewrite-rules.php',                        // RW Custom functions.
	'/shared-media.php',                        // Shared Media Library thru networks sites.
	'/graphql.php',                          // GraphQl Filters.
	'/global-header-menu.php',               // Gefran Admin Network Sites
	'/yoast-seo.php',                        // RW Custom functions.

	// CPT - CTX
	'/cptui/cpt.php',
	'/cptui/ctx.php',

	// PLUGINS
	'/plugins/duplicator/original-id-handler.php',
	'/plugins/duplicator/duplicator.php',
	'/plugins/multisite-global-sync/multisite-global-sync.php',
	'/plugins/gatsby-builder-and-notifier/gatsby-builder-and-notifier.php',

	// --------------------------------------- Utilities
	// '/moduli/_util-check-page-template.php',	// report page template

);

foreach ( $overstrap_includes as $file ) {
	$filepath = locate_template( 'inc' . $file );
	if ( ! $filepath ) {
		trigger_error( sprintf( 'Error locating /inc%s for inclusion', $file ), E_USER_ERROR );
	}
	require_once $filepath;
}
