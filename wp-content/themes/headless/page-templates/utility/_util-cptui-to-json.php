<?php
/*
Template Name: CPTUI Export to json
*/
// https://docs.pluginize.com/article/84-save-cptui-settings-data-to-file

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">


			<?php
			/**
			 * Saves post type and taxonomy data to JSON files in the theme directory.
			 *
			 * @param array $data Array of post type data that was just saved.
			 */
			function pluginize_local_cptui_data( $data = array() ) {
			    $theme_dir = get_stylesheet_directory();
			    // Create our directory if it doesn't exist.
			    if ( ! is_dir( $theme_dir .= '/cptui_data' ) ) {
			        mkdir( $theme_dir, 0755 );
			    }

			        // Fetch all of our post types and encode into JSON.
			        $cptui_post_types = get_option( 'cptui_post_types', array() );
			        $content = json_encode( $cptui_post_types );
			        // Save the encoded JSON to a primary file holding all of them.
			        file_put_contents( $theme_dir . '/cptui_post_type_data.json', $content );

			        // Fetch all of our taxonomies and encode into JSON.
			        $cptui_taxonomies = get_option( 'cptui_taxonomies', array() );
			        $content = json_encode( $cptui_taxonomies );
			        // Save the encoded JSON to a primary file holding all of them.
			        file_put_contents( $theme_dir . '/cptui_taxonomy_data.json', $content );
			}

			pluginize_local_cptui_data();

			?>


		</main><!-- #main -->
	</div><!-- #primary -->

<?php // get_sidebar(); ?>
<?php get_footer(); ?>
