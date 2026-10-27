<?php
/**
 * «Заказ оформлен» (src/order-received.html): шапка оформления и фон, без подвала; секция на всю высоту (main--flex).
 * Подключается фильтром template_include (inc/order.php) для эндпоинта order-received. Вывод — логика шорткода оформления
 * без обёртки div.woocommerce (иначе секция не тянется на всю высоту): проверка ключа заказа, очистка корзины,
 * шаблон woocommerce/checkout/thankyou.php.
 */

defined( 'ABSPATH' ) || exit;

get_header( 'checkout' );
?>
<main class="main main--flex">
	<?php WC_Shortcode_Checkout::output( [] ); ?>
</main>

<?php
get_footer( 'checkout' );
