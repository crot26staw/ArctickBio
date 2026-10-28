<?php
/**
 * Оформление заказа (src/checkout.html): своя шапка (логотип и телефон) и фон, без подвала.
 * Подключается фильтром template_include (inc/checkout.php) для страницы WC «Оформление заказа»; форму выводит шорткод
 * [woocommerce_checkout] — шаблон woocommerce/checkout/form-checkout.php. Ниже — попапы пункта выдачи и адреса курьера (если есть способ «Курьер»).
 */

defined( 'ABSPATH' ) || exit;

get_header( 'checkout' );
?>
<main class="main">
	<?php
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
</main>

<?php
if ( ! WC()->cart->is_empty() ) {
	get_template_part( 'template-parts/checkout/pickup-modal' );
	// Курьерской доставки сейчас нет (способа нет в зоне) — попапа адреса тоже
	if ( in_array( 'courier', array_column( arctik_shipping_rates(), 'slot' ), true ) ) {
		get_template_part( 'template-parts/checkout/address-modal' );
	}
}

get_footer( 'checkout' );
