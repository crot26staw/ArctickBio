<?php
/**
 * Оплата — интернет-эквайринг Альфа-Банка: плагин банка «WooCommerce Alfabank Payment» (способ alfabank_payment, этап Оф2в).
 * Плагин регистрирует заказ в банке, переводит покупателя на платёжную страницу (карта или СБП — там),
 * принимает callback и ставит статус оплаченного заказа (в его настройках — «Обработка»).
 * Настройки и доступы — WooCommerce → Настройки → Платежи → Alfa Bank (в базе, не в репозитории).
 *
 * Здесь — стыковка с вёрсткой:
 * - оплата не прошла — плагин отправляет на страницу оплаты WC (order-pay), её вёрстки нет: переход на «Заказ оформлен»,
 *   там «Оплата не прошла» + «Оплатить снова» (woocommerce/checkout/thankyou.php);
 * - «Оплатить снова» — arctik_payment_retry_url(): новая платёжная страница банка сразу, без order-pay;
 * - иконка способа в форме — arctik_payment_icon();
 * - ошибка банка при переходе к оплате — свой текст вместо «There was an error… ERRORCODE#…» плагина (код — в логе плагина).
 */

defined( 'ABSPATH' ) || exit;

const ARCTIK_GATEWAY = 'alfabank_payment';

/**
 * Иконка способа оплаты в форме (как в статике): [ файл, ширина, высота, модификатор ].
 */
function arctik_payment_icon( string $gateway_id ): array {
	return [ 'icons/icn-credit.svg', '32', '32', '' ];
}

/**
 * Шлюз Альфа-Банка или null (плагин выключен).
 */
function arctik_payment_gateway(): ?WC_Payment_Gateway {
	$gateways = WC()->payment_gateways()->payment_gateways();

	return $gateways[ ARCTIK_GATEWAY ] ?? null;
}

/**
 * «Оплатить снова»: ссылка на повторную оплату заказа (ключ заказа — как у страницы оплаты WC).
 */
function arctik_payment_retry_url( WC_Order $order ): string {
	return add_query_arg(
		[
			'arctik-pay' => $order->get_id(),
			'key'        => $order->get_order_key(),
		],
		home_url( '/' )
	);
}

/*
 * Возврат покупателя из банка (?wc-api=alfabank_payment&action=result): плагин при включённых callback статус здесь не меняет —
 * ждёт callback банка; не пришёл — оплаченный заказ оставался «Ожидает оплаты» / «Не удался» (найдено на тесте 30.10.2026).
 * Подтверждаем сами, до обработчика плагина: статус — запросом в банк, номер заказа в банке (<номер>_<время>) и сумма должны совпасть
 * с заказом из адреса (иначе чужой оплаченный orderId засчитал бы другой заказ). Дальше плагин делает свой редирект; callback
 * потом видит оплаченный заказ и ничего не меняет.
 */
add_action( 'woocommerce_api_' . ARCTIK_GATEWAY, function () {
	if ( 'result' !== ( $_GET['action'] ?? '' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	$gateway = arctik_payment_gateway();
	$order   = wc_get_order( absint( $_GET['order_id'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$md      = sanitize_text_field( wp_unslash( $_GET['orderId'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification

	if ( ! $gateway || ! $order || ! $md || $order->is_paid() || ARCTIK_GATEWAY !== $order->get_payment_method() ) {
		return;
	}

	$url      = ( 'yes' === $gateway->test_mode ? $gateway->test_url : $gateway->prod_url ) . 'getOrderStatusExtended.do';
	$response = json_decode( (string) $gateway->_sendGatewayData(
		http_build_query( [ 'userName' => $gateway->merchant, 'password' => $gateway->password, 'orderId' => $md ], '', '&' ),
		$url
	), true );

	if ( ! is_array( $response ) ) {
		return;
	}

	$response = array_merge( $response, (array) ( $response['orderStatusResultModel'] ?? [] ) );
	$number   = preg_replace( '/_\d+$/', '', (string) ( $response['orderNumber'] ?? '' ) );
	$paid     = in_array( (int) ( $response['orderStatus'] ?? -1 ), [ 1, 2 ], true );

	if ( ! $paid || $number !== (string) $order->get_order_number() || (int) ( $response['amount'] ?? 0 ) !== (int) round( (float) $order->get_total() * 100 ) ) {
		return;
	}

	$order->update_meta_data( ARCTIK_GATEWAY . '_orderId', $md );
	$order->add_order_note( 'Альфа-Банк: оплата подтверждена при возврате покупателя на сайт' );
	$order->payment_complete( sanitize_text_field( (string) ( $response['authRefNum'] ?? '' ) ) ); // «Обработка», остаток списывается
}, 5 );

// Перевод плагина не срабатывает (в .po ключ с <br/>), к тексту добавлен код банка — покупателю свой текст
add_filter( 'woocommerce_add_error', function ( $message ) {
	if ( is_string( $message ) && ( str_contains( $message, 'ERRORCODE#' ) || str_contains( $message, 'There was an error while processing payment' ) ) ) {
		return "Не удалось перейти к\u{00A0}оплате\u{00A0}— попробуйте ещё раз через несколько минут";
	}

	return $message;
} );

add_action( 'template_redirect', function () {
	// «Оплатить снова» — новая платёжная страница банка (generate_form плагина регистрирует заказ заново и делает редирект)
	if ( isset( $_GET['arctik-pay'], $_GET['key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$order   = wc_get_order( absint( $_GET['arctik-pay'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$key     = sanitize_text_field( wp_unslash( $_GET['key'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$gateway = arctik_payment_gateway();

		if ( ! $order || ! hash_equals( $order->get_order_key(), $key ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		if ( ! $order->needs_payment() || ! $gateway || ARCTIK_GATEWAY !== $order->get_payment_method() ) {
			wp_safe_redirect( $order->get_checkout_order_received_url() );
			exit;
		}

		$gateway->generate_form( $order->get_id() ); // ошибка банка — уведомление и order-pay, оттуда — ниже
		exit;
	}

	// Страница оплаты WC (сюда плагин отправляет при неудачной оплате) — «Заказ оформлен» с «Оплата не прошла»
	if ( is_wc_endpoint_url( 'order-pay' ) && 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		$order = wc_get_order( absint( get_query_var( 'order-pay' ) ) );
		$key   = sanitize_text_field( wp_unslash( $_GET['key'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification

		if ( $order && hash_equals( $order->get_order_key(), $key ) ) {
			wc_clear_notices(); // текст плагина «There was an error…» иначе всплыл бы потом на оформлении
			wp_safe_redirect( $order->get_checkout_order_received_url() );
			exit;
		}
	}
} );
