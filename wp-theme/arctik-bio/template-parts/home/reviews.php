<?php
/**
 * Отзывы (src/partials/home/reviews.html): заголовок и кнопка — главная, вкладка «Отзывы» (кнопка ведёт в каталог);
 * карточки — записи «Отзывы» по дате, новые первыми. Нет отзывов — секции нет.
 * Фон — картинка темы: карточкам-стеклу нужна его заранее размытая копия (js/modules/fake-glass.js).
 */

defined( 'ABSPATH' ) || exit;

$arctik_reviews = get_posts( [ 'post_type' => 'arctik_review', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC' ] );
if ( ! $arctik_reviews ) {
	return;
}

$arctik_page   = (int) get_option( 'page_on_front' );
$arctik_button = (string) arctik_field( 'arctik_home_reviews_button', $arctik_page );
?>
<section class="reviews" id="reviews" data-header-theme="dark" data-js-fake-glass data-js-reveal-sequence data-reveal-sequence-play>
	<div class="reviews__bg-wrap" aria-hidden="true">
		<img class="reviews__bg" data-js-fake-glass-source src="<?php echo esc_url( arctik_asset( 'images/review-bg.webp' ) ); ?>" alt="" width="1448" height="1086" loading="lazy">
	</div>

	<div class="reviews__header">
		<h2 class="section-title section-title--white" data-js-reveal-sequence-item data-reveal-sequence-split><?php echo arctik_lines( arctik_field( 'arctik_home_reviews_title', $arctik_page ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		<?php if ( $arctik_button ) : ?>
			<a class="btn btn--light" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" data-js-swipe data-js-reveal-sequence-item><?php echo arctik_text( $arctik_button ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<?php endif; ?>
	</div>

	<div class="reviews__viewport" data-js-drag-slider data-drag-slider-align-mobile="start">
		<ul class="reviews__list" data-js-drag-slider-track data-js-reveal-sequence-item data-reveal-sequence-children="visible">
			<?php
			foreach ( $arctik_reviews as $arctik_review ) {
				get_template_part( 'template-parts/review-card', null, [ 'review' => $arctik_review ] );
			}
			?>
		</ul>
	</div>
</section>
