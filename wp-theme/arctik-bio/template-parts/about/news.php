<?php
/**
 * Новости (src/partials/about/news.html): записи «Новости» по дате, новые первыми; заголовок — поле страницы (вкладка «Новости»).
 * Нет новостей — секция не выводится, одна — без стрелок.
 */

defined( 'ABSPATH' ) || exit;

$arctik_news = get_posts( [
	'post_type'   => 'arctik_news',
	'post_status' => 'publish',
	'numberposts' => -1,
	'orderby'     => 'date',
	'order'       => 'DESC',
] );
if ( ! $arctik_news ) {
	return;
}
?>
<section class="news" data-header-theme="light">
	<h2 class="section-title" data-js-title-reveal><?php echo arctik_text( arctik_field( 'arctik_news_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>

	<div class="news__inner container">
		<?php // Слайдер с автопролистыванием (js/modules/news-slider.js) ?>
		<div class="news__slider swiper" data-js-news-slider data-js-fade-up>
			<ul class="news__list swiper-wrapper">
				<?php foreach ( $arctik_news as $arctik_item ) : ?>
					<?php get_template_part( 'template-parts/news-item', null, [ 'post' => $arctik_item ] ); ?>
				<?php endforeach; ?>
			</ul>

			<?php if ( count( $arctik_news ) > 1 ) : ?>
				<?php // Стрелки — поверх фото (правая колонка) ?>
				<div class="news__nav">
					<button class="news__arrow news__arrow--prev" type="button" aria-label="Предыдущая новость" data-js-news-slider-prev data-js-swipe>
						<img src="<?php echo esc_url( arctik_asset( 'icons/icn-arrow-back.svg' ) ); ?>" alt="" width="24" height="24">
					</button>
					<button class="news__arrow news__arrow--next" type="button" aria-label="Следующая новость" data-js-news-slider-next data-js-swipe>
						<img src="<?php echo esc_url( arctik_asset( 'icons/icn-arrow-back.svg' ) ); ?>" alt="" width="24" height="24">
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
