<?php
/**
 * «Попробуйте также» в корзине-попапе (src/partials/cart.html, .mini-cart__more) — фрагмент WC [data-js-cart-more] (inc/cart.php).
 * Вкусы, которых ещё нет в корзине; в корзине все — блок скрыт (решение пользователя).
 */

defined( 'ABSPATH' ) || exit;

$arctik_in_cart  = array_map( fn( $item ) => (int) $item['product_id'], WC()->cart->get_cart() );
$arctik_products = array_filter( arctik_products(), fn( $product ) => ! in_array( $product->get_id(), $arctik_in_cart, true ) );
?>
<div class="mini-cart__more"<?php echo $arctik_products ? '' : ' hidden'; ?> data-js-cart-more>
	<?php if ( $arctik_products ) : ?>
		<p class="mini-cart__more-title">Попробуйте также</p>
		<div class="mini-cart__more-viewport" data-js-drag-slider data-drag-slider-align="start">
			<div class="mini-cart__more-track" data-js-drag-slider-track>
				<?php
				foreach ( $arctik_products as $arctik_product ) {
					get_template_part( 'template-parts/mini-card', null, [
						'product' => $arctik_product,
						'mod'     => 'mini-card--light',
					] );
				}
				?>
			</div>
		</div>
	<?php endif; ?>
</div>
