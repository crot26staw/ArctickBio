<?php
/**
 * WooCommerce: поддержка в теме, отключение его стилей (вёрстка своя).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'woocommerce' );
} );

add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

add_action( 'wp_enqueue_scripts', function () {
	foreach ( [ 'wc-blocks-style', 'wc-blocks-vendors-style', 'woocommerce-inline' ] as $handle ) {
		wp_dequeue_style( $handle );
	}
}, 100 );

// Каталог — все товары на одной странице (без пагинации, как в макете).
add_filter( 'loop_shop_per_page', fn() => 100 );

// Галерея и форма покупки на странице товара — свои (drag-slider.js, cart.js), jQuery-скрипт WC не нужен.
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_script( 'wc-single-product' );
}, 100 );
