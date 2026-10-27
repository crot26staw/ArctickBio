<?php
/**
 * Шаг «Процесса изготовления» с попапом (src/partials/production-step.html), js/modules/step-popup.js.
 *
 * $args: num — номер шага, step — шаг из «Общие блоки → Процесс» (title, image, popup_title, popup_text, popup_video, popup_poster).
 */

defined( 'ABSPATH' ) || exit;

$arctik_num    = (int) $args['num'];
$arctik_step   = $args['step'];
$arctik_title  = (string) $arctik_step['title'];
$arctik_popup  = trim( (string) $arctik_step['popup_title'] ) ?: preg_replace( '/\s*\R\s*/', ' ', trim( $arctik_title ) );
$arctik_video  = arctik_file_url( $arctik_step['popup_video'] ?? 0 );
$arctik_poster = arctik_file_url( $arctik_step['popup_poster'] ?? 0 );
?>
<li class="production-step production-step--<?php echo esc_attr( $arctik_num ); ?>" data-js-step-popup>
	<h3 class="production-step__title"><span><?php echo arctik_lines( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></h3>
	<div class="production-step__img-wrap">
		<?php echo arctik_image( (int) $arctik_step['image'], 'full', [ 'class' => 'production-step__img', 'alt' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
	<span class="production-step__beam" aria-hidden="true"></span>
	<button class="production-step__marker" type="button" aria-label="<?php echo esc_attr( "Подробнее о шаге {$arctik_num}" ); ?>" aria-expanded="false" data-js-step-popup-toggle data-js-swipe>
		<img src="<?php echo esc_url( arctik_asset( 'icons/icn-ingredients-accent.svg' ) ); ?>" alt="" width="32" height="32">
	</button>
	<?php // Попап шага. Мобилка: окно поверх страницы — подложка (сам __popup) и карточка с крестиком ?>
	<div class="production-step__popup">
		<div class="production-step__popup-card" data-js-step-popup-card>
			<div class="production-step__popup-text">
				<span class="production-step__popup-num"><?php echo esc_html( $arctik_num ); ?></span>
				<div>
					<p class="production-step__popup-title"><?php echo arctik_text( $arctik_popup ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<?php if ( '' !== trim( (string) $arctik_step['popup_text'] ) ) : ?>
						<p class="production-step__popup-desc"><?php echo arctik_lines( $arctik_step['popup_text'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<?php endif; ?>
				</div>
			</div>
			<div class="production-step__popup-img-wrap">
				<?php // Видео играет, пока попап открыт; без src — только картинка (poster). ?>
				<video class="production-step__popup-video"<?php echo $arctik_video ? ' src="' . esc_url( $arctik_video ) . '"' : ''; ?><?php echo $arctik_poster ? ' poster="' . esc_url( $arctik_poster ) . '"' : ''; ?> width="700" height="394" muted loop playsinline preload="none" aria-hidden="true" data-js-step-popup-video></video>
			</div>
			<button class="production-step__popup-close" type="button" aria-label="Закрыть" data-js-step-popup-close>
				<svg width="44" height="44" viewBox="0 0 44 44" fill="currentColor" aria-hidden="true">
					<path d="M23.414 22L30 15.414L28.586 14L22 20.586L15.414 14L14 15.414L20.586 22L14 28.586L15.414 30L22 23.414L28.586 30L30 28.586L23.414 22Z"/>
				</svg>
			</button>
		</div>
	</div>
</li>
