<?php
/**
 * Первый экран главной (src/partials/home/hero.html): видео по кругу поверх фото, заголовок, слайдер товаров.
 * Поля — главная, вкладка «Первый экран»; товары — все вкусы (arctik_products()). Переход в about — js/modules/hero-cover.js.
 */

defined( 'ABSPATH' ) || exit;

$arctik_page     = (int) get_option( 'page_on_front' );
$arctik_title    = (string) arctik_field( 'arctik_home_hero_title', $arctik_page );
$arctik_accent   = (string) arctik_field( 'arctik_home_hero_title_accent', $arctik_page );
$arctik_text     = (string) arctik_field( 'arctik_home_hero_text', $arctik_page );
$arctik_photo    = (int) arctik_field( 'arctik_home_hero_photo', $arctik_page );
$arctik_video    = arctik_file_url( arctik_field( 'arctik_home_hero_video', $arctik_page ) );
$arctik_poster   = arctik_file_url( $arctik_photo );
$arctik_products = arctik_products();
?>
<section class="hero" data-header-theme="dark" data-js-hero-cover>
	<div class="hero__bg" aria-hidden="true">
		<?php echo arctik_image( $arctik_photo, 'full', [ 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $arctik_video ) : ?>
			<video class="hero__video" src="<?php echo esc_url( $arctik_video ); ?>"<?php echo $arctik_poster ? ' poster="' . esc_url( $arctik_poster ) . '"' : ''; ?> width="1920" height="1080" autoplay muted loop playsinline preload="auto" disablepictureinpicture></video>
		<?php endif; ?>
	</div>
	<div class="hero__inner container" data-js-hero-cover-parallax>
		<div class="hero__content">
			<h1 class="hero__title" data-js-title-reveal><?php echo arctik_text( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( $arctik_accent ) : ?> <span class="hero__title-accent"><?php echo arctik_text( $arctik_accent ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php endif; ?></h1>
			<?php if ( $arctik_text ) : ?>
				<p class="hero__text" data-js-title-reveal data-title-reveal-start="top bottom"><?php echo arctik_text( $arctik_text ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $arctik_products ) : ?>
			<div class="hero__products swiper" data-js-product-slider data-js-fade-up data-fade-up-delay="0.6" data-fade-up-start="top bottom">
				<ul class="hero__products-list products swiper-wrapper">
					<?php foreach ( $arctik_products as $arctik_product ) : ?>
						<li class="hero__product product swiper-slide">
							<?php get_template_part( 'template-parts/mini-card', null, [ 'product' => $arctik_product, 'mod' => 'mini-card--bare' ] ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<div class="hero__products-dots" data-js-product-slider-pagination></div>
			</div>
		<?php endif; ?>
	</div>
	<div class="hero__shade" aria-hidden="true" data-js-hero-cover-shade></div>
</section>
