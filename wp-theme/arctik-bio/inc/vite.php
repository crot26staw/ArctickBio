<?php
/**
 * Подключение стилей и скриптов, собранных Vite (vite.config.js, режим wp).
 *
 * - npm run dev:wp — Vite пишет в тему файл hot с адресом dev-сервера: исходники грузятся с него (HMR).
 * - npm run build:wp — сборка в dist/, файлы с хешами берутся из dist/.vite/manifest.json.
 */

defined( 'ABSPATH' ) || exit;

const ARCTIK_VITE_SCRIPT = 'js/main.js';
const ARCTIK_VITE_STYLE  = 'scss/main.scss';
const ARCTIK_VITE_FONT   = 'assets/fonts/Involve-VF.woff2';

/**
 * Адрес dev-сервера Vite или null, если он не запущен.
 */
function arctik_vite_dev_origin(): ?string {
	static $origin;

	if ( null === $origin ) {
		$file   = get_theme_file_path( 'hot' );
		$origin = is_readable( $file ) ? rtrim( trim( (string) file_get_contents( $file ) ), '/' ) : '';
	}

	return '' === $origin ? null : $origin;
}

/**
 * manifest.json сборки.
 */
function arctik_vite_manifest(): array {
	static $manifest;

	if ( null === $manifest ) {
		$file     = get_theme_file_path( 'dist/.vite/manifest.json' );
		$manifest = is_readable( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : [];
	}

	return $manifest;
}

/**
 * URL файла сборки по пути исходника от src/ (как ключ в manifest.json).
 */
function arctik_vite_url( string $src ): ?string {
	$dev = arctik_vite_dev_origin();
	if ( $dev ) {
		return "{$dev}/{$src}";
	}

	$file = arctik_vite_manifest()[ $src ]['file'] ?? null;

	return $file ? get_theme_file_uri( "dist/{$file}" ) : null;
}

add_action( 'wp_enqueue_scripts', function () {
	$dev = arctik_vite_dev_origin();

	if ( $dev ) {
		wp_enqueue_script_module( 'arctik-vite-client', "{$dev}/@vite/client", [], null );
		// Стили в dev — модулем: так работает HMR.
		wp_enqueue_script_module( 'arctik-style', "{$dev}/" . ARCTIK_VITE_STYLE, [], null );
		wp_enqueue_script_module( 'arctik-main', "{$dev}/" . ARCTIK_VITE_SCRIPT, [], null );

		return;
	}

	$style  = arctik_vite_url( ARCTIK_VITE_STYLE );
	$script = arctik_vite_url( ARCTIK_VITE_SCRIPT );

	if ( ! $style || ! $script ) {
		trigger_error( 'АрктикБио: нет сборки темы — запустите npm run build:wp или npm run dev:wp', E_USER_WARNING );

		return;
	}

	wp_enqueue_style( 'arctik', $style, [], null );
	wp_enqueue_script_module( 'arctik-main', $script, [], null );
} );

// Предзагрузка основного шрифта (как в статике).
add_action( 'wp_head', function () {
	$font = arctik_vite_url( ARCTIK_VITE_FONT );

	if ( $font ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $font ) );
	}
}, 1 );
