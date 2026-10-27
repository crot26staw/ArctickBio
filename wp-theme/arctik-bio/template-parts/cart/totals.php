<?php
/**
 * Итоги корзины-попапа (фрагмент [data-js-cart-totals], inc/cart.php). «Скидка» — только если применён промокод.
 * «Итого» — без разобранных товаров (как в статике; WC считает их в сумме корзины).
 */

defined( 'ABSPATH' ) || exit;

$arctik_discount = (float) WC()->cart->get_discount_total() + (float) WC()->cart->get_discount_tax();
$arctik_total    = (float) WC()->cart->get_total( 'edit' );

foreach ( WC()->cart->get_cart() as $arctik_item ) {
	if ( arctik_cart_item_is_sold( $arctik_item ) ) {
		$arctik_total -= (float) $arctik_item['line_total'] + (float) $arctik_item['line_tax'];
	}
}
?>
<dl class="mini-cart__totals" data-js-cart-totals>
	<div class="mini-cart__row mini-cart__row--total">
		<dt>Итого</dt>
		<dd class="mini-cart__total" data-js-cart-total><?php echo wp_kses_post( wc_price( max( $arctik_total, 0 ) ) ); ?></dd>
	</div>
	<?php if ( $arctik_discount > 0 ) : ?>
		<div class="mini-cart__row">
			<dt>Скидка</dt>
			<dd class="mini-cart__discount" data-js-cart-discount data-value="<?php echo esc_attr( $arctik_discount ); ?>">- <?php echo wp_kses_post( wc_price( $arctik_discount ) ); ?></dd>
		</div>
	<?php endif; ?>
	<div class="mini-cart__row">
		<dt>Доставка</dt>
		<dd class="mini-cart__free">Бесплатно</dd>
	</div>
</dl>
