<?php
/**
 * Наполнение «Покупателям» (этап «Б»): шаблон страницы и поля — тексты и картинки из статики.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/buyers.php
 */

require_once __DIR__ . '/_lib.php';

$page = get_page_by_path( 'buyers' );
if ( ! $page ) {
	WP_CLI::error( 'Нет страницы «Покупателям» — сначала /seed/base.php' );
}

update_post_meta( $page->ID, '_wp_page_template', 'page-templates/buyers.php' );

// В статике ответ во всех вопросах один (из макета), вопросы «Оплаты» и «Возврата» — копия «Общих».
$answer  = '15 саше — попробовать продукт и выбрать любимую ягоду. 30 саше — месяц ежедневного приёма';
$general = [
	'Какую упаковку выбрать: 15 или 30 стиков?',
	'Как принимать коллаген?',
	'Нужно ли хранить стики в холодильнике?',
	'Есть ли в коллагене вкус или запах рыбы?',
	'Сколько занимает доставка?',
	'Можно ли вернуть заказ?',
	'Есть ли скидка на курс?',
];
$delivery = array_merge( [ 'Куда вы доставляете заказы?', 'Какие способы доставки доступны?' ], array_slice( $general, 2 ) );

$questions = fn( array $list ) => array_map( fn( $question ) => [ 'question' => $question, 'answer' => $answer ], $list );

$contacts = get_page_by_path( 'contacts' );

carbon_set_post_meta( $page->ID, 'arctik_buyers_lead', "Есть вопросы?\nНайдите ответы здесь" );
carbon_set_post_meta( $page->ID, 'arctik_buyers_bg', arctik_seed_file( 'buyers-bg.webp' ) );
carbon_set_post_meta( $page->ID, 'arctik_buyers_note', sprintf(
	'Не нашли ответы на свои вопросы? <a href="%s">Напишите</a> нам, и мы ответим вам в кратчайшие сроки.',
	$contacts ? get_permalink( $contacts ) : '#'
) );
carbon_set_post_meta( $page->ID, 'arctik_buyers_groups', [
	[ 'title' => 'Общие вопросы', 'menu' => '', 'anchor' => 'help-general', 'questions' => $questions( $general ) ],
	[ 'title' => 'Доставка', 'menu' => '', 'anchor' => 'help-delivery', 'questions' => $questions( $delivery ) ],
	[ 'title' => 'Оплата', 'menu' => '', 'anchor' => 'help-payment', 'questions' => $questions( $general ) ],
	[ 'title' => 'Возврат', 'menu' => 'Возврат товара', 'anchor' => 'help-returns', 'questions' => $questions( $general ) ],
] );

carbon_set_post_meta( $page->ID, 'arctik_stores_title', 'Где нас купить?' );
carbon_set_post_meta( $page->ID, 'arctik_stores_cities', [
	[ 'name' => 'г. Екатеринбург', 'addresses' => "ул. Сакко и Ванцетти, д.48\nул. Ленина, д. 24\nул. Ленина, д. 24" ],
	[ 'name' => 'г. Москва', 'addresses' => 'ул. Ленина, д. 24' ],
	[ 'name' => 'г. Владивосток', 'addresses' => 'ул. Ленина, д. 24' ],
	[ 'name' => 'г. Санкт-Петербург', 'addresses' => 'ул. Ленина, д. 24' ],
	[ 'name' => 'г. Тюмень', 'addresses' => 'ул. Ленина, д. 24' ],
] );
carbon_set_post_meta( $page->ID, 'arctik_stores_map', arctik_seed_file( 'buyers-map.webp', 'Карта магазинов' ) );

WP_CLI::success( 'Покупателям заполнено' );
