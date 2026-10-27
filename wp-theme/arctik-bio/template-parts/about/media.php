<?php
/**
 * «Больше процессов производства показываем в соцсетях» (src/partials/about/media.html): заголовок — поле страницы
 * (вкладка «Соцсети»), карточки — соцсети из «Настройки сайта». Нет соцсетей — секция не выводится.
 * Фон — картинка темы: карточкам-стеклу нужна его заранее размытая копия (--glass-image в _media.scss, js/modules/fake-glass.js).
 */

defined( 'ABSPATH' ) || exit;

$arctik_socials = arctik_socials();
if ( ! $arctik_socials ) {
	return;
}
?>
<section class="media" data-header-theme="dark" data-js-fake-glass>
	<picture class="media__bg-wrap">
		<source media="(max-width: 1199px)" srcset="<?php echo esc_url( arctik_asset( 'images/company-media-bg-tablet.webp' ) ); ?>" width="1536" height="1566">
		<img class="media__bg" data-js-fake-glass-source src="<?php echo esc_url( arctik_asset( 'images/company-media-bg.webp' ) ); ?>" alt="" width="2880" height="1320" loading="lazy">
	</picture>

	<h2 class="media__title section-title section-title--white" data-js-title-reveal><?php echo arctik_lines( arctik_field( 'arctik_media_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>

	<ul class="media__list" data-js-fade-up-group>
		<?php foreach ( $arctik_socials as $arctik_social ) : ?>
			<?php get_template_part( 'template-parts/media-card', null, $arctik_social ); ?>
		<?php endforeach; ?>
	</ul>
</section>
