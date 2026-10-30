<?php
/**
 * «Заказ оформлен» (src/partials/order/received.html) вместо шаблона WC. Анимация — js/modules/order-success.js.
 * Шаблон страницы — page-templates/order-received.php.
 *
 * Оплата не прошла (статус «Не удался», макета нет) — тот же блок с красным крестиком (order-success--error, icn-error.svg), текстом и кнопкой «Оплатить снова» (сразу платёжная страница банка, inc/payment.php).
 * $order = false (неверная ссылка) — как у WC: просто «Заказ оформлен», без данных заказа.
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;

$arctik_failed = $order && $order->has_status( 'failed' );

if ( $arctik_failed ) {
	$arctik_title = "Оплата не\u{00A0}прошла";
	$arctik_desc  = "Заказ сохранён\u{00A0}— попробуйте оплатить его ещё раз";
	$arctik_link  = arctik_payment_retry_url( $order );
	$arctik_btn   = 'Оплатить снова';
} else {
	$arctik_title = 'Заказ успешно оформлен';
	$arctik_desc  = "Заказ уже отображается в\u{00A0}вашем личном кабинете";
	$arctik_link  = wc_get_account_endpoint_url( 'orders' );
	$arctik_btn   = "Перейти в\u{00A0}лк";
}

if ( $order ) {
	do_action( 'woocommerce_before_thankyou', $order->get_id() );
}
?>
<?php /*
	Заказ оформлен (макет «Заказ оформлен» в секции 18126:59053: 18252:77498 → 18253:80920; планшет 18271:38594 / 18271:38883,
	мобилка 18263:88788 / 18263:88849). Два кадра — анимация: галочка с пульсирующими кольцами, затем текст и кнопка.
*/ ?>
<section class="order-success<?php echo $arctik_failed ? ' order-success--error' : ''; ?>" data-header-theme="dark">
	<div class="order-success__card">
		<div class="order-success__box woocommerce-order" data-js-order-success>
			<div class="order-success__icon" aria-hidden="true">
				<span class="order-success__ring order-success__ring--1" data-js-order-success-ring></span>
				<span class="order-success__ring order-success__ring--2" data-js-order-success-ring></span>
				<span class="order-success__ring order-success__ring--3" data-js-order-success-ring></span>
				<img class="order-success__check" src="<?php echo esc_url( arctik_asset( $arctik_failed ? 'icons/icn-error.svg' : 'icons/icn-success.svg' ) ); ?>" alt="" width="44" height="44" data-js-order-success-check>
			</div>
			<div class="order-success__content" data-js-order-success-content>
				<div class="order-success__text">
					<h1 class="order-success__title woocommerce-thankyou-order-received"><?php echo esc_html( $arctik_title ); ?></h1>
					<p class="order-success__desc"><?php echo esc_html( $arctik_desc ); ?></p>
				</div>
				<a class="btn btn--accent" href="<?php echo esc_url( $arctik_link ); ?>" data-js-swipe><?php echo esc_html( $arctik_btn ); ?></a>
			</div>
		</div>
	</div>
</section>
<?php
if ( $order ) {
	// Способы оплаты и аналитика выводят здесь своё (таблица заказа WC снята — inc/order.php)
	do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
	do_action( 'woocommerce_thankyou', $order->get_id() );
}
