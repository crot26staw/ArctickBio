<?php
/**
 * Шапка оформления заказа и страницы «Заказ оформлен» (src/partials/checkout/header.html). Телефон — «Настройки сайта → Контакты».
 */

defined( 'ABSPATH' ) || exit;

$arctik_phone = (string) arctik_option( 'arctik_phone' );
?>
<?php /* Шапка оформления заказа и страницы «Заказ оформлен» (макет 18205:48157): только логотип и телефон */ ?>
<header class="checkout-header">
	<a class="checkout-header__logo logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="АрктикБиоМарин — на главную">
		<span class="logo__img"></span>
	</a>
	<?php if ( $arctik_phone ) : ?>
		<a class="checkout-header__phone" href="<?php echo esc_attr( arctik_phone_href( $arctik_phone ) ); ?>" data-js-swipe><?php echo esc_html( $arctik_phone ); ?></a>
	<?php endif; ?>
</header>
<?php get_template_part( 'template-parts/cookies' ); ?>
