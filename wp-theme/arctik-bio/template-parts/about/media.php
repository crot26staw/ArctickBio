<?php
/**
 * «Больше процессов производства показываем в соцсетях» (src/partials/about/media.html): заголовок — поле страницы
 * (вкладка «Соцсети»), карточки — соцсети из «Настройки сайта». Нет соцсетей — секция не выводится.
 * Фон — поля вкладки «Соцсети» (общий и планшет / мобилка; пусто — картинки темы, у планшета — сначала общий фон);
 * карточкам-стеклу нужна его размытая копия (--media-glass(-tablet) → --glass-image в _media.scss, inc/glass.php, js/modules/fake-glass.js).
 */

defined( 'ABSPATH' ) || exit;

$arctik_socials = arctik_socials();
if ( ! $arctik_socials ) {
	return;
}

$arctik_bg        = arctik_glass_bg( (int) arctik_field( 'arctik_media_bg' ), 16, 1440 );
$arctik_bg_tablet = arctik_glass_bg( (int) arctik_field( 'arctik_media_bg_tablet' ), 16, 768 ) ?? $arctik_bg;

$arctik_style = '';
if ( $arctik_bg ) {
	$arctik_style .= '--media-glass: url(' . esc_url( $arctik_bg['glass'] ) . ');';
}
if ( $arctik_bg_tablet ) {
	$arctik_style .= ' --media-glass-tablet: url(' . esc_url( $arctik_bg_tablet['glass'] ) . ');';
}
?>
<section class="media" data-header-theme="dark" data-js-fake-glass<?php echo $arctik_style ? ' style="' . esc_attr( trim( $arctik_style ) ) . '"' : ''; ?>>
	<picture class="media__bg-wrap">
		<?php if ( ! $arctik_bg_tablet ) : ?>
			<source media="(max-width: 1199px)" srcset="<?php echo esc_url( arctik_asset( 'images/company-media-bg-tablet.webp' ) ); ?>" width="1536" height="1566">
		<?php elseif ( $arctik_bg_tablet !== $arctik_bg ) : ?>
			<?php $arctik_meta = wp_get_attachment_metadata( $arctik_bg_tablet['id'] ); ?>
			<source media="(max-width: 1199px)" srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( $arctik_bg_tablet['id'], 'full' ) ?: wp_get_attachment_url( $arctik_bg_tablet['id'] ) ); ?>" sizes="100vw" width="<?php echo (int) ( $arctik_meta['width'] ?? 0 ); ?>" height="<?php echo (int) ( $arctik_meta['height'] ?? 0 ); ?>">
		<?php endif; ?>
		<?php if ( $arctik_bg ) : ?>
			<?php echo arctik_image( $arctik_bg['id'], 'full', [ 'class' => 'media__bg', 'data-js-fake-glass-source' => '', 'alt' => '', 'sizes' => '100vw', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php else : ?>
			<img class="media__bg" data-js-fake-glass-source src="<?php echo esc_url( arctik_asset( 'images/company-media-bg.webp' ) ); ?>" alt="" width="2880" height="1320" loading="lazy">
		<?php endif; ?>
	</picture>

	<h2 class="media__title section-title section-title--white" data-js-title-reveal><?php echo arctik_lines( arctik_field( 'arctik_media_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>

	<ul class="media__list" data-js-fade-up-group>
		<?php foreach ( $arctik_socials as $arctik_social ) : ?>
			<?php get_template_part( 'template-parts/media-card', null, $arctik_social ); ?>
		<?php endforeach; ?>
	</ul>
</section>
