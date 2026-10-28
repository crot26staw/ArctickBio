<?php
/**
 * Доставка на оформлении: свои wc-ajax обработчики (?wc-ajax=arctik_…), nonce корзины (arctik-cart, cart-api.js).
 * Ответ — { success, data, fragments }: фрагменты WC (способы получения, итоги — inc/checkout.php) обновляют форму,
 * data — для попапа пункта выдачи (pickup.js).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Данные попапа пункта выдачи для города покупателя: город, пункты, стоимость и даты (тариф до пункта), службы.
 * Нет тарифа до города — пунктов нет (в попапе — «Пунктов выдачи нет»).
 */
function arctik_pickup_data(): array {
	$city  = arctik_city();
	$rate  = current( array_filter( arctik_shipping_rates(), fn( $rate ) => 'pickup' === $rate['slot'] ) );
	$cost  = $rate ? (float) $rate['rate']->get_cost() + (float) $rate['rate']->get_shipping_tax() : 0;
	$point = fn( array $point ): array => [
		'id'      => $point['id'],
		'service' => 'cdek',
		'title'   => 'ПВЗ СДЭК',
		'address' => $city['name'] . ', ' . $point['address'],
		'time'    => $point['time'],
		'lat'     => $point['lat'],
		'lon'     => $point['lon'],
	];

	return [
		'city'   => $city,
		'points' => $rate ? array_map( $point, arctik_cdek_points( $city['code'] ) ) : [],
		'price'  => $cost > 0 ? arctik_price_text( $cost ) : 'бесплатно',
		'date'   => $rate ? $rate['dates'] : '',
	];
}

/**
 * Ответ с пересчитанной корзиной: data + фрагменты WC (как WC_AJAX::get_refreshed_fragments).
 */
function arctik_delivery_send( array $data = [] ): void {
	WC()->cart->calculate_totals();

	wp_send_json( [
		'success'   => true,
		'data'      => $data,
		'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', [] ),
	] );
}

function arctik_delivery_check_nonce(): void {
	if ( ! check_ajax_referer( 'arctik-cart', 'security', false ) ) {
		wp_send_json_error( [ 'message' => 'Сессия устарела, обновите страницу' ], 403 );
	}
}

// Подсказки города: query (от 2 букв).
add_action( 'wc_ajax_arctik_city_suggest', function () {
	arctik_delivery_check_nonce();

	$query = sanitize_text_field( wp_unslash( $_POST['query'] ?? '' ) );

	wp_send_json_success( [ 'cities' => mb_strlen( $query ) < 2 ? [] : arctik_cdek_suggest_cities( $query ) ] );
} );

// Выбрать город: code (СДЭК). Город проверяется по справочнику СДЭК; ответ — данные попапа и фрагменты формы.
add_action( 'wc_ajax_arctik_city_set', function () {
	arctik_delivery_check_nonce();

	$found = arctik_cdek_request( 'GET', '/location/cities', [
		'code'         => absint( $_POST['code'] ?? 0 ),
		'country_code' => 'RU',
	] )[0] ?? null;

	if ( ! $found ) {
		wp_send_json_error( [ 'message' => 'Город не найден' ] );
	}

	$name   = (string) $found['city'];
	$region = (string) ( $found['region'] ?? '' );
	arctik_set_city( [
		'code'  => (int) $found['code'],
		'name'  => $name,
		'label' => $name . ', ' . ( $region && $region !== $name ? $region : 'Россия' ),
	] );

	WC()->cart->calculate_totals();
	arctik_delivery_send( arctik_pickup_data() );
} );

// Пункты выдачи города покупателя (при открытии попапа).
add_action( 'wc_ajax_arctik_pickup_points', function () {
	arctik_delivery_check_nonce();

	WC()->cart->calculate_totals();
	wp_send_json_success( arctik_pickup_data() );
} );

// Способ получения: method — ID ставки (arctik_pickup:3). Итоги и способы — фрагментами.
add_action( 'wc_ajax_arctik_checkout_shipping', function () {
	arctik_delivery_check_nonce();

	$method = sanitize_text_field( wp_unslash( $_POST['method'] ?? '' ) );
	if ( arctik_shipping_slot( $method ) ) {
		WC()->session->set( 'chosen_shipping_methods', [ $method ] );
	}

	arctik_delivery_send();
} );
