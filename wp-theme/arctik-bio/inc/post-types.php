<?php
/**
 * Свои типы записей (только согласованные с пользователем, см. WORDPRESS.md → «Принятые решения»).
 */

defined( 'ABSPATH' ) || exit;

// Новости «О компании»: без своей страницы, архива и URL — только записи для слайдера (решение пользователя).
add_action( 'init', function () {
	register_post_type( 'arctik_news', [
		'labels'             => [
			'name'               => 'Новости',
			'singular_name'      => 'Новость',
			'menu_name'          => 'Новости',
			'add_new'            => 'Добавить новость',
			'add_new_item'       => 'Добавить новость',
			'edit_item'          => 'Редактировать новость',
			'new_item'           => 'Новая новость',
			'view_item'          => 'Просмотреть новость',
			'search_items'       => 'Искать новости',
			'not_found'          => 'Новостей нет',
			'not_found_in_trash' => 'В корзине новостей нет',
			'all_items'          => 'Все новости',
		],
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_rest'       => false,
		'has_archive'        => false,
		'rewrite'            => false,
		'menu_icon'          => 'dashicons-megaphone',
		'menu_position'      => 21,
		'supports'           => [ 'title', 'excerpt' ],
	] );
} );

// Отзывы на главной: без своей страницы, архива и URL — только записи для слайдера (решение пользователя).
add_action( 'init', function () {
	register_post_type( 'arctik_review', [
		'labels'             => [
			'name'               => 'Отзывы',
			'singular_name'      => 'Отзыв',
			'menu_name'          => 'Отзывы',
			'add_new'            => 'Добавить отзыв',
			'add_new_item'       => 'Добавить отзыв',
			'edit_item'          => 'Редактировать отзыв',
			'new_item'           => 'Новый отзыв',
			'view_item'          => 'Просмотреть отзыв',
			'search_items'       => 'Искать отзывы',
			'not_found'          => 'Отзывов нет',
			'not_found_in_trash' => 'В корзине отзывов нет',
			'all_items'          => 'Все отзывы',
		],
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_rest'       => false,
		'has_archive'        => false,
		'rewrite'            => false,
		'menu_icon'          => 'dashicons-star-filled',
		'menu_position'      => 22,
		'supports'           => [ 'title' ],
	] );
} );
