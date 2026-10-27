<?php
/**
 * Плашка товара со скидкой (src/partials/product-plan.html): баннер набора в каталоге. Цена — del (обычная) + ins (со скидкой).
 *
 * $args: product — WC_Product.
 */

defined( 'ABSPATH' ) || exit;

/** @var WC_Product|null $arctik_product */
$arctik_product = $args['product'] ?? null;
if ( ! $arctik_product instanceof WC_Product ) {
	return;
}

[ $arctik_title, $arctik_subtitle ] = arctik_product_mini_titles( $arctik_product );

$arctik_regular = (float) $arctik_product->get_regular_price();
$arctik_price   = (float) $arctik_product->get_price();
?>
<a class="product-plan" href="<?php echo esc_url( $arctik_product->get_permalink() ); ?>" data-js-fade-up>
	<?php echo arctik_image( arctik_product_image_id( $arctik_product ), 'medium', [ 'class' => 'product-plan__img', 'alt' => '', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<span class="product-plan__text">
		<span class="product-plan__title"><?php echo arctik_text( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<span class="product-plan__subtitle"><?php echo arctik_text( $arctik_subtitle ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	</span>
	<span class="product-plan__price price">
		<?php if ( $arctik_regular > $arctik_price ) : ?>
			<del aria-label="Старая цена"><?php echo wp_kses_post( wc_price( $arctik_regular ) ); ?></del>
		<?php endif; ?>
		<ins aria-label="Цена"><?php echo wp_kses_post( wc_price( $arctik_price ) ); ?></ins>
	</span>
</a>
