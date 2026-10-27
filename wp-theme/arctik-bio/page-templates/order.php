<?php
/**
 * Статус заказа (src/order.html): обычная шапка, фон оформления, без подвала; секция на всю высоту (main--flex).
 * Подключается фильтром template_include (inc/order.php) для эндпоинта кабинета view-order, если заказ свой (arctik_viewed_order()).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<img class="page__bg" src="<?php echo esc_url( arctik_asset( 'images/checkout-bg.webp' ) ); ?>" alt="" width="936" height="1024">

<main class="main main--flex">
	<?php get_template_part( 'template-parts/order/status', null, [ 'order' => arctik_viewed_order() ] ); ?>
</main>

<?php
get_footer( 'checkout' );
