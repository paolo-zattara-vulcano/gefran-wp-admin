<?php
/**
 * The header for our theme.
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
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<link rel="icon" type="image/png" sizes="32x32" href="<?= get_template_directory_uri() ?>/dist/images/favicon/gefran-favicon.svg">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= get_template_directory_uri() ?>/dist/images/favicon/gefran-favicon.svg">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
