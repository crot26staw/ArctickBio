<?php
/**
 * Начало документа страниц оформления (src/checkout.html): фон страницы и шапка без меню (template-parts/checkout/header.php).
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
<body <?php body_class( 'page page--checkout' ); ?>>
<?php wp_body_open(); ?>
<img class="page__bg" src="<?php echo esc_url( arctik_asset( 'images/checkout-bg.webp' ) ); ?>" alt="" width="936" height="1024">
<?php get_template_part( 'template-parts/checkout/header' ); ?>
