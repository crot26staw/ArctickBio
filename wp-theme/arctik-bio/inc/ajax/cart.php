<?php
/**
 * Корзина: свои wc-ajax обработчики (?wc-ajax=arctik_…), отвечают фрагментами корзины (inc/cart.php).
 * Добавление — свой arctik_cart_add (текст ошибки в ответе), удаление — стандартный remove_from_cart WC.
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

// Добавление: product_id (товар или вариация), quantity, keep_notice. Как add_to_cart WC (без nonce), но при ошибке
// отдаёт её текст: { error, message, product_url }. keep_notice — уведомление остаётся в сессии и выводится на странице товара
// («+» у карточек переходит туда), иначе — снимается (форма на странице товара показывает текст сама).
add_action( 'wc_ajax_arctik_cart_add', function () {
	$product      = wc_get_product( absint( $_POST['product_id'] ?? 0 ) );
	$qty          = max( 1, absint( $_POST['quantity'] ?? 1 ) );
	$product_id   = $product ? ( $product->get_parent_id() ?: $product->get_id() ) : 0;
	$variation_id = $product && $product->is_type( 'variation' ) ? $product->get_id() : 0;
	$variation    = $variation_id ? $product->get_variation_attributes() : [];

	$passed = $product && apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $qty, $variation_id, $variation );
	if ( $passed && 'publish' === get_post_status( $product_id ) && WC()->cart->add_to_cart( $product_id, $qty, $variation_id, $variation ) ) {
		do_action( 'woocommerce_ajax_added_to_cart', $product_id );
		WC_AJAX::get_refreshed_fragments();
	}

	wp_send_json( [
		'error'       => true,
		'message'     => arctik_cart_notice( empty( $_POST['keep_notice'] ) ) ?: "Не\u{00A0}удалось добавить товар в\u{00A0}корзину",
		'product_url' => $product_id ? get_permalink( $product_id ) : '',
	] );
} );

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

	$typed = trim( sanitize_text_field( wp_unslash( $_POST['coupon_code'] ?? '' ) ) );
	$code  = wc_format_coupon_code( $typed );

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

	// Код — как ввёл покупатель (arctik_cart_coupon())
	WC()->session->set( 'arctik_coupon_input', $typed );
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
