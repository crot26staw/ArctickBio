<?php
/**
 * О бренде (src/partials/home/about.html): заголовок, текст, 3 карточки. Поля — главная, вкладка «О бренде».
 * Появление и перелёт стика — js/modules/sachet-journey.js (обёртка .sachet-journey в front-page.php).
 */

defined( 'ABSPATH' ) || exit;

$arctik_page  = (int) get_option( 'page_on_front' );
$arctik_cards = (array) arctik_field( 'arctik_home_about_cards', $arctik_page );
$arctik_text  = (string) arctik_field( 'arctik_home_about_text', $arctik_page );
?>
<section class="about" data-header-theme="light">
	<div class="about__bg" aria-hidden="true">
		<img class="about__mountains" data-js-sachet-journey-bg src="<?php echo esc_url( arctik_asset( 'images/about-mountains.webp' ) ); ?>" alt="" width="1672" height="941" loading="lazy">
		<div class="about__clouds-wrap">
			<img class="about__clouds" src="<?php echo esc_url( arctik_asset( 'images/about-clouds.webp' ) ); ?>" alt="" width="648" height="405" loading="lazy">
		</div>
	</div>

	<div class="about__header container">
		<h2 class="about__title" data-js-title-reveal data-title-reveal-start="top 56%"><?php echo arctik_lines( arctik_field( 'arctik_home_about_title', $arctik_page ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		<?php if ( $arctik_text ) : ?>
			<p class="about__text" data-js-title-reveal data-title-reveal-start="top 56%"><?php echo arctik_lines( $arctik_text, 'about__text-br' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		<?php endif; ?>
	</div>

	<ul class="about__cards container">
		<?php foreach ( $arctik_cards as $arctik_card ) : ?>
			<li class="about-card" data-js-sachet-journey-card>
				<h3 class="about-card__title"><?php echo arctik_lines( $arctik_card['title'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
				<?php if ( ! empty( $arctik_card['icon'] ) ) : ?>
					<img class="about-card__icon" data-js-sachet-journey-card-icon src="<?php echo esc_url( arctik_icon_url( $arctik_card['icon'] ) ); ?>" alt="" width="80" height="80" loading="lazy">
				<?php endif; ?>
				<p><?php echo arctik_lines( $arctik_card['text'] ?? '', 'about-card__br' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
