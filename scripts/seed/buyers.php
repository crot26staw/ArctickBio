<?php
/**
 * Наполнение «Покупателям» (этап «Б»): шаблон страницы и поля — тексты и картинки из статики, вопросы — из документа заказчика.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/buyers.php
 */

require_once __DIR__ . '/_lib.php';

$page = get_page_by_path( 'buyers' );
if ( ! $page ) {
	WP_CLI::error( 'Нет страницы «Покупателям» — сначала /seed/base.php' );
}

update_post_meta( $page->ID, '_wp_page_template', 'page-templates/buyers.php' );

// Вопросы — документ заказчика (этап Кт2): разделы по порядку, названия разделов и якоря — как в вёрстке.
$sections = [
	[ 'title' => 'Общие вопросы', 'menu' => '', 'anchor' => 'help-general' ],
	[ 'title' => 'Доставка', 'menu' => '', 'anchor' => 'help-delivery' ],
	[ 'title' => 'Оплата', 'menu' => '', 'anchor' => 'help-payment' ],
	[ 'title' => 'Возврат', 'menu' => 'Возврат товара', 'anchor' => 'help-returns' ],
];
$faq = arctik_seed_faq( 'ArcticBerries_FAQ_pokupatelyam.txt' );

$contacts = get_page_by_path( 'contacts' );

carbon_set_post_meta( $page->ID, 'arctik_buyers_lead', "Есть вопросы?\nНайдите ответы здесь" );
carbon_set_post_meta( $page->ID, 'arctik_buyers_bg', arctik_seed_file( 'buyers-bg.webp' ) );
carbon_set_post_meta( $page->ID, 'arctik_buyers_note', sprintf(
	'Не нашли ответы на свои вопросы? <a href="%s">Напишите</a> нам, и мы ответим вам в кратчайшие сроки.',
	$contacts ? get_permalink( $contacts ) : '#'
) );
carbon_set_post_meta( $page->ID, 'arctik_buyers_groups', array_map(
	fn( $section, $group ) => $section + [ 'questions' => $group['questions'] ],
	$sections,
	array_slice( $faq, 0, count( $sections ) )
) );

// «Где нас купить?» пока скрыт (просьба пользователя) — поля закомментированы в inc/fields/buyers.php.
/*
carbon_set_post_meta( $page->ID, 'arctik_stores_title', 'Где нас купить?' );
carbon_set_post_meta( $page->ID, 'arctik_stores_cities', [
	[ 'name' => 'г. Екатеринбург', 'addresses' => "ул. Сакко и Ванцетти, д.48\nул. Ленина, д. 24\nул. Ленина, д. 24" ],
	[ 'name' => 'г. Москва', 'addresses' => 'ул. Ленина, д. 24' ],
	[ 'name' => 'г. Владивосток', 'addresses' => 'ул. Ленина, д. 24' ],
	[ 'name' => 'г. Санкт-Петербург', 'addresses' => 'ул. Ленина, д. 24' ],
	[ 'name' => 'г. Тюмень', 'addresses' => 'ул. Ленина, д. 24' ],
] );
carbon_set_post_meta( $page->ID, 'arctik_stores_map', arctik_seed_file( 'buyers-map.webp', 'Карта магазинов' ) );
*/

WP_CLI::success( 'Покупателям заполнено' );
