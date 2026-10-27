<?php
/**
 * Товар в блоке «Ваш заказ» (src/partials/checkout/order-item.html): оформление (позиция корзины) и статус заказа (позиция заказа).
 * $args: product — WC_Product (вариация или товар; null — товар удалён), name — название, если товара нет, qty — количество,
 * price — сумма строки (HTML wc_price).
 * Название — название товара, картинка — пачка мини-карточки (как в корзине-попапе).
 */

defined( 'ABSPATH' ) || exit;

$arctik_product = $args['product'] ?? null;
$arctik_parent  = $arctik_product instanceof WC_Product ? ( wc_get_product( $arctik_product->get_parent_id() ?: $arctik_product->get_id() ) ?: $arctik_product ) : null;
$arctik_name    = $arctik_parent ? $arctik_parent->get_name() : (string) ( $args['name'] ?? '' );
$arctik_qty     = (int) ( $args['qty'] ?? 1 );
?>
<?php /* Товар в блоке «Ваш заказ» (макет 18247:73233) */ ?>
<li class="order-item">
	<span class="order-item__thumb">
		<?php echo $arctik_parent ? arctik_image( arctik_product_image_id( $arctik_parent, 'mini' ), 'woocommerce_thumbnail', [ 'class' => 'order-item__img', 'alt' => '' ] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="order-item__badge" aria-hidden="true"><?php echo esc_html( $arctik_qty ); ?></span>
	</span>
	<span class="order-item__text">
		<span class="order-item__title"><?php echo arctik_text( $arctik_name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<span class="order-item__qty"><?php echo esc_html( $arctik_qty ); ?> шт</span>
	</span>
	<span class="order-item__price"><?php echo wp_kses_post( $args['price'] ?? '' ); ?></span>
</li>
