<?php
/**
 * Статус заказа (src/partials/order/status.html). $args: order — WC_Order (свой, проверен в arctik_viewed_order()).
 *
 * Шкала — по статусу заказа (arctik_order_step(), inc/order.php), подсветка шагов — js/modules/order-track.js.
 * Вместо дат — сообщение (arctik_order_message()): готов к получению в пункте выдачи, получен, не оплачен / отменён / возвращён;
 * у трёх последних шкалы нет. Плановая дата — поле заказа (пусто — только дата заказа).
 * «Отследить заказ» — ссылка из поля заказа, когда заказ передан в доставку; иначе неактивна с подсказкой.
 */

defined( 'ABSPATH' ) || exit;

$arctik_order = $args['order'] ?? null;
if ( ! $arctik_order instanceof WC_Order ) {
	return;
}

$arctik_step     = arctik_order_step( $arctik_order );
$arctik_delivery = arctik_order_delivery( $arctik_order );
$arctik_message  = arctik_order_message( $arctik_order, $arctik_delivery['slot'] );
$arctik_planned  = arctik_order_delivery_date( $arctik_order );
$arctik_created  = $arctik_order->get_date_created();
$arctik_tracking = $arctik_step >= 2 ? (string) $arctik_order->get_meta( '_arctik_tracking_url' ) : '';
$arctik_items    = $arctik_order->get_items();
$arctik_count    = arctik_checkout_count_text( $arctik_order->get_item_count() );
$arctik_discount = (float) $arctik_order->get_total_discount( false );
$arctik_shipping = (float) $arctik_order->get_shipping_total() + (float) $arctik_order->get_shipping_tax();

$arctik_last_name = $arctik_order->get_billing_last_name();
$arctik_recipient = trim( $arctik_order->get_billing_first_name() . ( $arctik_last_name ? ' ' . mb_substr( $arctik_last_name, 0, 1 ) . '.' : '' ) );
$arctik_contacts  = implode( ' • ', array_filter( [ $arctik_order->get_billing_phone(), $arctik_order->get_billing_email() ] ) );

$arctik_steps = [
	[ "В\u{00A0}сборке", "Заказ собирается и\u{00A0}упаковывается для\u{00A0}отправки" ],
	[ "Передается в\u{00A0}доставку", "Заказ передаётся в\u{00A0}службу доставки" ],
	[ "В\u{00A0}пути", "Заказ находится в\u{00A0}пути к\u{00A0}пункту выдачи или\u{00A0}адресу получения" ],
	[ "Готов к\u{00A0}получению", "Заказ прибыл и\u{00A0}доступен для\u{00A0}получения" ],
	[ 'Получен', 'Заказ успешно получен' ],
];
?>
<?php /*
	Статус заказа (макет «Статус заказа» в секции 18126:59053: в работе — 18281:47397, получен — 18282:57850;
	планшет 18281:57154 / 18282:57907, мобилка 18264:89798 / 18282:57964). Сетка карточки — как у оформления (.checkout__card).
*/ ?>
<section class="checkout-wrap order-status" data-header-theme="dark">
	<?php get_template_part( 'template-parts/account/nav', null, [ 'mod' => 'account-nav--mobile', 'active' => 'orders' ] ); ?>

	<div class="checkout__card" data-header-theme="light">
		<div class="checkout__main order-status__main">
			<div class="order-status__head">
				<a class="order-status__back" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10.7984 12.0039L15.3984 16.6039L13.9984 18.0039L7.99844 12.0039L13.9984 6.00391L15.3984 7.40391L10.7984 12.0039Z"/></svg>
					<span>К&nbsp;списку заказов</span>
				</a>
				<h1 class="order-status__title">Заказ <span class="order-status__number">#<?php echo esc_html( $arctik_order->get_order_number() ); ?></span></h1>
			</div>

			<div class="order-status__progress">
				<?php if ( $arctik_message ) : ?>
					<p class="order-status__message<?php echo $arctik_message[1] ? ' order-status__message--' . esc_attr( $arctik_message[1] ) : ''; ?>"><?php echo esc_html( $arctik_message[0] ); ?></p>
				<?php elseif ( $arctik_created ) : ?>
					<div class="order-status__dates">
						<p class="order-status__date">
							<span class="order-status__date-value"><?php echo esc_html( arctik_short_date( $arctik_created->getTimestamp() ) ); ?></span>
							<span class="order-status__date-label">Дата заказа</span>
						</p>
						<?php if ( $arctik_planned ) : ?>
							<img class="order-status__arrow" src="<?php echo esc_url( arctik_asset( 'icons/icn-arrow-long.svg' ) ); ?>" alt="" width="72" height="24">
							<p class="order-status__date order-status__date--planned">
								<span class="order-status__date-value"><?php echo esc_html( arctik_short_date( $arctik_planned ) ); ?></span>
								<span class="order-status__date-label">Плановая дата доставки</span>
							</p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $arctik_step ) : ?>
					<ol class="order-track" data-step="<?php echo esc_attr( $arctik_step ); ?>" data-js-order-track>
						<?php foreach ( $arctik_steps as [ $arctik_step_title, $arctik_step_desc ] ) : ?>
							<li class="order-track__step" data-js-order-track-step>
								<span class="order-track__dot" aria-hidden="true"></span>
								<span class="order-track__text">
									<span class="order-track__title"><?php echo esc_html( $arctik_step_title ); ?></span>
									<span class="order-track__desc"><?php echo esc_html( $arctik_step_desc ); ?></span>
								</span>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
			</div>

			<section class="order-status__section" aria-labelledby="order-recipient-title">
				<h2 class="order-status__subtitle" id="order-recipient-title">Получатель</h2>
				<div class="order-status__recipient">
					<img src="<?php echo esc_url( arctik_asset( 'icons/icn-recipient.svg' ) ); ?>" alt="" width="28" height="24">
					<div class="order-status__recipient-text">
						<p><span class="order-status__muted">Получатель:</span> <strong class="order-status__name"><?php echo esc_html( $arctik_recipient ); ?></strong></p>
						<?php if ( $arctik_contacts ) : ?>
							<p class="order-status__muted"><?php echo esc_html( $arctik_contacts ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</section>

			<?php if ( $arctik_delivery['title'] ) : ?>
				<section class="order-status__section order-status__section--delivery" aria-labelledby="order-delivery-title">
					<h2 class="order-status__subtitle" id="order-delivery-title">Доставка</h2>
					<div class="order-status__delivery">
						<div class="order-status__point">
							<img class="order-status__point-logo" src="<?php echo esc_url( arctik_asset( $arctik_delivery['logo'] ) ); ?>" alt="" width="24" height="24">
							<div class="order-status__point-text">
								<p class="order-status__point-title"><?php echo arctik_text( $arctik_delivery['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
								<?php if ( $arctik_delivery['address'] ) : ?>
									<p><?php echo esc_html( $arctik_delivery['address'] ); ?></p>
								<?php endif; ?>
								<?php if ( $arctik_delivery['note'] ) : ?>
									<p class="order-status__point-note"><?php echo esc_html( $arctik_delivery['note'] ); ?></p>
								<?php endif; ?>
							</div>
						</div>
						<?php if ( $arctik_step ) : ?>
							<div class="order-status__track">
								<?php if ( $arctik_tracking ) : ?>
									<a class="btn order-status__track-btn order-status__track-btn--active" href="<?php echo esc_url( $arctik_tracking ); ?>" target="_blank" rel="noopener" data-js-swipe>Отследить заказ</a>
								<?php else : ?>
									<?php // Неактивна, пока заказ не передан в доставку (или нет ссылки); подсказка — при наведении и фокусе ?>
									<button class="btn order-status__track-btn" type="button" aria-disabled="true" aria-describedby="order-track-tip">
										Отследить заказ
										<img src="<?php echo esc_url( arctik_asset( 'icons/icn-exclamation.svg' ) ); ?>" alt="" width="16" height="16">
									</button>
									<span class="order-status__tooltip" id="order-track-tip" role="tooltip">Отслеживание появится после передачи заказа в&nbsp;доставку</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</section>
			<?php endif; ?>
		</div>

		<aside class="checkout__aside order-status__aside" aria-label="Ваш заказ">
			<div class="checkout__order">
				<h2 class="checkout__order-title">Ваш заказ</h2>
				<button class="checkout__toggle" type="button" aria-expanded="true" aria-controls="order-items" data-js-order-toggle>
					<span class="checkout__toggle-label"><?php echo esc_html( $arctik_count ); ?></span>
					<svg class="checkout__toggle-chevron" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.74935 10.7507L13.5827 6.91732L14.7493 8.08398L9.74935 13.084L4.74935 8.08398L5.91602 6.91732L9.74935 10.7507Z"/></svg>
				</button>
				<ul class="checkout__order-list" id="order-items">
					<?php
					foreach ( $arctik_items as $arctik_item ) {
						if ( ! $arctik_item instanceof WC_Order_Item_Product ) {
							continue;
						}
						get_template_part( 'template-parts/checkout/order-item', null, [
							'product' => $arctik_item->get_product() ?: null,
							'name'    => $arctik_item->get_name(),
							'qty'     => $arctik_item->get_quantity(),
							'price'   => $arctik_order->get_formatted_line_subtotal( $arctik_item ),
						] );
					}
					?>
				</ul>
			</div>

			<div class="checkout__summary">
				<div class="checkout__totals">
					<dl class="checkout__rows">
						<div class="checkout__row">
							<dt><?php echo esc_html( $arctik_count ); ?></dt>
							<dd><?php echo wp_kses_post( wc_price( (float) $arctik_order->get_subtotal() + (float) $arctik_order->get_cart_tax(), [ 'currency' => $arctik_order->get_currency() ] ) ); ?></dd>
						</div>
						<?php if ( $arctik_discount > 0 ) : ?>
							<div class="checkout__row">
								<dt>Скидка</dt>
								<dd class="checkout__discount">- <?php echo wp_kses_post( wc_price( $arctik_discount, [ 'currency' => $arctik_order->get_currency() ] ) ); ?></dd>
							</div>
						<?php endif; ?>
						<div class="checkout__row">
							<dt>Доставка</dt>
							<?php if ( $arctik_shipping > 0 ) : ?>
								<dd><?php echo wp_kses_post( wc_price( $arctik_shipping, [ 'currency' => $arctik_order->get_currency() ] ) ); ?></dd>
							<?php else : ?>
								<dd class="checkout__free">Бесплатно</dd>
							<?php endif; ?>
						</div>
					</dl>
					<div class="checkout__total">
						<p class="order-status__paid"><?php echo $arctik_order->is_paid() ? 'Оплачено' : "К\u{00A0}оплате"; // phpcs:ignore WordPress.Security.EscapeOutput ?> <span class="order-status__paid-method"><?php echo esc_html( arctik_order_paid_label( $arctik_order ) ); ?></span></p>
						<span><?php echo wp_kses_post( wc_price( (float) $arctik_order->get_total(), [ 'currency' => $arctik_order->get_currency() ] ) ); ?></span>
					</div>
				</div>
			</div>
		</aside>

		<?php get_template_part( 'template-parts/checkout/footer' ); ?>
	</div>
</section>
