<?php
/**
 * Способы доставки оформления заказа: «Пункт выдачи» (arctik_pickup) и «Курьер» (arctik_courier) — СДЭК.
 * Добавляются в зону доставки (WooCommerce → Настройки → Доставка), у каждого — название и наценка.
 * Стоимость и срок — тариф СДЭК (inc/cdek.php) до города покупателя (arctik_city()); тарифы — «Настройки сайта → Доставка».
 * От суммы товаров «Бесплатная доставка от» («Настройки сайта → Доставка → Условия», 5 000 ₽ от заказчика) — стоимость 0.
 * СДЭК не ответил или не возит в город — способа нет. Блок способа в форме (pickup / courier) — по ID способа, arctik_shipping_slot().
 * Адрес курьера и дата пока — демо-попап и даты с завтрашнего дня (этап Оф2б — DaData и даты по сроку СДЭК).
 */

defined( 'ABSPATH' ) || exit;

const ARCTIK_SHIPPING_SLOTS = [
	'arctik_pickup'  => 'pickup',
	'arctik_courier' => 'courier',
];

/**
 * Блок способа в форме оформления по ID способа или ставки («arctik_pickup:3» → pickup); чужой способ — ''.
 */
function arctik_shipping_slot( string $rate_or_method_id ): string {
	return ARCTIK_SHIPPING_SLOTS[ strtok( $rate_or_method_id, ':' ) ] ?? '';
}

/**
 * Доставка бесплатна для суммы товаров (после скидки): порог из настроек, 0 / пусто — бесплатной доставки нет.
 */
function arctik_is_free_shipping( float $sum ): bool {
	$from = (float) arctik_option( 'arctik_free_shipping_from' );

	return $from > 0 && $sum >= $from;
}

/**
 * Тариф СДЭК для блока способа (pickup / courier) до города и упаковки пакета WC: результат arctik_cdek_tariff() или null.
 */
function arctik_shipping_quote( string $slot, int $city_code, array $contents ): ?array {
	$tariff = (int) arctik_option( 'pickup' === $slot ? 'arctik_cdek_tariff_pickup' : 'arctik_cdek_tariff_courier' );

	return arctik_cdek_tariff( $tariff ?: ( 'pickup' === $slot ? 136 : 137 ), $city_code, arctik_cdek_package( $contents ) );
}

if ( class_exists( 'WC_Shipping_Method' ) ) {
	abstract class Arctik_Shipping_Method extends WC_Shipping_Method {
		public function __construct( $instance_id = 0 ) {
			$this->instance_id          = absint( $instance_id );
			$this->supports             = [ 'shipping-zones', 'instance-settings', 'instance-settings-modal' ];
			$this->instance_form_fields = [
				'title' => [
					'title'   => 'Название',
					'type'    => 'text',
					'default' => $this->method_title,
				],
				'markup' => [
					'title'       => 'Наценка, ₽',
					'type'        => 'price',
					'default'     => '0',
					'description' => 'Прибавляется к тарифу СДЭК (можно отрицательную — скидка на доставку)',
				],
			];
			$this->title = $this->get_option( 'title' );

			add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
		}

		public function calculate_shipping( $package = [] ) {
			$slot  = arctik_shipping_slot( $this->id );
			$quote = arctik_shipping_quote( $slot, (int) ( $package['arctik_city_code'] ?? arctik_city()['code'] ), $package['contents'] ?? [] );
			if ( ! $quote ) {
				return;
			}

			$this->add_rate( [
				'id'        => $this->get_rate_id(),
				'label'     => $this->title,
				'cost'      => arctik_is_free_shipping( (float) ( $package['contents_cost'] ?? 0 ) ) ? 0 : max( 0, $quote['sum'] + (float) $this->get_option( 'markup' ) ),
				'meta_data' => [
					'term'     => arctik_days_text( $quote['min'], $quote['max'] ),
					'dates'    => arctik_dates_text( $quote['date_min'], $quote['date_max'] ),
					'date_min' => $quote['date_min'],
				],
			] );
		}
	}

	final class Arctik_Shipping_Pickup extends Arctik_Shipping_Method {
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'arctik_pickup';
			$this->method_title       = 'Пункт выдачи';
			$this->method_description = 'Пункт выдачи СДЭК (позже — Озон): пункт выбирается на карте, стоимость и срок — тариф СДЭК.';
			parent::__construct( $instance_id );
		}
	}

	final class Arctik_Shipping_Courier extends Arctik_Shipping_Method {
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'arctik_courier';
			$this->method_title       = 'Курьер';
			$this->method_description = 'Курьер СДЭК по адресу, с выбором даты; стоимость и срок — тариф СДЭК.';
			parent::__construct( $instance_id );
		}
	}

	add_filter( 'woocommerce_shipping_methods', function ( array $methods ): array {
		$methods['arctik_pickup']  = Arctik_Shipping_Pickup::class;
		$methods['arctik_courier'] = Arctik_Shipping_Courier::class;

		return $methods;
	} );
}

/**
 * Ставки доставки корзины для формы: [ [ 'rate' => WC_Shipping_Rate, 'slot' => pickup|courier, 'term' => '3–4 дня', 'dates' => '31 окт – 1 нояб' ], … ].
 */
function arctik_shipping_rates(): array {
	$package = WC()->shipping()->get_packages()[0] ?? [];
	$rates   = [];

	foreach ( $package['rates'] ?? [] as $rate ) {
		$slot = arctik_shipping_slot( $rate->get_id() );
		if ( $slot ) {
			$rates[] = [
				'rate'  => $rate,
				'slot'  => $slot,
				'term'  => (string) ( $rate->get_meta_data()['term'] ?? '' ),
				'dates' => (string) ( $rate->get_meta_data()['dates'] ?? '' ),
			];
		}
	}

	return $rates;
}

/**
 * Даты доставки курьером: 8 дней с завтрашнего — [ 'Y-m-d' => 'Ср, 30 сент' ] (в статике — демо-даты).
 */
function arctik_delivery_dates(): array {
	$days   = [ 'Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб' ];
	$months = [ 'янв', 'февр', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сент', 'окт', 'нояб', 'дек' ];
	$dates  = [];

	for ( $i = 1; $i <= 8; $i++ ) {
		$date = ( new DateTimeImmutable( 'today', wp_timezone() ) )->modify( "+{$i} day" );

		$dates[ $date->format( 'Y-m-d' ) ] = sprintf( '%s, %d %s', $days[ (int) $date->format( 'w' ) ], $date->format( 'j' ), $months[ (int) $date->format( 'n' ) - 1 ] );
	}

	return $dates;
}

/**
 * «30 сентября - 1 октября» — первые два дня доставки (заголовок над датами, как в статике).
 */
function arctik_delivery_range(): string {
	$months = [ 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря' ];
	$format = fn( string $ymd ): string => (int) substr( $ymd, 8, 2 ) . "\u{00A0}" . $months[ (int) substr( $ymd, 5, 2 ) - 1 ];
	$dates  = array_keys( arctik_delivery_dates() );

	return $format( $dates[0] ) . ' - ' . $format( $dates[1] );
}
