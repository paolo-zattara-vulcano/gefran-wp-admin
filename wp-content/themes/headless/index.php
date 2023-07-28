<?php
/**
 * The main template file.
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

get_header();

// Check if the URL contains the specified endpoint and key/value pair
$uri = $_SERVER['REQUEST_URI'];
$endpoint = 'endpoints/investors';
$key = isset($_GET["key"]) ? $_GET["key"] : '';
$key_value = "nb3aNpukKYKicLmgegzhJJfr";

// Get the subsite folder path
$subsite_folder = get_blog_details()->path;

if (strpos($uri, $subsite_folder . $endpoint) !== false && $key === $key_value) {
    // Save the ID of the current site
    $original_site = get_current_blog_id();

		$args = array(
		    'post_type' => 'page',
		    'posts_per_page' => -1,
		    'meta_query' => array(
		        'relation' => 'OR',
		        array(
		            'key' => '_wp_page_template',
		            'value' => 'page-templates/investors-home.php',
		        ),
		        array(
		            'key' => '_wp_page_template',
		            'value' => 'page-templates/investors-meetings.php',
		        ),
		    ),
		);
    $pages = get_posts( $args );

    foreach ( $pages as $page ) {
        $page->post_content = rand(0, 9999);
        $page->post_date = current_time('mysql');
        $page->post_date_gmt = current_time('mysql', true);

        echo "Updated page ID " . $page->ID . " for blog " . $original_site . ": " . $page->post_content . "<br>";
        wp_update_post( $page );
    }

    // Only call the home page URL of other sites in the network if the original site is 1
		if ($original_site == 1) {
        // Get all sites in the network
        $sites = get_sites();

        foreach ( $sites as $site ) {
            if ($site->blog_id != 1) {
                switch_to_blog( $site->blog_id );
                $unique_param = time();
                // Add the endpoint to $homepage_url
                $homepage_url = home_url($endpoint . "?key=" . $key . "&nocache=" . $unique_param);
								// $response = wp_remote_get($homepage_url, array('timeout' => 30));
								$response = wp_remote_get($homepage_url);

                // Debugging information
                echo "Calling URL for blog " . $site->blog_id . ": " . $homepage_url . "<br>";
                // echo "Response code: " . wp_remote_retrieve_response_code($response) . "<br>";
                // echo "Response message: " . wp_remote_retrieve_response_message($response) . "<br>";
                // echo "Response body: <pre>" . wp_remote_retrieve_body($response) . "</pre><br>";

                restore_current_blog();
            }
        }
    }
} else { ?>

 <main class="site-main container d-flex flex-column justify-content-center align-items-center" id="main" style="height: 100vh;">

    <img src="<?= esc_url(get_template_directory_uri()) ?>/dist/images/svg/gefran-logo.svg">

    <?php if ( have_posts() ) { ?>

        <?php while ( have_posts() ) : the_post();?>

        <?php endwhile; ?>

    <?php } else { ?>

    <h1>404 - nothing here</h1>

    <?php } ?>

</main>

<?php
}

get_footer(); ?>
