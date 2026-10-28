<?php
/**
 * Начало документа и шапка сайта (src/partials/header.html).
 * Шапка — position: fixed, тема по секции под ней (js/modules/header.js).
 */

defined( 'ABSPATH' ) || exit;

$arctik_phone = (string) arctik_option( 'arctik_phone' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'page' ); ?>>
<?php wp_body_open(); ?>
<header class="header" data-js-header>
	<?php // Меню планшета и мобилки: открывается бургером (js/modules/mobile-menu.js). ?>
	<?php get_template_part( 'template-parts/mobile-menu' ); ?>

	<div class="header__inner container">
		<nav class="header__nav" aria-label="Основное меню">
			<?php arctik_menu( 'header', 'header__menu menu', 'data-js-swipe-group' ); ?>
		</nav>

		<a class="header__logo logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="АрктикБиоМарин — на главную">
			<span class="logo__img"></span>
		</a>

		<div class="header__actions">
			<?php if ( $arctik_phone ) : ?>
				<a class="header__phone" href="<?php echo esc_attr( arctik_phone_href( $arctik_phone ) ); ?>" data-js-swipe><?php echo esc_html( $arctik_phone ); ?></a>
			<?php endif; ?>

			<?php if ( is_user_logged_in() ) : ?>
				<?php
				// Вошедший пользователь (макет 18182:43263) — ссылка на кабинет с первой буквой имени.
				$arctik_user   = wp_get_current_user();
				$arctik_letter = mb_strtoupper( mb_substr( $arctik_user->first_name ?: $arctik_user->display_name, 0, 1 ) );
				?>
				<a class="header__icon-btn header__user" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : admin_url( 'profile.php' ) ); ?>" aria-label="Личный кабинет" data-js-swipe><?php echo esc_html( $arctik_letter ); ?></a>
			<?php else : ?>
				<?php // Гость: кнопка открывает попап входа (template-parts/auth-modal.php). ?>
				<button class="header__icon-btn" type="button" aria-label="Войти в личный кабинет" aria-haspopup="dialog" aria-controls="auth" data-js-auth-open="login" data-js-swipe>
					<svg class="header__icon" width="15" height="15" viewBox="0 0 14.7321 14.7321" fill="none" stroke="currentColor" stroke-width="1.33929" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M12.2824 12.8862V11.6585C12.2824 11.0073 12.0237 10.3828 11.5632 9.92225C11.1027 9.46181 10.4782 9.20312 9.82699 9.20312H4.91629C4.26509 9.20312 3.64056 9.46181 3.18009 9.92225C2.71962 10.3828 2.46094 11.0073 2.46094 11.6585V12.8862"/>
						<path d="M7.36549 6.75444C8.72158 6.75444 9.82087 5.65516 9.82087 4.29911C9.82087 2.94305 8.72158 1.84375 7.36549 1.84375C6.00946 1.84375 4.91016 2.94305 4.91016 4.29911C4.91016 5.65516 6.00946 6.75444 7.36549 6.75444Z"/>
					</svg>
				</button>
			<?php endif; ?>

			<?php // Открывает корзину-попап (template-parts/cart.php). На мобилке, пока корзина открыта, вместо иконки — крестик. ?>
			<button class="header__icon-btn header__cart" type="button" aria-label="Корзина" aria-haspopup="dialog" aria-controls="cart" data-js-cart-open data-js-swipe>
				<svg class="header__icon header__cart-icon" width="15" height="15" viewBox="0 0 14.7321 14.7321" fill="none" stroke="currentColor" stroke-width="1.33929" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M6.36607 13.3884C6.55099 13.3884 6.70089 13.2385 6.70089 13.0536C6.70089 12.8687 6.55099 12.7188 6.36607 12.7188C6.18116 12.7188 6.03125 12.8687 6.03125 13.0536C6.03125 13.2385 6.18116 13.3884 6.36607 13.3884Z"/>
					<path d="M12.3934 13.3884C12.5783 13.3884 12.7282 13.2385 12.7282 13.0536C12.7282 12.8687 12.5783 12.7188 12.3934 12.7188C12.2085 12.7188 12.0586 12.8687 12.0586 13.0536C12.0586 13.2385 12.2085 13.3884 12.3934 13.3884Z"/>
					<path d="M2.01172 2H4.20328L4.75158 4.6785M4.75158 4.6785L5.67162 9.17301C5.72172 9.41964 5.85895 9.64123 6.05928 9.79893C6.25961 9.95656 6.51027 10.0403 6.76739 10.0355H12.0929C12.35 10.0403 12.6007 9.95656 12.801 9.79893C13.0014 9.64123 13.1386 9.41964 13.1887 9.17301L14.0653 4.6785H4.75158ZM4.75158 4.6785H4.75117"/>
				</svg>
				<svg class="header__icon header__cart-close" width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.174" aria-hidden="true">
					<path d="M1.6 1.6L14.4 14.4M14.4 1.6L1.6 14.4"/>
				</svg>
				<?php echo arctik_cart_part( 'count' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
			<button class="header__icon-btn header__burger" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="mobile-menu" data-js-mobile-menu-toggle data-js-swipe>
				<span class="header__burger-lines" aria-hidden="true"></span>
			</button>
		</div>
	</div>

	<?php
	if ( ! is_user_logged_in() ) {
		get_template_part( 'template-parts/auth-modal' );
	}

	get_template_part( 'template-parts/cart' );
	get_template_part( 'template-parts/cookies' );
	?>
</header>
