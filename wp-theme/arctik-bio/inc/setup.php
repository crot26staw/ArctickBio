<?php
/**
 * Возможности темы.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
} );

// Класс js на <html> — до разбивки заголовков они скрыты (как в статике, см. CLAUDE.md → «Заголовки и описания»).
add_action( 'wp_head', function () {
	wp_print_inline_script_tag( "document.documentElement.classList.add('js');" );
}, 0 );

add_action( 'after_setup_theme', function () {
	register_nav_menus( [
		'header'        => 'Шапка',
		'mobile'        => 'Мобильное меню',
		'footer_about'  => 'Подвал: «О нас»',
		'footer_buyers' => 'Подвал: «Покупателям»',
		'footer_docs'   => 'Подвал: «Документы»',
	] );
} );

// Админ-бар на сайте скрыт: перекрывает фиксированную шапку и сбивает закреплённые сцены (решение пользователя).
add_filter( 'show_admin_bar', '__return_false' );

// Ссылка на секцию страницы (/buyers/#where-to-buy) — не «текущая страница»: WP сравнивает адрес без якоря и подсвечивал её.
add_filter( 'wp_nav_menu_objects', function ( array $items ): array {
	foreach ( $items as $item ) {
		if ( 'custom' === $item->type && str_contains( (string) $item->url, '#' ) && $item->current ) {
			$item->current = false;
			$item->classes = array_diff( (array) $item->classes, [ 'current-menu-item' ] );
		}
	}

	return $items;
} );
