<?php
/**
 * Товар в блоке «Ваш заказ» (src/partials/checkout/order-item.html). $args: item — позиция WC()->cart.
 * Название — название товара, картинка — пачка мини-карточки (как в корзине-попапе), цена — сумма строки.
 */

defined( 'ABSPATH' ) || exit;

$arctik_item    = $args['item'] ?? [];
$arctik_product = $arctik_item['data'] ?? null;
if ( ! $arctik_product instanceof WC_Product ) {
	return;
}

$arctik_parent = wc_get_product( $arctik_product->get_parent_id() ?: $arctik_product->get_id() ) ?: $arctik_product;
$arctik_qty    = (int) $arctik_item['quantity'];
?>
<?php /* Товар в блоке «Ваш заказ» (макет 18247:73233) */ ?>
<li class="order-item">
	<span class="order-item__thumb">
		<?php echo arctik_image( arctik_product_image_id( $arctik_parent, 'mini' ), 'woocommerce_thumbnail', [ 'class' => 'order-item__img', 'alt' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="order-item__badge" aria-hidden="true"><?php echo esc_html( $arctik_qty ); ?></span>
	</span>
	<span class="order-item__text">
		<span class="order-item__title"><?php echo arctik_text( $arctik_parent->get_name() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<span class="order-item__qty"><?php echo esc_html( $arctik_qty ); ?> шт</span>
	</span>
	<span class="order-item__price"><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $arctik_product, $arctik_qty ) ); ?></span>
</li>
