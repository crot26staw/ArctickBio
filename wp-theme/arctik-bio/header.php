<?php
/**
 * Шапка документа. Разметка шапки сайта (partials/header.html) — этап 0.3.
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'page' ); ?>>
<?php wp_body_open(); ?>
