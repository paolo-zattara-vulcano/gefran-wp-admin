<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$info_text = 'Sorry, but nothing matched your search terms.';
$button_text = 'Back to Home Page.';

get_header();
?>

<main class="site-main col-md" id="main">
	<section class="container error-404 not-found d-flex justify-content-center align-items-center flex-column" fs-page="content">

		<header class="page-header">
			<h1 class="page-title mb-5">404</h1>
		</header>

		<div class="page-content text-center">

			<p><?= $info_text ?></p>
			<a href="<?= get_home_url() ?>" class="btn btn-primary">
				<?= $button_text ?>
			</a>

		</div>
	</section>
</main>


<?php get_footer(); ?>
