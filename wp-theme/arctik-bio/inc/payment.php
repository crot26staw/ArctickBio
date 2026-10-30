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
 * - иконка способа в форме — arctik_payment_icon().
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
