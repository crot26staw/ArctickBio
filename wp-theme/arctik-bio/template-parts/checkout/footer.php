<?php
/**
 * Подвал карточки оформления и статуса заказа (footer.checkout__footer в src/partials/checkout/main.html и order/status.html):
 * меню «Оформление заказа: ссылки внизу» и телефон поддержки из «Настройки сайта → Контакты».
 * $args: pay — логотипы платёжных систем (только на мобилке, оформление заказа).
 */

defined( 'ABSPATH' ) || exit;

$arctik_phone = (string) arctik_option( 'arctik_phone' );
?>
<footer class="checkout__footer">
	<?php arctik_menu( 'checkout', 'checkout__links', 'data-js-roll-group' ); ?>
	<?php if ( $arctik_phone ) : ?>
		<div class="checkout__support">
			<p>Служба поддержки 24/7</p>
			<a class="checkout__support-phone" href="<?php echo esc_attr( arctik_phone_href( $arctik_phone ) ); ?>" data-js-roll><?php echo esc_html( $arctik_phone ); ?></a>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $args['pay'] ) ) : ?>
		<?php get_template_part( 'template-parts/pay-logos', null, [ 'mod' => 'checkout__footer-pay' ] ); ?>
	<?php endif; ?>
</footer>
