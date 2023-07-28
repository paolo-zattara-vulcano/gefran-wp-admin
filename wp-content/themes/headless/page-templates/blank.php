<?php
/**
 * Template Name: Blank Page Template
 *
 * Template for displaying a blank page.
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body>
	<?php while ( have_posts() ) : the_post(); ?>
		<main class="blank-template container-fluid h-100 d-flex justify-content-center align-items-center flex-column" id="main" style="min-height: 100vh;">
			<?php get_template_part( 'templates/loop/content', 'blank' ); ?>
		</main>
	<?php endwhile; // end of the loop. ?>
	<?php wp_footer(); ?>
</body>
</html>
