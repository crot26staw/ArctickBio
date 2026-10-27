<?php
/**
 * Карточка товара в списке (src/partials/product-card.html): каталог, главная. Разметка цикла WooCommerce — li.product.
 *
 * $args: product — WC_Product, mod — модификаторы (product-card--wide).
 */

defined( 'ABSPATH' ) || exit;

/** @var WC_Product|null $arctik_product */
$arctik_product = $args['product'] ?? null;
if ( ! $arctik_product instanceof WC_Product ) {
	return;
}

$arctik_link    = $arctik_product->get_permalink();
$arctik_title   = $arctik_product->get_name();
$arctik_cart_id = arctik_product_cart_id( $arctik_product );
?>
<li class="product product-card <?php echo esc_attr( $args['mod'] ?? '' ); ?>">
	<?php echo arctik_image( arctik_product_image_id( $arctik_product, 'hover' ), 'large', [ 'class' => 'product-card__hover-img', 'alt' => '', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<a class="product-card__media" href="<?php echo esc_url( $arctik_link ); ?>" tabindex="-1" aria-hidden="true">
		<?php echo arctik_image( arctik_product_image_id( $arctik_product ), 'large', [ 'class' => 'product-card__img', 'alt' => '', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</a>
	<div class="product-card__info">
		<div class="product-card__heading">
			<h3 class="product-card__title woocommerce-loop-product__title"><?php echo arctik_text( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
			<span class="product-card__price price"><?php echo wp_kses_post( $arctik_product->get_price_html() ); ?></span>
		</div>
		<div class="product-card__actions">
			<a class="product-card__more btn btn--neutral btn--small" href="<?php echo esc_url( $arctik_link ); ?>" data-js-swipe>Подробнее</a>
			<a class="product-card__cart icon-btn icon-btn--32 button add_to_cart_button ajax_add_to_cart" href="<?php echo esc_url( add_query_arg( 'add-to-cart', $arctik_cart_id, $arctik_link ) ); ?>" data-product_id="<?php echo esc_attr( $arctik_cart_id ); ?>" aria-label="<?php echo esc_attr( 'Добавить в корзину: ' . $arctik_title ); ?>" rel="nofollow" data-js-swipe>
				<img src="<?php echo esc_url( arctik_asset( 'icons/icn-cart.svg' ) ); ?>" alt="" width="15" height="15">
			</a>
		</div>
	</div>
</li>
