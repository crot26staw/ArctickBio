<?php
/**
 * «Заказ оформлен» (src/order-received.html) и статус заказа (src/order.html).
 *
 * «Заказ оформлен» — эндпоинт order-received оформления: шаблон page-templates/order-received.php + woocommerce/checkout/thankyou.php.
 * Статус — эндпоинт view-order кабинета: page-templates/order.php + template-parts/order/status.php (только своему заказу,
 * иначе — стандартный вид WC).
 * Шкала статусов — по статусу заказа WC: на удержании / в обработке — «В сборке», свои статусы «Передаётся в доставку»,
 * «В пути», «Готов к получению» (переключает менеджер), «Выполнен» — «Получен» (решение пользователя).
 * Плановая дата доставки и ссылка отслеживания — поля заказа в админке (у курьера дату выбирает покупатель).
 */

defined( 'ABSPATH' ) || exit;

const ARCTIK_ORDER_STATUSES = [
	'arctik-handover' => 'Передаётся в доставку',
	'arctik-transit'  => 'В пути',
	'arctik-ready'    => 'Готов к получению',
];

// Службы пунктов выдачи (как в попапе ПВЗ, template-parts/checkout/pickup-modal.php)
const ARCTIK_PICKUP_SERVICES = [
	'ozon' => [ 'name' => 'Озон', 'logo' => 'icons/logo-ozon.svg' ],
	'cdek' => [ 'name' => 'СДЭК', 'logo' => 'images/logo-cdek.webp' ],
];

// Службы в попапе пункта выдачи (Озон — когда будет его API)
const ARCTIK_PICKUP_ACTIVE = [ 'cdek' ];

// Срок хранения в пункте выдачи, дней (в статике — «Срок хранения заказа — 14 дней», checkout.js)
const ARCTIK_PICKUP_STORAGE_DAYS = 14;

// «Оплачено Картой»: способ оплаты в итогах заказа
const ARCTIK_PAID_LABELS = [
	'arctik_sbp'  => 'СБП',
	'arctik_card' => 'Картой',
];

// ---------- Статусы ----------

add_filter( 'woocommerce_register_shop_order_post_statuses', function ( array $statuses ): array {
	foreach ( ARCTIK_ORDER_STATUSES as $slug => $label ) {
		$statuses[ 'wc-' . $slug ] = [
			'label'                     => $label,
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: число заказов */
			'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>' ), // phpcs:ignore WordPress.WP.I18n
		];
	}

	return $statuses;
} );

// В списке статусов — после «Обработка»
add_filter( 'wc_order_statuses', function ( array $statuses ): array {
	$result = [];

	foreach ( $statuses as $key => $label ) {
		$result[ $key ] = $label;
		if ( 'wc-processing' === $key ) {
			foreach ( ARCTIK_ORDER_STATUSES as $slug => $arctik_label ) {
				$result[ 'wc-' . $slug ] = $arctik_label;
			}
		}
	}

	return $result;
} );

// Заказ в доставке — оплачен (отчёты, «Оплачено» на странице статуса)
add_filter( 'woocommerce_order_is_paid_statuses', fn( array $statuses ) => array_merge( $statuses, array_keys( ARCTIK_ORDER_STATUSES ) ) );

// Дата прибытия в пункт выдачи — от неё считается срок хранения
add_action( 'woocommerce_order_status_arctik-ready', function ( int $order_id, WC_Order $order ) {
	$order->update_meta_data( '_arctik_ready_date', time() );
	$order->save();
}, 10, 2 );

/**
 * Текущий шаг шкалы статусов (1–5), 0 — заказ не в работе (не оплачен, отменён, возвращён).
 */
function arctik_order_step( WC_Order $order ): int {
	return match ( $order->get_status() ) {
		'pending', 'failed', 'cancelled', 'refunded', 'checkout-draft' => 0,
		'arctik-handover' => 2,
		'arctik-transit'  => 3,
		'arctik-ready'    => 4,
		'completed'       => 5,
		default           => 1,
	};
}

/**
 * Сообщение вместо дат: [ текст, модификатор order-status__message ] или null — показать даты.
 */
function arctik_order_message( WC_Order $order, string $slot ): ?array {
	$status = $order->get_status();

	if ( in_array( $status, [ 'pending', 'failed', 'checkout-draft' ], true ) ) {
		return [ "Заказ не\u{00A0}оплачен", 'error' ];
	}
	if ( 'cancelled' === $status ) {
		return [ 'Заказ отменён', 'muted' ];
	}
	if ( 'refunded' === $status ) {
		return [ "Деньги за\u{00A0}заказ возвращены", 'muted' ];
	}
	if ( 'completed' === $status ) {
		$date = $order->get_date_completed();

		return [ $date ? 'Заказ получен ' . arctik_long_date( $date->getTimestamp() ) : 'Заказ получен', '' ];
	}
	if ( 'arctik-ready' === $status && 'pickup' === $slot ) {
		$ready = (int) $order->get_meta( '_arctik_ready_date' ) ?: time();

		return [ sprintf( "Заказ прибыл в\u{00A0}пункт выдачи и\u{00A0}доступен для\u{00A0}получения до\u{00A0}%s", arctik_long_date( $ready + ARCTIK_PICKUP_STORAGE_DAYS * DAY_IN_SECONDS ) ), '' ];
	}

	return null;
}

/**
 * Дата с месяцем: «10 ноября».
 */
function arctik_long_date( int $timestamp ): string {
	return str_replace( ' ', "\u{00A0}", wp_date( 'j F', $timestamp ) );
}

/**
 * Дата коротко: «2 ноя».
 */
function arctik_short_date( int $timestamp ): string {
	$months = [ 'янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек' ];

	return wp_date( 'j', $timestamp ) . "\u{00A0}" . $months[ (int) wp_date( 'n', $timestamp ) - 1 ];
}

/**
 * Плановая дата доставки (timestamp) или 0 — поле заказа «Плановая дата доставки» (у курьера — выбранная при оформлении).
 */
function arctik_order_delivery_date( WC_Order $order ): int {
	$date = (string) $order->get_meta( '_arctik_delivery_date' );
	if ( ! $date ) {
		return 0;
	}

	$parsed = DateTimeImmutable::createFromFormat( '!Y-m-d', $date, wp_timezone() );

	return $parsed ? $parsed->getTimestamp() : 0;
}

/**
 * Доставка заказа для страницы статуса: slot (pickup / courier / ''), title, address, logo (путь в assets темы), note.
 */
function arctik_order_delivery( WC_Order $order ): array {
	$method = current( $order->get_shipping_methods() );
	$slot   = $method ? arctik_shipping_slot( $method->get_method_id() ) : '';

	if ( 'pickup' === $slot ) {
		$service = ARCTIK_PICKUP_SERVICES[ $order->get_meta( '_arctik_pickup_service' ) ] ?? null;
		$address = (string) ( $order->get_meta( '_arctik_pickup_address' ) ?: $order->get_shipping_address_1() );

		// В адресе пункта служба идёт первой: «Озон, Москва, …»
		if ( $service && str_starts_with( $address, $service['name'] . ', ' ) ) {
			$address = substr( $address, strlen( $service['name'] ) + 2 );
		}

		return [
			'slot'    => $slot,
			'title'   => $service ? 'Пункт ' . $service['name'] : $method->get_name(),
			'address' => $address,
			'logo'    => $service['logo'] ?? 'icons/icn-location.svg',
			'note'    => "Срок хранения заказа\u{00A0}— " . ARCTIK_PICKUP_STORAGE_DAYS . "\u{00A0}дней",
		];
	}

	return [
		'slot'    => $slot,
		'title'   => $method ? $method->get_name() : '',
		'address' => $order->get_shipping_address_1(),
		'logo'    => 'icons/icn-location.svg',
		'note'    => '',
	];
}

/**
 * «Картой» / «СБП» — способ оплаты в итогах.
 */
function arctik_order_paid_label( WC_Order $order ): string {
	return ARCTIK_PAID_LABELS[ $order->get_payment_method() ] ?? $order->get_payment_method_title();
}

// ---------- Админка: поля заказа ----------

add_action( 'woocommerce_admin_order_data_after_shipping_address', function ( WC_Order $order ) {
	$point  = (string) $order->get_meta( '_arctik_pickup_point' );
	$pickup = trim( $order->get_meta( '_arctik_pickup_address' ) . ( $point ? " ({$point})" : '' ) );

	if ( $pickup ) {
		printf( '<p><strong>Пункт выдачи:</strong> %s</p>', esc_html( $pickup ) );
	}
	?>
	<p class="form-field form-field-wide">
		<label for="arctik_delivery_date">Плановая дата доставки</label>
		<input type="date" id="arctik_delivery_date" name="arctik_delivery_date" value="<?php echo esc_attr( $order->get_meta( '_arctik_delivery_date' ) ); ?>">
	</p>
	<p class="form-field form-field-wide">
		<label for="arctik_tracking_url">Ссылка для отслеживания</label>
		<input type="url" id="arctik_tracking_url" name="arctik_tracking_url" value="<?php echo esc_attr( $order->get_meta( '_arctik_tracking_url' ) ); ?>" placeholder="https://" style="width: 100%;">
		<span class="description">Кнопка «Отследить заказ» активна, когда заказ передан в&nbsp;доставку и&nbsp;ссылка заполнена.</span>
	</p>
	<?php
} );

// Nonce проверил WC (woocommerce_meta_boxes_nonce) перед этим хуком
add_action( 'woocommerce_process_shop_order_meta', function ( int $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$date = sanitize_text_field( wp_unslash( $_POST['arctik_delivery_date'] ?? '' ) );
	$order->update_meta_data( '_arctik_delivery_date', preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '' );
	$order->update_meta_data( '_arctik_tracking_url', esc_url_raw( wp_unslash( $_POST['arctik_tracking_url'] ?? '' ) ) );
	// phpcs:enable

	$order->save();
}, 50 );

// ---------- Шаблоны ----------

/**
 * Заказ на странице статуса (эндпоинт view-order) — если он принадлежит текущему пользователю, иначе null.
 */
function arctik_viewed_order(): ?WC_Order {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || ! is_wc_endpoint_url( 'view-order' ) ) {
		return null;
	}

	$order_id = absint( get_query_var( 'view-order' ) );
	$order    = wc_get_order( $order_id );

	return $order instanceof WC_Order && current_user_can( 'view_order', $order_id ) ? $order : null;
}

add_filter( 'template_include', function ( string $template ): string {
	if ( function_exists( 'is_wc_endpoint_url' ) && is_checkout() && is_wc_endpoint_url( 'order-received' ) ) {
		return get_theme_file_path( 'page-templates/order-received.php' );
	}

	return arctik_viewed_order() ? get_theme_file_path( 'page-templates/order.php' ) : $template;
} );

add_filter( 'body_class', function ( array $classes ): array {
	if ( arctik_viewed_order() ) {
		array_push( $classes, 'page--checkout', 'page--account' );
	}

	return $classes;
} );

// Таблица заказа WC под «Заказ оформлен» не нужна — состав заказа виден на странице статуса
add_action( 'wp', function () {
	remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
} );
