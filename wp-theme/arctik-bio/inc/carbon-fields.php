<?php
/**
 * Carbon Fields: загрузка из vendor/ (composer install) и регистрация полей из inc/fields/*.php.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	$autoload = get_theme_file_path( 'vendor/autoload.php' );

	if ( ! is_readable( $autoload ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>АрктикБио: не установлен Carbon Fields — выполните <code>composer install</code> в папке темы.</p></div>';
		} );

		return;
	}

	require_once $autoload;
	\Carbon_Fields\Carbon_Fields::boot();
} );

add_action( 'carbon_fields_register_fields', function () {
	foreach ( glob( get_theme_file_path( 'inc/fields/*.php' ) ) ?: [] as $file ) {
		require_once $file;
	}
} );
