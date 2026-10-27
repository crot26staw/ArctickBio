<?php
/**
 * «Показываем состав полностью» (src/partials/home/compound.html): высокая дорожка, экран сцены прилипает, по скроллу
 * (js/modules/compound-scene.js) стик раскрывается — сначала из него выезжают цифры, потом их сменяют бейджи витаминов.
 * Поля — главная, вкладка «Состав». Видео стика и камни — файлы темы. Наезд video — overlap-stack--track в front-page.php.
 */

defined( 'ABSPATH' ) || exit;

$arctik_page     = (int) get_option( 'page_on_front' );
$arctik_stats    = array_slice( (array) arctik_field( 'arctik_home_compound_stats', $arctik_page ), 0, 2 );
$arctik_vitamins = array_slice( (array) arctik_field( 'arctik_home_compound_vitamins', $arctik_page ), 0, 6 );

// Места на сцене — модификаторы из вёрстки, по порядку: цифры слева / справа, витамины — три слева, три справа.
$arctik_stat_mods    = [ 'percent', 'mass' ];
$arctik_vitamin_mods = [ 'c', 'e', 'k', 'h', 'pp', 'a' ];
?>
<section class="compound" data-header-theme="light" data-js-compound>
	<div class="compound__sticky">
		<div class="compound__rocks" aria-hidden="true" data-js-compound-rocks>
			<img src="<?php echo esc_url( arctik_asset( 'images/rock-bottom-back.webp' ) ); ?>" alt="" width="2172" height="724" loading="lazy">
			<img src="<?php echo esc_url( arctik_asset( 'images/rock-bottom.webp' ) ); ?>" alt="" width="2169" height="725" loading="lazy">
		</div>

		<div class="compound__stage">
			<h2 class="compound__title section-title" data-js-title-reveal><?php echo arctik_text( arctik_field( 'arctik_home_compound_title', $arctik_page ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>

			<?php // Сцена под заголовком: стик и витамины позиционируются от её центра, при нехватке высоты она сжимается вместе со стиком. ?>
			<div class="compound__scene">
				<div class="compound__sachet" aria-hidden="true" data-js-compound-sachet>
					<video src="<?php echo esc_url( arctik_asset( 'video/stick.mp4' ) ); ?>" poster="<?php echo esc_url( arctik_asset( 'images/stick-video-poster.webp' ) ); ?>" width="600" height="1186" muted playsinline preload="auto" disablepictureinpicture data-js-compound-video></video>
				</div>

				<?php foreach ( $arctik_stats as $arctik_i => $arctik_stat ) : ?>
					<?php $arctik_notes = array_filter( array_map( 'trim', explode( "\n", (string) ( $arctik_stat['notes'] ?? '' ) ) ) ); ?>
					<div class="compound-stat compound-stat--<?php echo esc_attr( $arctik_stat_mods[ $arctik_i ] ); ?>" data-js-compound-stat>
						<div class="compound-stat__main">
							<p class="compound-stat__value"><?php echo arctik_text( $arctik_stat['amount'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( ! empty( $arctik_stat['unit'] ) ) : ?> <span class="compound-stat__unit"><?php echo arctik_text( $arctik_stat['unit'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php endif; ?></p>
							<p class="compound-stat__label"><?php echo arctik_lines( $arctik_stat['label'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						</div>
						<?php if ( $arctik_notes ) : ?>
							<ul class="compound-stat__notes">
								<?php foreach ( $arctik_notes as $arctik_note ) : ?>
									<li>- <?php echo arctik_text( $arctik_note ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>

				<?php // Бейджи — когда стик раскрылся. Карточка с описанием всплывает при наведении/фокусе; подпись витамина лежит в её нижнем углу. ?>
				<?php if ( $arctik_vitamins ) : ?>
					<ul class="compound__vitamins">
						<?php
						foreach ( $arctik_vitamins as $arctik_i => $arctik_vitamin ) :
							$arctik_left  = $arctik_i < 3;
							$arctik_berry = wp_get_attachment_image_url( (int) ( $arctik_vitamin['image'] ?? 0 ), 'thumbnail' );
							ob_start();
							?>
							<span class="vitamin__name"><?php echo arctik_text( $arctik_vitamin['name'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<?php $arctik_name = ob_get_clean(); ?>
							<li class="vitamin vitamin--<?php echo esc_attr( $arctik_vitamin_mods[ $arctik_i ] ); ?> vitamin--<?php echo $arctik_left ? 'left' : 'right'; ?>" tabindex="0" data-js-compound-vitamin>
								<?php echo $arctik_left ? $arctik_name : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<span class="vitamin__icon">
									<img src="<?php echo esc_url( arctik_asset( 'icons/icn-ingredients.svg' ) ); ?>" alt="" width="32" height="32">
									<?php if ( $arctik_berry ) : ?>
										<img class="vitamin__berry" src="<?php echo esc_url( $arctik_berry ); ?>" alt="" width="40" height="40" loading="lazy">
									<?php endif; ?>
								</span>
								<?php echo $arctik_left ? '' : $arctik_name; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<div class="vitamin__card">
									<p class="vitamin__text"><?php echo arctik_text( $arctik_vitamin['text'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
