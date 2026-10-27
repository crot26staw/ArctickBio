<?php
/**
 * Мини-карточка товара (src/partials/mini-card.html): подвал, корзина «Попробуйте также», hero.
 *
 * $args: product — WC_Product, mod — модификаторы (mini-card--solid mini-card--no-dots…).
 * Заголовок / подзаголовок («Морской коллаген» / «с дикой брусникой») и пачка — поля товара (вкладки «Названия», «Картинки»).
 */

defined( 'ABSPATH' ) || exit;

/** @var WC_Product|null $arctik_product */
$arctik_product = $args['product'] ?? null;
if ( ! $arctik_product instanceof WC_Product ) {
	return;
}

$arctik_link = $arctik_product->get_permalink();

[ $arctik_title, $arctik_subtitle ] = arctik_product_mini_titles( $arctik_product );
?>
<article class="mini-card <?php echo esc_attr( $args['mod'] ?? '' ); ?>">
	<a class="mini-card__media" href="<?php echo esc_url( $arctik_link ); ?>" tabindex="-1" aria-hidden="true">
		<span class="mini-card__img-wrap">
			<?php echo arctik_image( arctik_product_image_id( $arctik_product, 'mini' ), 'woocommerce_thumbnail', [ 'class' => 'mini-card__img', 'alt' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</span>
		<span class="mini-card__dots">
			<span class="mini-card__dot is-active"></span>
			<span class="mini-card__dot"></span>
			<span class="mini-card__dot"></span>
		</span>
	</a>
	<div class="mini-card__body">
		<div class="mini-card__text">
			<a class="mini-card__title" href="<?php echo esc_url( $arctik_link ); ?>"><?php echo arctik_text( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php if ( $arctik_subtitle ) : ?>
				<p class="mini-card__subtitle"><?php echo arctik_text( $arctik_subtitle ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
		<div class="mini-card__actions">
			<button class="mini-card__add icon-btn icon-btn--24 icon-btn--light add_to_cart_button" type="button" aria-label="Добавить в корзину" data-product_id="<?php echo esc_attr( arctik_product_cart_id( $arctik_product ) ); ?>" data-js-swipe>
				<img src="<?php echo esc_url( arctik_asset( 'icons/icn-add.svg' ) ); ?>" alt="" width="18" height="18">
			</button>
			<span class="mini-card__price price"><?php echo wp_kses_post( arctik_product_card_price( $arctik_product ) ); ?></span>
		</div>
	</div>
</article>
