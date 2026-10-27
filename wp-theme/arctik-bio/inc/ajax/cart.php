<?php
/**
 * Корзина: свои wc-ajax обработчики (?wc-ajax=arctik_…), отвечают фрагментами корзины (inc/cart.php).
 * Добавление и удаление — стандартные add_to_cart / remove_from_cart WC.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Проверка nonce; неверный — ошибка с текстом для промокода.
 */
function arctik_cart_check_nonce(): void {
	if ( ! check_ajax_referer( 'arctik-cart', 'security', false ) ) {
		wp_send_json_error( [ 'errors' => [ 'coupon_code' => 'Сессия устарела, обновите страницу' ] ], 403 );
	}
}

// Количество товара: cart_item_key, quantity.
add_action( 'wc_ajax_arctik_cart_qty', function () {
	arctik_cart_check_nonce();

	$key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ?? '' ) );
	$qty = absint( $_POST['quantity'] ?? 0 );

	if ( $key && $qty && WC()->cart->get_cart_item( $key ) ) {
		WC()->cart->set_quantity( $key, $qty );
	}

	WC_AJAX::get_refreshed_fragments();
} );

// Промокод: coupon_code. В корзине один промокод — новый заменяет прежний.
add_action( 'wc_ajax_arctik_coupon_apply', function () {
	arctik_cart_check_nonce();

	$code = wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['coupon_code'] ?? '' ) ) );

	if ( ! $code || ! wc_get_coupon_id_by_code( $code ) ) {
		wp_send_json_error( [ 'errors' => [ 'coupon_code' => "Промокод не\u{00A0}найден" ] ] );
	}

	if ( ! WC()->cart->has_discount( $code ) ) {
		$previous = WC()->cart->get_applied_coupons();
		WC()->cart->remove_coupons();

		if ( ! WC()->cart->apply_coupon( $code ) ) {
			// Текст ошибки WC: срок истёк, не та сумма и т.п.
			$notices = wc_get_notices( 'error' );
			wc_clear_notices();
			array_map( [ WC()->cart, 'apply_coupon' ], $previous );
			wc_clear_notices();

			wp_send_json_error( [ 'errors' => [ 'coupon_code' => wp_strip_all_tags( $notices[0]['notice'] ?? "Промокод не\u{00A0}найден" ) ] ] );
		}
	}

	wc_clear_notices();
	WC_AJAX::get_refreshed_fragments();
} );

// Снять промокод: coupon_code.
add_action( 'wc_ajax_arctik_coupon_remove', function () {
	arctik_cart_check_nonce();

	$code = wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['coupon_code'] ?? '' ) ) );
	if ( $code ) {
		WC()->cart->remove_coupon( $code );
	}

	wc_clear_notices();
	WC_AJAX::get_refreshed_fragments();
} );
