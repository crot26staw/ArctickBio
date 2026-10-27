<?php
/**
 * Товары корзины-попапа (фрагмент [data-js-cart-list], inc/cart.php).
 */

defined( 'ABSPATH' ) || exit;
?>
<ul class="mini-cart__list woocommerce-mini-cart cart_list product_list_widget" data-js-cart-list>
	<?php
	foreach ( WC()->cart->get_cart() as $arctik_key => $arctik_item ) {
		get_template_part( 'template-parts/cart-item', null, [
			'key'  => $arctik_key,
			'item' => $arctik_item,
		] );
	}
	?>
</ul>
