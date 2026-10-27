<?php
/**
 * Меню планшета и мобилки (src/partials/mobile-menu.html), открывается бургером — js/modules/mobile-menu.js.
 */

defined( 'ABSPATH' ) || exit;

$arctik_phone    = (string) arctik_option( 'arctik_phone' );
$arctik_email    = (string) arctik_option( 'arctik_email' );
$arctik_address  = (string) arctik_option( 'arctik_address' );
$arctik_question = (string) arctik_option( 'arctik_question_link' );
?>
<div class="mobile-menu" id="mobile-menu" inert data-js-mobile-menu data-lenis-prevent>
	<div class="mobile-menu__inner">
		<nav aria-label="Меню">
			<?php arctik_menu( 'mobile', 'mobile-menu__list menu', 'data-js-swipe-group' ); ?>
		</nav>

		<div class="mobile-menu__contacts">
			<div class="mobile-menu__info">
				<?php if ( $arctik_email ) : ?>
					<div class="mobile-menu__item">
						<p class="mobile-menu__label">Почта</p>
						<a class="mobile-menu__mail" href="mailto:<?php echo esc_attr( $arctik_email ); ?>" data-js-roll><?php echo esc_html( $arctik_email ); ?></a>
					</div>
				<?php endif; ?>
				<?php if ( $arctik_address ) : ?>
					<div class="mobile-menu__item">
						<p class="mobile-menu__label">Офис</p>
						<address class="mobile-menu__address"><?php echo arctik_text( $arctik_address ); // phpcs:ignore WordPress.Security.EscapeOutput ?></address>
					</div>
				<?php endif; ?>
				<?php if ( arctik_socials() ) : ?>
					<div class="mobile-menu__item">
						<p class="mobile-menu__label mobile-menu__label--media">Медиа</p>
						<?php get_template_part( 'template-parts/socials', null, [ 'mod' => 'socials--large socials--glass', 'size' => 32 ] ); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $arctik_phone ) : ?>
				<div class="mobile-menu__item mobile-menu__item--phone">
					<p class="mobile-menu__label">Телефон</p>
					<a class="mobile-menu__phone" href="<?php echo esc_attr( arctik_phone_href( $arctik_phone ) ); ?>" data-js-roll><?php echo esc_html( $arctik_phone ); ?></a>
				</div>
			<?php endif; ?>

			<?php if ( $arctik_question ) : ?>
				<a class="mobile-menu__btn btn btn--light" href="<?php echo esc_url( $arctik_question ); ?>" data-js-swipe>Задать вопрос</a>
			<?php endif; ?>
		</div>
	</div>
</div>
