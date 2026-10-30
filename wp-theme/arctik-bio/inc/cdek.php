<?php
/**
 * API СДЭК (v2): токен, города, пункты выдачи, расчёт тарифа. Настройки — «Настройки сайта → Доставка» (inc/fields/delivery.php).
 * Тестовый режим — api.edu.cdek.ru; пустые доступы в нём — общий тестовый аккаунт из документации СДЭК.
 * Ответы кешируются (транзиенты): токен — на срок жизни, пункты города — 12 часов, тариф — час. Ошибки — в журнал WC (источник arctik-cdek).
 *
 * Город покупателя хранится в сессии WC (arctik_city()): по нему считаются стоимость и срок доставки и выводятся пункты выдачи.
 */

defined( 'ABSPATH' ) || exit;

const ARCTIK_CDEK_TEST_ID     = 'wqGwiQx0gg8mLtiEKsUinjVSICCjtTEP';
const ARCTIK_CDEK_TEST_SECRET = 'RmAmgvSgSl1yirlz9QupbzOJVqhCxcP5';

// Город по умолчанию — пока покупатель не выбрал свой
const ARCTIK_DEFAULT_CITY = [
	'code'  => 44,
	'name'  => 'Москва',
	'label' => 'Москва, Россия',
];

function arctik_cdek_is_test(): bool {
	return (bool) arctik_option( 'arctik_cdek_test' );
}

function arctik_cdek_base(): string {
	return arctik_cdek_is_test() ? 'https://api.edu.cdek.ru/v2' : 'https://api.cdek.ru/v2';
}

function arctik_cdek_log( string $message ): void {
	wc_get_logger()->warning( $message, [ 'source' => 'arctik-cdek' ] );
}

/**
 * Токен OAuth (client_credentials), кеш — на срок жизни минус минута. Нет доступов / ошибка — ''.
 */
function arctik_cdek_token(): string {
	$id     = trim( (string) arctik_option( 'arctik_cdek_client_id' ) );
	$secret = trim( (string) arctik_option( 'arctik_cdek_client_secret' ) );

	if ( arctik_cdek_is_test() && ( ! $id || ! $secret ) ) {
		[ $id, $secret ] = [ ARCTIK_CDEK_TEST_ID, ARCTIK_CDEK_TEST_SECRET ];
	}
	if ( ! $id || ! $secret ) {
		return '';
	}

	$key   = 'arctik_cdek_token_' . md5( arctik_cdek_base() . $id );
	$token = get_transient( $key );
	if ( $token ) {
		return $token;
	}

	$response = wp_remote_post( arctik_cdek_base() . '/oauth/token', [
		'timeout' => 10,
		'body'    => [
			'grant_type'    => 'client_credentials',
			'client_id'     => $id,
			'client_secret' => $secret,
		],
	] );
	$body = is_wp_error( $response ) ? [] : json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $body['access_token'] ) ) {
		arctik_cdek_log( 'Токен: ' . ( is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_body( $response ) ) );
		return '';
	}

	set_transient( $key, $body['access_token'], max( 60, (int) ( $body['expires_in'] ?? 3600 ) - 60 ) );

	return $body['access_token'];
}

/**
 * Запрос к API: GET с параметрами или POST с JSON. Ответ — массив из JSON, ошибка — null (в журнал).
 */
function arctik_cdek_request( string $method, string $path, array $data = [] ): ?array {
	$token = arctik_cdek_token();
	if ( ! $token ) {
		return null;
	}

	$args = [
		'method'  => $method,
		'timeout' => 15,
		'headers' => [ 'Authorization' => 'Bearer ' . $token ],
	];
	$url  = arctik_cdek_base() . $path;

	if ( 'GET' === $method ) {
		$url = add_query_arg( array_map( 'rawurlencode', $data ), $url );
	} else {
		$args['headers']['Content-Type'] = 'application/json';
		$args['body']                    = wp_json_encode( $data );
	}

	$response = wp_remote_request( $url, $args );
	$code     = (int) wp_remote_retrieve_response_code( $response );
	$body     = is_wp_error( $response ) ? null : json_decode( wp_remote_retrieve_body( $response ), true );

	if ( $code < 200 || $code >= 300 || ! is_array( $body ) ) {
		arctik_cdek_log( sprintf( '%s %s → %s', $method, $path, is_wp_error( $response ) ? $response->get_error_message() : $code . ' ' . wp_remote_retrieve_body( $response ) ) );
		return null;
	}

	return $body;
}

/**
 * Подсказки городов по началу названия: [ [ 'code', 'name', 'label', 'full' ], … ] (до 10).
 */
function arctik_cdek_suggest_cities( string $query ): array {
	$cities = arctik_cdek_request( 'GET', '/location/suggest/cities', [
		'name'         => $query,
		'country_code' => 'RU',
	] ) ?? [];

	return array_map( 'arctik_cdek_city', array_slice( $cities, 0, 10 ) );
}

/**
 * Город из ответа СДЭК: full_name «Екатеринбург, городской округ Екатеринбург, Свердловская область, Россия» →
 * name «Екатеринбург», label «Екатеринбург, Свердловская область» (как «Москва, Россия» в макете).
 */
function arctik_cdek_city( array $city ): array {
	$parts  = array_map( 'trim', explode( ',', (string) ( $city['full_name'] ?? '' ) ) );
	$name   = $parts[0];
	$region = count( $parts ) > 2 ? $parts[ count( $parts ) - 2 ] : ( $parts[1] ?? 'Россия' );

	return [
		'code'  => (int) ( $city['code'] ?? 0 ),
		'name'  => $name,
		'label' => $name === $region ? $name . ', Россия' : $name . ', ' . $region,
		'full'  => implode( ', ', $parts ),
	];
}

/**
 * Пункты выдачи города (только выдающие посылки): [ [ 'id', 'address', 'lat', 'lon', 'time' ], … ]; кеш 12 часов.
 */
function arctik_cdek_points( int $city_code ): array {
	$key    = 'arctik_cdek_points_' . md5( arctik_cdek_base() . $city_code );
	$points = get_transient( $key );
	if ( is_array( $points ) ) {
		return $points;
	}

	$response = arctik_cdek_request( 'GET', '/deliverypoints', [
		'city_code'  => $city_code,
		'type'       => 'PVZ',
		'is_handout' => 'true',
	] );
	if ( null === $response ) {
		return [];
	}

	$points = [];
	foreach ( $response as $point ) {
		if ( empty( $point['code'] ) || empty( $point['location']['latitude'] ) ) {
			continue;
		}
		$points[] = [
			'id'      => (string) $point['code'],
			'address' => (string) ( $point['location']['address'] ?? '' ),
			'lat'     => (float) $point['location']['latitude'],
			'lon'     => (float) $point['location']['longitude'],
			'time'    => (string) ( $point['work_time'] ?? '' ),
		];
	}

	set_transient( $key, $points, 12 * HOUR_IN_SECONDS );

	return $points;
}

/**
 * Пункт выдачи по коду среди пунктов города или null.
 */
function arctik_cdek_point( int $city_code, string $id ): ?array {
	foreach ( arctik_cdek_points( $city_code ) as $point ) {
		if ( $point['id'] === $id ) {
			return $point;
		}
	}

	return null;
}

/**
 * Расчёт тарифа: [ 'sum' => 210.0, 'min' => 3, 'max' => 4, 'date_min' => 'Y-m-d', 'date_max' => 'Y-m-d' ] или null; кеш час.
 * $package — [ 'weight' (г), 'length', 'width', 'height' (см) ].
 */
function arctik_cdek_tariff( int $tariff, int $city_code, array $package ): ?array {
	$data = [
		'tariff_code'   => $tariff,
		'from_location' => [ 'code' => (int) ( arctik_option( 'arctik_cdek_from_city' ) ?: 44 ) ],
		'to_location'   => [ 'code' => $city_code ],
		'packages'      => [ $package ],
	];

	$key    = 'arctik_cdek_tariff_' . md5( arctik_cdek_base() . wp_json_encode( $data ) );
	$result = get_transient( $key );
	if ( is_array( $result ) ) {
		return $result;
	}

	$response = arctik_cdek_request( 'POST', '/calculator/tariff', $data );
	if ( ! isset( $response['total_sum'] ) && ! isset( $response['delivery_sum'] ) ) {
		return null;
	}

	$min    = (int) ( $response['calendar_min'] ?? $response['period_min'] ?? 0 );
	$max    = (int) ( $response['calendar_max'] ?? $response['period_max'] ?? $min );
	$today  = new DateTimeImmutable( 'today', wp_timezone() );
	$result = [
		'sum'      => (float) ( $response['total_sum'] ?? $response['delivery_sum'] ),
		'min'      => $min,
		'max'      => $max,
		'date_min' => (string) ( $response['delivery_date_range']['min'] ?? $today->modify( "+{$min} day" )->format( 'Y-m-d' ) ),
		'date_max' => (string) ( $response['delivery_date_range']['max'] ?? $today->modify( "+{$max} day" )->format( 'Y-m-d' ) ),
	];

	set_transient( $key, $result, HOUR_IN_SECONDS );

	return $result;
}

/**
 * Упаковка корзины (пакета доставки WC): вес — сумма весов товаров (без веса — «Вес» из настроек), длина и ширина — наибольшие,
 * высота — сумма высот (единицы товара стопкой). Габариты товара — из WC, иначе из настроек.
 */
function arctik_cdek_package( array $contents ): array {
	$default = [
		'weight' => (int) ( arctik_option( 'arctik_cdek_weight' ) ?: 300 ),
		'length' => (int) ( arctik_option( 'arctik_cdek_length' ) ?: 15 ),
		'width'  => (int) ( arctik_option( 'arctik_cdek_width' ) ?: 10 ),
		'height' => (int) ( arctik_option( 'arctik_cdek_height' ) ?: 8 ),
	];
	$package = [
		'weight' => 0,
		'length' => 0,
		'width'  => 0,
		'height' => 0,
	];

	foreach ( $contents as $item ) {
		$product = $item['data'] ?? null;
		$qty     = (int) ( $item['quantity'] ?? 1 );
		if ( ! $product instanceof WC_Product ) {
			continue;
		}

		$weight = $product->get_weight() ? (int) ceil( wc_get_weight( (float) $product->get_weight(), 'g' ) ) : $default['weight'];
		$size   = [
			'length' => $product->get_length() ? (int) ceil( wc_get_dimension( (float) $product->get_length(), 'cm' ) ) : $default['length'],
			'width'  => $product->get_width() ? (int) ceil( wc_get_dimension( (float) $product->get_width(), 'cm' ) ) : $default['width'],
			'height' => $product->get_height() ? (int) ceil( wc_get_dimension( (float) $product->get_height(), 'cm' ) ) : $default['height'],
		];

		$package['weight'] += $weight * $qty;
		$package['length']  = max( $package['length'], $size['length'] );
		$package['width']   = max( $package['width'], $size['width'] );
		$package['height'] += $size['height'] * $qty;
	}

	return $package['weight'] ? $package : $default;
}

// ---------- Город покупателя ----------

/**
 * Город покупателя из сессии WC: [ 'code', 'name', 'label' ]; не выбран — Москва.
 */
function arctik_city(): array {
	$city = WC()->session ? WC()->session->get( 'arctik_city' ) : null;

	return is_array( $city ) && ! empty( $city['code'] ) ? $city : ARCTIK_DEFAULT_CITY;
}

/**
 * Запомнить город: сессия + город доставки покупателя WC (меняет хеш пакета — доставка пересчитывается).
 */
function arctik_set_city( array $city ): void {
	WC()->session->set( 'arctik_city', [
		'code'  => (int) $city['code'],
		'name'  => (string) $city['name'],
		'label' => (string) $city['label'],
	] );
	WC()->customer->set_shipping_city( (string) $city['name'] );
	WC()->customer->set_billing_city( (string) $city['name'] );
	WC()->customer->save();
}

// Код города — в пакет доставки WC: по нему считают способы доставки, и он входит в хеш пакета (кеш ставок WC).
add_filter( 'woocommerce_cart_shipping_packages', function ( array $packages ): array {
	foreach ( $packages as &$package ) {
		$package['arctik_city_code'] = arctik_city()['code'];
	}

	return $packages;
} );

// Сохранили «Настройки сайта» (доступы, город отправки, тарифы, упаковка) — сбросить кеш ставок WC в сессиях покупателей:
// иначе ставки, посчитанные со старыми настройками (пустой список — «доставки пока нет»), живут до смены корзины.
add_action( 'carbon_fields_theme_options_container_saved', function (): void {
	if ( class_exists( 'WC_Cache_Helper' ) ) {
		WC_Cache_Helper::get_transient_version( 'shipping', true );
	}
} );

// ---------- Тексты ----------

/**
 * «3–4 дня», «1 день» — срок доставки.
 */
function arctik_days_text( int $min, int $max ): string {
	$word = function ( int $n ): string {
		$mod10  = $n % 10;
		$mod100 = $n % 100;

		return 1 === $mod10 && 11 !== $mod100 ? 'день' : ( $mod10 >= 2 && $mod10 <= 4 && ( $mod100 < 12 || $mod100 > 14 ) ? 'дня' : 'дней' );
	};

	if ( $max <= 0 ) {
		return 'Завтра';
	}

	return ( $min > 0 && $min < $max ? $min . '–' : '' ) . $max . "\u{00A0}" . $word( $max );
}

/**
 * «31 окт» или «31 окт – 2 нояб» — даты доставки.
 */
function arctik_dates_text( string $min, string $max ): string {
	$months = [ 'янв', 'февр', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сент', 'окт', 'нояб', 'дек' ];
	$format = fn( string $ymd ): string => (int) substr( $ymd, 8, 2 ) . "\u{00A0}" . $months[ (int) substr( $ymd, 5, 2 ) - 1 ];

	return $min === $max || ! $max ? $format( $min ) : $format( $min ) . ' – ' . $format( $max );
}
