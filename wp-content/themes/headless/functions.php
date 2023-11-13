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
	'/rest-cpq.php',                           	// Custom REST APIs
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



function delete_revisions_multisite_batch() {
    global $wpdb;

    // Batch size
    $batch_size = 100;

    // Get all sites in the network
    $blog_ids = get_sites( array( 'fields' => 'ids' ) );

    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );

        do {
            // Get revision IDs in batches
            $revision_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision' LIMIT $batch_size" );

            if ( empty( $revision_ids ) ) {
                break;
            }

            foreach ( $revision_ids as $revision_id ) {
                wp_delete_post_revision( $revision_id );
            }

        } while ( count( $revision_ids ) == $batch_size );

				error_log('Blog ID: ' . $blog_id . ' done');

        restore_current_blog();
    }

    return 'Batch deletion of revisions complete.';
}

// delete_revisions_multisite_batch();
