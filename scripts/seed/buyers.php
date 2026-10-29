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

// «Где нас купить?» — города и адреса из макета (заглушки, кроме Екатеринбурга; реальные заполняются в админке), карта — демо.
carbon_set_post_meta( $page->ID, 'arctik_stores_title', 'Где нас купить?' );
// У каждого адреса своя карта (демо — точка на карте), карта по умолчанию — для адресов без своей
$map = fn( string $ll ) => '<iframe src="https://yandex.ru/map-widget/v1/?ll=' . $ll . '&z=16&pt=' . $ll . ',pm2rdm" width="560" height="400" frameborder="1" allowfullscreen="true"></iframe>';
carbon_set_post_meta( $page->ID, 'arctik_stores_cities', [
	[ 'name' => 'г. Екатеринбург', 'points' => [
		[ 'address' => 'ул. Сакко и Ванцетти, д.48', 'map' => $map( '60.603500,56.833700' ) ],
		[ 'address' => 'ул. Ленина, д. 24', 'map' => $map( '60.597474,56.842011' ) ],
		[ 'address' => 'ул. Ленина, д. 24', 'map' => $map( '60.597474,56.846011' ) ],
	] ],
	[ 'name' => 'г. Москва', 'points' => [ [ 'address' => 'ул. Ленина, д. 24', 'map' => $map( '37.617635,55.755814' ) ] ] ],
	[ 'name' => 'г. Владивосток', 'points' => [ [ 'address' => 'ул. Ленина, д. 24', 'map' => $map( '131.885485,43.115536' ) ] ] ],
	[ 'name' => 'г. Санкт-Петербург', 'points' => [ [ 'address' => 'ул. Ленина, д. 24', 'map' => $map( '30.315635,59.938951' ) ] ] ],
	[ 'name' => 'г. Тюмень', 'points' => [ [ 'address' => 'ул. Ленина, д. 24', 'map' => '' ] ] ],
] );
carbon_set_post_meta( $page->ID, 'arctik_stores_map_embed', $map( '60.597474,56.838011' ) );

WP_CLI::success( 'Покупателям заполнено' );
