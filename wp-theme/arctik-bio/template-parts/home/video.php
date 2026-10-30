<?php
/**
 * Видео «Мы собираем ягоды…» (src/partials/home/video.html). Контент — «Настройки сайта → Общие блоки → Видео».
 * Превью — фон блока, полное видео раскрывается по «Смотреть» (js/modules/video.js).
 */

defined( 'ABSPATH' ) || exit;

$arctik_button  = (string) arctik_option( 'arctik_video_button' );
$arctik_preview = arctik_file_url( arctik_option( 'arctik_video_preview' ) );
$arctik_poster  = arctik_file_url( arctik_option( 'arctik_video_poster' ) );
// Постеры на мобилке — уменьшенные копии из медиатеки (lazy-media.js, data-poster-mobile)
$arctik_poster_mobile = (string) wp_get_attachment_image_url( (int) arctik_option( 'arctik_video_poster' ), 'large' );
$arctik_card_mobile   = (string) wp_get_attachment_image_url( (int) arctik_option( 'arctik_video_card' ), 'woocommerce_single' );
$arctik_main    = arctik_file_url( arctik_option( 'arctik_video_main' ) );
$arctik_card_id = (int) arctik_option( 'arctik_video_card' );
?>
<section class="video" data-header-theme="dark" data-js-cursor-follow-area data-js-video>
	<?php // Фон — превью без звука, по кругу; грузится и играет, только когда блок рядом с экраном (js/modules/video.js); постеры — тоже рядом с экраном (lazy-media.js). ?>
	<div class="video__bg" aria-hidden="true">
		<video src="<?php echo esc_url( $arctik_preview ); ?>" data-poster="<?php echo esc_url( $arctik_poster ); ?>" data-poster-mobile="<?php echo esc_url( $arctik_poster_mobile ); ?>" width="1920" height="1080" muted loop playsinline preload="none" data-js-video-preview data-js-lazy-media></video>
	</div>
	<img class="video__light" src="<?php echo esc_url( arctik_asset( 'images/video-light.webp' ) ); ?>" alt="" width="1920" height="1088" loading="lazy" aria-hidden="true">

	<?php // Следует за курсором в пределах блока (js/modules/cursor-follow.js). ?>
	<button class="video__play" type="button" data-js-video-open data-js-swipe data-js-cursor-follow>
		<img src="<?php echo esc_url( arctik_asset( 'icons/icn-play.svg' ) ); ?>" alt="" width="12" height="12">
		<?php echo arctik_text( $arctik_button ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</button>

	<div class="video__inner container">
		<h2 class="video__title" data-js-title-reveal><?php echo arctik_lines( arctik_option( 'arctik_video_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>

		<button class="video__card" type="button" data-js-fade-up data-js-video-open data-js-video-card data-swipe-trigger>
			<?php echo arctik_image( $arctik_card_id, 'large', [ 'class' => 'video__card-img', 'alt' => '', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php // Кнопка на превью; гаснет, когда курсор над карточкой. ?>
			<span class="video__play video__play--card" aria-hidden="true">
				<img src="<?php echo esc_url( arctik_asset( 'icons/icn-play.svg' ) ); ?>" alt="" width="12" height="12">
				<?php echo arctik_text( $arctik_button ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</span>
			<span class="video__card-label">
				<?php echo arctik_text( $arctik_button ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="icon-btn icon-btn--24" data-js-swipe>
					<img src="<?php echo esc_url( arctik_asset( 'icons/icn-play-18.svg' ) ); ?>" alt="" width="18" height="18">
				</span>
			</span>
		</button>
	</div>

	<?php // Полное видео со звуком: по «Смотреть» раскрывается из карточки-картинки (или кнопки) на весь блок (js/modules/video.js). ?>
	<div class="video__player" data-js-video-player hidden>
		<video class="video__player-media" src="<?php echo esc_url( $arctik_main ); ?>" data-poster="<?php echo esc_url( arctik_file_url( $arctik_card_id ) ); ?>" data-poster-mobile="<?php echo esc_url( $arctik_card_mobile ); ?>" width="1920" height="1080" playsinline preload="none" data-js-video-main data-js-lazy-media></video>
		<button class="video__close" type="button" aria-label="Закрыть видео" data-js-video-close>
			<svg width="44" height="44" viewBox="0 0 44 44" fill="currentColor" aria-hidden="true">
				<path d="M23.414 22L30 15.414L28.586 14L22 20.586L15.414 14L14 15.414L20.586 22L14 28.586L15.414 30L22 23.414L28.586 30L30 28.586L23.414 22Z"/>
			</svg>
		</button>
	</div>
</section>
