<?php
/**
 * Формат товара (src/partials/product-format.html): радиокнопка-плашка выбора вариации на странице товара.
 * name="variation_id", value — ID вариации (форма без JS — обработчик WC, с JS — AJAX из cart.js),
 * data-price / data-max — цена и максимум количества (product-format.js). Нет в наличии — disabled.
 *
 * $args: format — элемент arctik_product_formats(), product — родительский WC_Product (пачка, если у вариации нет картинки).
 */

defined( 'ABSPATH' ) || exit;

$arctik_format = $args['format'] ?? null;
if ( ! $arctik_format ) {
	return;
}

/** @var WC_Product_Variation $arctik_variation */
$arctik_variation = $arctik_format['variation'];
$arctik_price     = wc_get_price_to_display( $arctik_variation );
$arctik_regular   = wc_get_price_to_display( $arctik_variation, [ 'price' => $arctik_variation->get_regular_price() ] );
$arctik_image_id  = (int) $arctik_variation->get_image_id() ?: arctik_product_image_id( $args['product'], 'mini' );
?>
<label class="product-plan product-plan--option">
	<input class="product-plan__input" type="radio" name="variation_id" value="<?php echo esc_attr( $arctik_variation->get_id() ); ?>" data-price="<?php echo esc_attr( arctik_price_text( $arctik_price ) ); ?>" data-max="<?php echo esc_attr( arctik_product_max_qty( $arctik_variation ) ); ?>" data-js-product-format<?php checked( $arctik_format['checked'] ); ?><?php disabled( ! $arctik_format['available'] ); ?>>
	<?php echo arctik_image( $arctik_image_id, 'woocommerce_thumbnail', [ 'class' => 'product-plan__img', 'alt' => '', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<span class="product-plan__text">
		<span class="product-plan__title"><?php echo arctik_text( $arctik_format['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<?php if ( $arctik_variation->get_description() ) : ?>
			<span class="product-plan__subtitle"><?php echo arctik_text( wp_strip_all_tags( $arctik_variation->get_description() ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<?php endif; ?>
	</span>
	<span class="product-plan__price price">
		<?php if ( $arctik_regular > $arctik_price ) : ?>
			<del aria-label="Старая цена"><?php echo wp_kses_post( wc_price( $arctik_regular ) ); ?></del>
		<?php endif; ?>
		<ins aria-label="Цена"><?php echo wp_kses_post( wc_price( $arctik_price ) ); ?></ins>
	</span>
</label>
