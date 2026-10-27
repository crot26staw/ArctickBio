<?php
/**
 * Способы доставки оформления заказа: «Пункт выдачи» (arctik_pickup) и «Курьер» (arctik_courier).
 * Добавляются в зону доставки (WooCommerce → Настройки → Доставка), у каждого — название, стоимость и срок («Завтра»).
 * Блок способа в форме (pickup / courier) — по ID способа, arctik_shipping_slot().
 * Пункт выдачи, адрес курьера и дата пока выбираются в демо-попапах (этап Оф2 — службы доставки и карта).
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
				'cost'  => [
					'title'       => 'Стоимость, ₽',
					'type'        => 'price',
					'default'     => '0',
					'description' => '0 — «бесплатно»',
				],
				'term'  => [
					'title'       => 'Срок',
					'type'        => 'text',
					'default'     => 'Завтра',
					'description' => 'Подпись под названием: «Завтра, бесплатно»',
				],
			];
			$this->title = $this->get_option( 'title' );

			add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
		}

		public function calculate_shipping( $package = [] ) {
			$this->add_rate( [
				'id'        => $this->get_rate_id(),
				'label'     => $this->title,
				'cost'      => (float) $this->get_option( 'cost' ),
				'meta_data' => [ 'term' => $this->get_option( 'term' ) ],
			] );
		}
	}

	final class Arctik_Shipping_Pickup extends Arctik_Shipping_Method {
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'arctik_pickup';
			$this->method_title       = 'Пункт выдачи';
			$this->method_description = 'Самовывоз из пункта выдачи (Озон, СДЭК) — пункт выбирается на карте.';
			parent::__construct( $instance_id );
		}
	}

	final class Arctik_Shipping_Courier extends Arctik_Shipping_Method {
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'arctik_courier';
			$this->method_title       = 'Курьер';
			$this->method_description = 'Доставка курьером по адресу, с выбором даты.';
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
 * Ставки доставки корзины для формы: [ [ 'rate' => WC_Shipping_Rate, 'slot' => pickup|courier, 'term' => 'Завтра' ], … ].
 */
function arctik_shipping_rates(): array {
	$package = WC()->shipping()->get_packages()[0] ?? [];
	$rates   = [];

	foreach ( $package['rates'] ?? [] as $rate ) {
		$slot = arctik_shipping_slot( $rate->get_id() );
		if ( $slot ) {
			$rates[] = [
				'rate' => $rate,
				'slot' => $slot,
				'term' => (string) ( $rate->get_meta_data()['term'] ?? '' ),
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
