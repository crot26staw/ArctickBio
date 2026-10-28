<?php
/**
 * Заказ в списке «Мои заказы» (src/partials/account/order-card.html). Ссылка — на статус заказа (view-order), растянута на всю карточку.
 * $args: order — WC_Order. Картинка — пачка первого товара (как в «Вашем заказе»), статус — шаг шкалы (arctik_order_card_status()).
 */

defined( 'ABSPATH' ) || exit;

$arctik_order   = $args['order'];
$arctik_created = $arctik_order->get_date_created();
$arctik_product = null;

foreach ( $arctik_order->get_items() as $arctik_item ) {
	$arctik_variation = $arctik_item->get_product();
	if ( $arctik_variation instanceof WC_Product ) {
		$arctik_product = wc_get_product( $arctik_variation->get_parent_id() ?: $arctik_variation->get_id() );
		break;
	}
}
?>
<?php /* Заказ в списке (макет 16692:131077) */ ?>
<li class="order-card woocommerce-orders-table__row">
	<div class="order-card__head">
		<p class="order-card__number"><span>#<?php echo esc_html( $arctik_order->get_order_number() ); ?></span><?php if ( $arctik_created ) : ?> <span class="order-card__date">от&nbsp;<?php echo esc_html( $arctik_created->date_i18n( 'd.m.Y' ) ); ?></span><?php endif; ?></p>
		<a class="order-card__status" href="<?php echo esc_url( $arctik_order->get_view_order_url() ); ?>"><?php echo esc_html( arctik_order_card_status( $arctik_order ) ); ?>
			<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8.4 8L5.33333 4.93333L6.26667 4L10.2667 8L6.26667 12L5.33333 11.0667L8.4 8Z"/></svg>
		</a>
	</div>
	<div class="order-card__body">
		<span class="order-card__thumb">
			<?php echo $arctik_product ? arctik_image( arctik_product_image_id( $arctik_product, 'mini' ), 'woocommerce_thumbnail', [ 'alt' => '', 'loading' => 'lazy' ] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</span>
		<p class="order-card__info">
			<span class="order-card__label">Состав заказа</span>
			<span class="order-card__value"><?php echo esc_html( arctik_checkout_count_text( $arctik_order->get_item_count() ) ); ?></span>
		</p>
		<p class="order-card__info order-card__info--end">
			<span class="order-card__label">Сумма</span>
			<span class="order-card__value"><?php echo esc_html( arctik_price_text( $arctik_order->get_total() ) ); ?></span>
		</p>
	</div>
</li>
