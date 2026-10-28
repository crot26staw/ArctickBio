<?php
/**
 * Тема АрктикБио. Логики здесь нет — только подключение файлов из inc/ (см. WORDPRESS.md).
 */

defined( 'ABSPATH' ) || exit;

foreach ( [
	'setup',
	'cleanup',
	'vite',
	'typograph',
	'helpers',
	'carbon-fields',
	'post-types',
	'woocommerce',
	'product',
	'cart',
	'auth',
	'cdek',
	'shipping',
	'payment',
	'checkout',
	'order',
	'account',
	'seo',
	'documents',
	'ajax/cart',
	'ajax/auth',
	'ajax/delivery',
	'ajax/account',
] as $arctik_file ) {
	require_once get_theme_file_path( "inc/{$arctik_file}.php" );
}
