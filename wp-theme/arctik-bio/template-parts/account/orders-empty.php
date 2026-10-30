<?php
/**
 * «Мои заказы» без заказов (src/partials/account/orders-empty.html): без вкладок, корзинка, текст и «К покупкам» (→ каталог).
 */

defined( 'ABSPATH' ) || exit;
?>
<?php /* Нет заказов (макет 16692:130852) */ ?>
<div class="account__head">
	<h1 class="account__title">Мои заказы</h1>
</div>
<div class="account__empty woocommerce-info">
	<img class="account__empty-img" src="<?php echo esc_url( arctik_asset( 'images/cart-empty.webp' ) ); ?>" alt="" width="72" height="72" loading="lazy">
	<div class="account__empty-text">
		<p class="account__empty-title">У&nbsp;вас нет заказов</p>
		<p class="account__empty-desc">Вы&nbsp;ещё ничего не&nbsp;покупали.<br> Самое время сделать первый заказ!</p>
	</div>
	<a class="btn btn--accent btn--small account__empty-btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" data-js-swipe>К&nbsp;покупкам</a>
</div>
