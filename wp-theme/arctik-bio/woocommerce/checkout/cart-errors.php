<?php
/**
 * Ошибки корзины на странице оформления (у WC — заглушка «вернитесь в корзину»): выводится обычная форма,
 * текст ошибок — под кнопкой оплаты (разобранные товары — inc/checkout.php). Оформить заказ сервер всё равно не даст.
 *
 * @var WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;

$arctik_errors = array_map( fn( $notice ) => html_entity_decode( wp_strip_all_tags( $notice['notice'] ), ENT_QUOTES, 'UTF-8' ), wc_get_notices( 'error' ) );

wc_get_template( 'checkout/form-checkout.php', [
	'checkout' => $checkout,
	'error'    => implode( ' ', $arctik_errors ),
] );
