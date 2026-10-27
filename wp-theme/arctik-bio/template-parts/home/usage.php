<?php
/**
 * «Рекомендуемый курс» (src/partials/home/usage.html). Контент — «Настройки сайта → Общие блоки → Рекомендуемый курс».
 * Появление по скроллу — js/modules/reveal-sequence.js.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="usage container" data-header-theme="light" data-js-reveal-sequence>
	<h2 class="usage__title section-title" data-js-reveal-sequence-item data-reveal-sequence-split><?php echo arctik_lines( arctik_option( 'arctik_usage_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>

	<div class="usage__grid">
		<div class="usage__main" data-js-reveal-sequence-item>
			<?php echo arctik_image( (int) arctik_option( 'arctik_usage_image' ), 'full', [ 'class' => 'usage__main-img', 'alt' => '', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php foreach ( (array) arctik_option( 'arctik_usage_badges' ) as $arctik_badge ) : ?>
				<div class="usage__badge">
					<span class="icon-circle"><img src="<?php echo esc_url( arctik_icon_url( $arctik_badge['icon'] ) ); ?>" alt="" width="24" height="24"></span>
					<p class="usage__badge-text"><?php echo arctik_text( $arctik_badge['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="usage__cards">
			<div class="usage__card usage__card--glass" data-js-reveal-sequence-item>
				<?php echo arctik_image( (int) arctik_option( 'arctik_usage_glass_image' ), 'large', [ 'class' => 'usage__glass-img', 'alt' => '', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p class="usage__card-text"><?php echo arctik_lines( arctik_option( 'arctik_usage_glass_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			</div>
			<div class="usage__card usage__card--time" data-js-reveal-sequence-item>
				<span class="icon-circle"><img src="<?php echo esc_url( arctik_icon_url( (string) arctik_option( 'arctik_usage_time_icon' ) ) ); ?>" alt="" width="24" height="24"></span>
				<p class="usage__card-text"><?php echo arctik_lines( arctik_option( 'arctik_usage_time_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			</div>
		</div>
	</div>
</section>
