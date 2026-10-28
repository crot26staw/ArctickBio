<?php
/**
 * Ошибки корзины на странице оформления (у WC — заглушка «вернитесь в корзину»): выводится обычная форма,
 * текст ошибок — под кнопкой оплаты (разобранные товары — inc/checkout.php). Оформить заказ сервер всё равно не даст.
 *
 * @var WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;

// Текст с разметкой (ссылка «удалите товар из корзины»), выводится через wp_kses_post
$arctik_errors = array_map( fn( $notice ) => (string) $notice['notice'], wc_get_notices( 'error' ) );

wc_get_template( 'checkout/form-checkout.php', [
	'checkout' => $checkout,
	'error'    => implode( ' ', $arctik_errors ),
] );
