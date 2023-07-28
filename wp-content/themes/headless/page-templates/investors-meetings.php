<?php
/**
 * Template Name: Investors - Shareholders meetings
 *
 * Template for displaying a blank page.
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
get_header();
?>


 <main class="site-main container d-flex flex-column justify-content-center align-items-center" id="main" style="height: 100vh;">

	<img src="<?= esc_url(get_template_directory_uri()) ?>/dist/images/svg/gefran-logo.svg">

	<?php if ( have_posts() ) { ?>

		<?php while ( have_posts() ) : the_post(); ?>

		<?php endwhile; ?>

	<?php } else { ?>

	<h1>404 - nothing here</h1>

	<?php } ?>

</main>


<?php get_footer(); ?>
