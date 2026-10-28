<?php
/**
 * Итоги и кнопка оплаты оформления (часть src/partials/checkout/main.html). Фрагмент [data-js-checkout-summary] — обновляется
 * после промокода (inc/checkout.php). $args: error — ошибка заказа под кнопкой (разобранные товары), по умолчанию скрыта.
 * «Скидка» — только при промокоде; «Доставка» — стоимость выбранного способа (0 — «Бесплатно»).
 */

defined( 'ABSPATH' ) || exit;

$arctik_cart     = WC()->cart;
$arctik_discount = (float) $arctik_cart->get_discount_total() + (float) $arctik_cart->get_discount_tax();
$arctik_shipping = (float) $arctik_cart->get_shipping_total() + (float) $arctik_cart->get_shipping_tax();
$arctik_error    = (string) ( $args['error'] ?? '' );
?>
<div class="checkout__summary" data-js-checkout-summary>
	<div class="checkout__totals">
		<dl class="checkout__rows">
			<div class="checkout__row">
				<dt><?php echo esc_html( arctik_checkout_count_text( $arctik_cart->get_cart_contents_count() ) ); ?></dt>
				<dd><?php echo wp_kses_post( wc_price( (float) $arctik_cart->get_subtotal() + (float) $arctik_cart->get_subtotal_tax() ) ); ?></dd>
			</div>
			<?php if ( $arctik_discount > 0 ) : ?>
				<div class="checkout__row">
					<dt>Скидка</dt>
					<dd class="checkout__discount">- <?php echo wp_kses_post( wc_price( $arctik_discount ) ); ?></dd>
				</div>
			<?php endif; ?>
			<div class="checkout__row">
				<dt>Доставка</dt>
				<?php if ( $arctik_shipping > 0 ) : ?>
					<dd><?php echo wp_kses_post( wc_price( $arctik_shipping ) ); ?></dd>
				<?php else : ?>
					<dd class="checkout__free">Бесплатно</dd>
				<?php endif; ?>
			</div>
		</dl>
		<p class="checkout__total order-total"><span>Итого</span> <span><?php echo esc_html( arctik_checkout_total() ); ?></span></p>
	</div>
	<button class="btn btn--accent checkout__pay" type="submit" name="woocommerce_checkout_place_order" id="place_order" data-js-swipe>Оплатить <?php echo esc_html( arctik_checkout_total() ); ?></button>
	<?php /* Ошибка заказа от сервера (товар закончился и т.п.) */ ?>
	<p class="checkout__error checkout__error--pay" <?php echo $arctik_error ? '' : 'hidden'; ?> data-js-checkout-error="general"><?php echo wp_kses_post( $arctik_error ); ?></p>
</div>
