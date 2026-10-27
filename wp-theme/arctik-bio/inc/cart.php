<?php
/**
 * Корзина-попап (template-parts/cart.php): части разметки, фрагменты WC, данные для js/modules/cart-api.js.
 *
 * Фрагменты (woocommerce_add_to_cart_fragments) — список товаров, итоги, число товаров в заголовке и бейдже шапки;
 * их отдают и стандартные wc-ajax WC (add_to_cart, remove_from_cart, get_refreshed_fragments), и наши (inc/ajax/cart.php).
 * jQuery-скрипты WC корзины не подключаются — запросы шлёт cart-api.js.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Товар разобран: нет в наличии или остатка меньше, чем в корзине.
 */
function arctik_cart_item_is_sold( array $item ): bool {
	$product = $item['data'] ?? null;

	return ! $product instanceof WC_Product
		|| ! $product->is_in_stock()
		|| ! $product->has_enough_stock( (int) $item['quantity'] );
}

/**
 * Число товаров в корзине без разобранных (заголовок попапа и бейдж в шапке — как в статике).
 */
function arctik_cart_count(): int {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}

	$count = 0;
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( ! arctik_cart_item_is_sold( $item ) ) {
			$count += (int) $item['quantity'];
		}
	}

	return $count;
}

/**
 * Промокод, применённый к корзине (в попапе — один, см. inc/ajax/cart.php).
 */
function arctik_cart_coupon(): string {
	return function_exists( 'WC' ) && WC()->cart ? (string) ( WC()->cart->get_applied_coupons()[0] ?? '' ) : '';
}

/**
 * Разметка части корзины: list — товары, totals — итоги, title-count — « (2)» в заголовке, count — бейдж шапки.
 */
function arctik_cart_part( string $part ): string {
	if ( 'title-count' === $part ) {
		$count = WC()->cart->is_empty() ? '' : ' (' . arctik_cart_count() . ')';

		return '<span class="mini-cart__count" data-js-cart-title-count>' . esc_html( $count ) . '</span>';
	}

	if ( 'count' === $part ) {
		return '<span class="header__cart-count" data-js-cart-count data-swipe-static>' . esc_html( arctik_cart_count() ) . '</span>';
	}

	ob_start();
	get_template_part( "template-parts/cart/{$part}" );

	return (string) ob_get_clean();
}

add_filter( 'woocommerce_add_to_cart_fragments', function ( array $fragments ): array {
	// Стандартная мини-корзина WC заменила бы наш попап целиком.
	unset( $fragments['div.widget_shopping_cart_content'] );

	WC()->cart->calculate_totals();

	foreach ( [ 'list', 'totals', 'title-count', 'count' ] as $part ) {
		$selector               = 'count' === $part ? '[data-js-cart-count]' : "[data-js-cart-{$part}]";
		$fragments[ $selector ] = arctik_cart_part( $part );
	}

	return $fragments;
} );

// Добавление в корзину, пересчёт и удаление — cart-api.js (jQuery-скрипты WC не нужны).
add_action( 'wp_enqueue_scripts', function () {
	foreach ( [ 'wc-add-to-cart', 'wc-cart-fragments' ] as $handle ) {
		wp_dequeue_script( $handle );
	}
}, 100 );

add_filter( 'arctik_js_data', function ( array $data ): array {
	$data['cart'] = [
		'endpoint' => WC_AJAX::get_endpoint( '%%endpoint%%' ),
		'nonce'    => wp_create_nonce( 'arctik-cart' ),
	];

	return $data;
} );
