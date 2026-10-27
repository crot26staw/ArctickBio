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
