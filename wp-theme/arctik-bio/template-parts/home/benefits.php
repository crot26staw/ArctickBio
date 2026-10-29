<?php
/**
 * «Регулярный приём помогает поддерживать» (src/partials/home/benefits.html). Контент — «Настройки сайта → Общие блоки → Регулярный приём».
 * Фон — поле там же (пусто — картинка темы); карточкам-стеклу нужна его размытая копия (--glass-image, inc/glass.php, js/modules/fake-glass.js).
 *
 * $args: mod — модификаторы секции (benefits--hidden-mobile).
 */

defined( 'ABSPATH' ) || exit;

$arctik_bg = arctik_glass_bg( (int) arctik_option( 'arctik_benefits_bg' ), 14, 1440 );
?>
<section class="benefits <?php echo esc_attr( $args['mod'] ?? '' ); ?>" data-header-theme="dark" data-js-fake-glass<?php echo $arctik_bg ? ' style="--glass-image: url(' . esc_url( $arctik_bg['glass'] ) . ')"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<?php if ( $arctik_bg ) : ?>
		<?php echo arctik_image( $arctik_bg['id'], 'full', [ 'class' => 'benefits__bg', 'data-js-fake-glass-source' => '', 'alt' => '', 'sizes' => '100vw', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<?php else : ?>
		<img class="benefits__bg" data-js-fake-glass-source src="<?php echo esc_url( arctik_asset( 'images/benefits-bg.webp' ) ); ?>" alt="" width="1683" height="935" loading="lazy">
	<?php endif; ?>
	<div class="benefits__inner container">
		<h2 class="section-title section-title--white" data-js-title-reveal><?php echo arctik_lines( arctik_option( 'arctik_benefits_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		<?php // На планшете ряд шире экрана — листается свайпом (js/modules/drag-slider.js). ?>
		<div class="benefits__viewport" data-js-drag-slider data-drag-slider-align="start">
			<ul class="benefits__list" data-js-cards-rise data-js-drag-slider-track>
				<?php foreach ( (array) arctik_option( 'arctik_benefits_cards' ) as $arctik_card ) : ?>
					<li class="benefit-card" data-js-cards-rise-item data-js-fake-glass-item>
						<img class="benefit-card__icon" src="<?php echo esc_url( arctik_icon_url( $arctik_card['icon'] ) ); ?>" alt="" width="80" height="80" loading="lazy">
						<p class="benefit-card__title"><?php echo arctik_lines( $arctik_card['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
