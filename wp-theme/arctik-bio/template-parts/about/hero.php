<?php
/**
 * Первый экран «О компании» (src/partials/about/hero.html): видео по кругу поверх фото — поля страницы, вкладка «Первый экран».
 */

defined( 'ABSPATH' ) || exit;

$arctik_page   = (int) get_the_ID();
$arctik_title  = trim( (string) arctik_field( 'arctik_about_hero_title', $arctik_page ) ) ?: get_the_title();
$arctik_photo  = (int) arctik_field( 'arctik_about_hero_photo', $arctik_page );
$arctik_mobile = wp_get_attachment_image_src( (int) arctik_field( 'arctik_about_hero_photo_mobile', $arctik_page ), 'full' );
$arctik_video  = arctik_file_url( arctik_field( 'arctik_about_hero_video', $arctik_page ) );
?>
<section class="page-hero" data-header-theme="dark">
	<div class="page-hero__bg" aria-hidden="true">
		<?php if ( $arctik_photo ) : ?>
			<picture>
				<?php if ( $arctik_mobile ) : ?>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( $arctik_mobile[0] ); ?>" width="<?php echo esc_attr( $arctik_mobile[1] ); ?>" height="<?php echo esc_attr( $arctik_mobile[2] ); ?>">
				<?php endif; ?>
				<?php echo arctik_image( $arctik_photo, 'full', [ 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</picture>
		<?php endif; ?>
		<?php if ( $arctik_video ) : ?>
			<video class="page-hero__video" src="<?php echo esc_url( $arctik_video ); ?>" width="1920" height="1080" autoplay muted loop playsinline preload="auto" disablepictureinpicture></video>
		<?php endif; ?>
	</div>
	<div class="page-hero__inner container">
		<h1 class="page-hero__title" data-js-title-reveal><?php echo arctik_text( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
	</div>
</section>
