<?php
/**
 * Карточка отзыва (src/partials/review-card.html). $args: review — WP_Post типа arctik_review.
 * Звёзд — по оценке (в макете есть только 5 из 5); товар — пачка и заголовки мини-карточки.
 */

defined( 'ABSPATH' ) || exit;

/** @var WP_Post|null $arctik_review */
$arctik_review = $args['review'] ?? null;
if ( ! $arctik_review instanceof WP_Post ) {
	return;
}

$arctik_id      = $arctik_review->ID;
$arctik_rating  = max( 1, min( 5, (int) arctik_field( 'arctik_review_rating', $arctik_id ) ?: 5 ) );
$arctik_assoc   = (array) arctik_field( 'arctik_review_product', $arctik_id );
$arctik_product = $arctik_assoc ? wc_get_product( (int) $arctik_assoc[0]['id'] ) : null;
$arctik_star    = fn( string $tone ) => '<img src="' . esc_url( arctik_asset( "icons/star-{$tone}.svg" ) ) . '" alt="" width="16" height="16">';
?>
<li class="review-card" data-js-fake-glass-item>
	<div class="review-card__header">
		<p class="review-card__name"><?php echo arctik_text( arctik_field( 'arctik_review_name', $arctik_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		<time class="review-card__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $arctik_review ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/y', $arctik_review ) ); ?></time>
	</div>
	<div class="review-card__stars" role="img" aria-label="<?php echo esc_attr( "Оценка {$arctik_rating} из 5" ); ?>">
		<?php echo str_repeat( $arctik_star( 'light' ), $arctik_rating ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="review-card__stars-dark" aria-hidden="true">
			<?php echo str_repeat( $arctik_star( 'dark' ), $arctik_rating ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</span>
	</div>
	<p class="review-card__title"><?php echo arctik_text( get_the_title( $arctik_review ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	<p class="review-card__text"><?php echo arctik_lines( arctik_field( 'arctik_review_text', $arctik_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	<?php if ( $arctik_product ) : ?>
		<?php [ $arctik_title, $arctik_subtitle ] = arctik_product_mini_titles( $arctik_product ); ?>
		<div class="review-card__product">
			<?php echo arctik_image( arctik_product_image_id( $arctik_product, 'mini' ), 'woocommerce_thumbnail', [ 'class' => 'review-card__product-img', 'alt' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div>
				<p class="review-card__product-title"><?php echo arctik_text( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php if ( $arctik_subtitle ) : ?>
					<p class="review-card__product-subtitle"><?php echo arctik_text( $arctik_subtitle ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</li>
