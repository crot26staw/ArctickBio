<?php
/**
 * Форма оформления заказа (src/partials/checkout/main.html) вместо шаблона WC. Логика — js/modules/checkout.js, сервер — inc/checkout.php.
 *
 * $error — ошибка (HTML) под кнопкой оплаты при выводе страницы (разобранные товары; передаёт woocommerce/checkout/cart-errors.php).
 * Способы оплаты и доставки — доступные в WC (inc/payment.php, inc/shipping.php); блок способа доставки — по его ID (pickup / courier).
 *
 * @var WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;

$arctik_error    = (string) ( $error ?? arctik_checkout_sold_message() );
$arctik_cart     = WC()->cart;
$arctik_count    = arctik_checkout_count_text( $arctik_cart->get_cart_contents_count() );
$arctik_terms_id = wc_terms_and_conditions_page_id();

// Оферта — страница условий WC; если это «Документы» — сразу к оферте (inc/documents.php).
$arctik_terms_url = $arctik_terms_id ? get_permalink( $arctik_terms_id ) : '#';
if ( $arctik_terms_id && 'page-templates/documents.php' === get_page_template_slug( $arctik_terms_id ) ) {
	$arctik_terms_url .= '#oferta';
}

// Способы оплаты: выбран сохранённый в сессии или первый (в статике — СБП).
$arctik_gateways = WC()->payment_gateways()->get_available_payment_gateways();
$arctik_gateway  = WC()->session->get( 'chosen_payment_method' );
if ( ! isset( $arctik_gateways[ $arctik_gateway ] ) ) {
	$arctik_gateway = (string) array_key_first( $arctik_gateways );
}

// Способы доставки (тариф СДЭК до города покупателя): выбран сохранённый в сессии или первый (в статике — пункт выдачи).
$arctik_rates     = arctik_shipping_rates();
$arctik_rate      = arctik_checkout_chosen_rate( $arctik_rates );
$arctik_slot      = arctik_shipping_slot( $arctik_rate );
$arctik_courier   = current( array_filter( $arctik_rates, fn( $rate ) => 'courier' === $rate['slot'] ) );
$arctik_address   = (string) $checkout->get_value( 'shipping_address_1' );
$arctik_first     = $arctik_cart->get_cart()[ array_key_first( $arctik_cart->get_cart() ) ]['data'] ?? null;
$arctik_first_img = $arctik_first instanceof WC_Product ? arctik_product_image_id( wc_get_product( $arctik_first->get_parent_id() ?: $arctik_first->get_id() ), 'mini' ) : 0;

$arctik_location_icon = arctik_asset( 'icons/icn-location.svg' );
$arctik_chevron       = '<svg class="checkout-target__chevron" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.5 10L6.66667 6.16667L7.83333 5L12.8333 10L7.83333 15L6.66667 13.8333L10.5 10Z"/></svg>';
?>
<?php /*
	Оформление заказа (макет «Оформление заказа» в секции 18126:59053 копии Pky7FDIkoyGiepPQCMknnV):
	десктоп 18270:36245 (ошибки — 18649:81751, выбран ПВЗ — 18271:39375, курьер — 18271:40435), планшет 18281:45602,
	мобилка 18270:36258 (товары раскрыты — 18281:46200).
	Форма — разметка WooCommerce (form.checkout, поля billing_*, payment_method, shipping_method, terms); логика — js/modules/checkout.js.
	Пункт выдачи выбирается в попапе (template-parts/checkout/pickup-modal.php); адрес курьера (address-modal.php) — только если есть способ «Курьер».
*/ ?>
<section class="checkout-wrap" data-header-theme="dark">
	<form class="checkout__card checkout woocommerce-checkout" name="checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" method="post" novalidate data-js-checkout>
		<div class="checkout__main">
			<div class="checkout__head">
				<a class="checkout__back" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" aria-label="Назад">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10.7984 12.0039L15.3984 16.6039L13.9984 18.0039L7.99844 12.0039L13.9984 6.00391L15.3984 7.40391L10.7984 12.0039Z"/></svg>
				</a>
				<h1 class="checkout__title"><?php echo arctik_text( get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
			</div>

			<fieldset class="checkout__section woocommerce-billing-fields">
				<legend class="checkout__subtitle">Контактные данные</legend>
				<div class="checkout__fields">
					<?php
					foreach ( [
						[ 'billing_phone', 'tel', 'Телефон', 'tel', 'phone' ],
						[ 'billing_email', 'email', 'Email', 'email', 'email' ],
						[ 'billing_first_name', 'text', 'Имя', 'given-name', 'required' ],
						[ 'billing_last_name', 'text', 'Фамилия', 'family-name', 'required' ],
					] as [ $arctik_name, $arctik_type, $arctik_label, $arctik_autocomplete, $arctik_validate ] ) {
						get_template_part( 'template-parts/form-field', null, [
							'id'           => $arctik_name,
							'type'         => $arctik_type,
							'name'         => $arctik_name,
							'label'        => $arctik_label,
							'autocomplete' => $arctik_autocomplete,
							'mod'          => 'field--light',
							'value'        => $checkout->get_value( $arctik_name ),
							'attrs'        => 'data-validate="' . $arctik_validate . '" data-message="Обязательное поле"',
						] );
					}
					?>
				</div>
			</fieldset>

			<fieldset class="checkout__section wc_payment_methods">
				<legend class="checkout__subtitle">Способ оплаты</legend>
				<div class="checkout__options">
					<?php foreach ( $arctik_gateways as $arctik_id => $arctik_gw ) : ?>
						<?php [ $arctik_icon, $arctik_w, $arctik_h, $arctik_mod ] = arctik_payment_icon( $arctik_id ); ?>
						<label class="checkout-pay">
							<input class="checkout-pay__input input-radio" type="radio" name="payment_method" value="<?php echo esc_attr( $arctik_id ); ?>" <?php checked( $arctik_id, $arctik_gateway ); ?>>
							<img class="checkout-pay__icon <?php echo esc_attr( $arctik_mod ); ?>" src="<?php echo esc_url( arctik_asset( $arctik_icon ) ); ?>" alt="" width="<?php echo esc_attr( $arctik_w ); ?>" height="<?php echo esc_attr( $arctik_h ); ?>">
							<span class="checkout-pay__label"><?php echo arctik_text( $arctik_gw->get_title() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						</label>
					<?php endforeach; ?>
				</div>
				<?php get_template_part( 'template-parts/pay-logos', null, [ 'mod' => 'checkout__pay-logos' ] ); ?>
			</fieldset>

			<fieldset class="checkout__section checkout__section--delivery woocommerce-shipping-methods">
				<legend class="checkout__subtitle">Способ получения</legend>
				<?php get_template_part( 'template-parts/checkout/rates', null, [ 'rates' => $arctik_rates, 'chosen' => $arctik_rate ] ); ?>

				<?php /* Пункт выдачи: пока не выбран — кнопка «Пункт выдачи», после выбора — служба и адрес (макет 18271:39375) */ ?>
				<div class="checkout__delivery" <?php echo 'pickup' === $arctik_slot ? '' : 'hidden'; ?> data-js-checkout-panel="pickup">
					<button class="checkout-target" type="button" aria-haspopup="dialog" aria-controls="pickup" data-js-modal-open="pickup">
						<span class="checkout-target__icon" data-js-checkout-pickup-icon>
							<img src="<?php echo esc_url( $arctik_location_icon ); ?>" alt="" width="20" height="20">
						</span>
						<span class="checkout-target__body">
							<span class="checkout-target__title"><span data-js-checkout-pickup-title>Пункт выдачи</span>
								<?php echo $arctik_chevron; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</span>
							<span class="checkout-target__text" hidden data-js-checkout-pickup-text></span>
						</span>
					</button>
					<input type="hidden" name="pickup_point" value="" data-js-checkout-pickup-value>
					<input type="hidden" name="pickup_point_address" value="" data-js-checkout-pickup-address>
					<input type="hidden" name="pickup_point_service" value="" data-js-checkout-pickup-service>
					<p class="checkout__error" hidden data-js-checkout-error="pickup">Выберите пункт выдачи</p>
				</div>

				<?php
				/*
					Курьер: адрес и дата доставки (макет 18271:40435). Адрес — из профиля покупателя, если был.
					Курьерской доставки нет (решение пользователя, 28.10.2026): способа «Курьер» нет в зоне — блок не выводится, в статике его нет.
				*/
				?>
				<?php if ( $arctik_courier ) : ?>
				<div class="checkout__delivery" <?php echo 'courier' === $arctik_slot ? '' : 'hidden'; ?> data-js-checkout-panel="courier">
					<button class="checkout-target" type="button" aria-haspopup="dialog" aria-controls="address" data-js-modal-open="address">
						<span class="checkout-target__icon">
							<img src="<?php echo esc_url( $arctik_location_icon ); ?>" alt="" width="20" height="20">
						</span>
						<span class="checkout-target__body">
							<span class="checkout-target__title">Адрес доставки
								<?php echo $arctik_chevron; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</span>
							<span class="checkout-target__text" <?php echo $arctik_address ? '' : 'hidden'; ?> data-js-checkout-address-text><?php echo esc_html( $arctik_address ); ?></span>
						</span>
					</button>
					<input type="hidden" name="shipping_address_1" value="<?php echo esc_attr( $arctik_address ); ?>" data-js-checkout-address-value>
					<p class="checkout__error" hidden data-js-checkout-error="courier">Укажите адрес доставки</p>

					<p class="checkout__dates-title">
						<img src="<?php echo esc_url( arctik_asset( 'icons/icn-package.svg' ) ); ?>" alt="" width="32" height="32">
						<span><?php echo esc_html( arctik_delivery_range() ); ?></span>
					</p>
					<div class="checkout__dates" data-js-drag-slider data-drag-slider-align="start">
						<div class="checkout__dates-track" data-js-drag-slider-track>
							<?php
							$arctik_date_price = arctik_price_text( $arctik_courier ? (float) $arctik_courier['rate']->get_cost() : 0 );
							$arctik_checked    = true;
							foreach ( arctik_delivery_dates() as $arctik_value => $arctik_day ) {
								get_template_part( 'template-parts/checkout/delivery-date', null, [
									'value'   => $arctik_value,
									'day'     => $arctik_day,
									'price'   => $arctik_date_price,
									'checked' => $arctik_checked,
								] );
								$arctik_checked = false;
							}
							?>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</fieldset>
		</div>

		<aside class="checkout__aside" aria-label="Ваш заказ">
			<div class="checkout__order">
				<h2 class="checkout__order-title">Ваш заказ</h2>
				<button class="checkout__toggle" type="button" aria-expanded="true" aria-controls="checkout-order-list" data-js-checkout-toggle>
					<span class="checkout__toggle-thumb">
						<?php echo arctik_image( $arctik_first_img, 'woocommerce_thumbnail', [ 'alt' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</span>
					<span class="checkout__toggle-text">
						<span class="checkout__toggle-label">Оформляем <?php echo esc_html( $arctik_count ); ?></span>
						<span class="checkout__toggle-mobile">Итого</span>
						<span class="checkout__toggle-count"><?php echo esc_html( $arctik_count ); ?></span>
					</span>
					<span class="checkout__toggle-price" data-js-checkout-total><?php echo esc_html( arctik_checkout_total() ); ?></span>
					<svg class="checkout__toggle-chevron" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.74935 10.7507L13.5827 6.91732L14.7493 8.08398L9.74935 13.084L4.74935 8.08398L5.91602 6.91732L9.74935 10.7507Z"/></svg>
				</button>
				<ul class="checkout__order-list woocommerce-checkout-review-order-table" id="checkout-order-list" data-js-checkout-list>
					<?php
					foreach ( $arctik_cart->get_cart() as $arctik_item ) {
						get_template_part( 'template-parts/checkout/order-item', null, [
							'product' => $arctik_item['data'],
							'qty'     => (int) $arctik_item['quantity'],
							'price'   => $arctik_cart->get_product_subtotal( $arctik_item['data'], $arctik_item['quantity'] ),
						] );
					}
					?>
				</ul>
			</div>

			<?php get_template_part( 'template-parts/checkout/summary', null, [ 'error' => $arctik_error ] ); ?>

			<?php
			get_template_part( 'template-parts/promo', null, [
				'id'    => 'coupon_code',
				'mod'   => 'checkout__promo checkout_coupon',
				'value' => arctik_cart_coupon(),
			] );
			?>

			<label class="checkbox checkout__terms woocommerce-form__label-for-checkbox" data-js-checkout-terms>
				<input class="checkbox__input input-checkbox" type="checkbox" name="terms" id="terms">
				<span class="checkbox__box" aria-hidden="true"></span>
				<span class="checkbox__label">Я&nbsp;принимаю <a href="<?php echo esc_url( $arctik_terms_url ); ?>" target="_blank" rel="noopener" data-js-roll>Публичную оферту</a> и&nbsp;<a href="<?php echo esc_url( get_privacy_policy_url() ?: '#' ); ?>" target="_blank" rel="noopener" data-js-roll>Политику обработки персональных данных</a></span>
			</label>
			<?php if ( $arctik_terms_id ) : ?>
				<input type="hidden" name="terms-field" value="1">
			<?php endif; ?>
		</aside>

		<?php get_template_part( 'template-parts/checkout/footer' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</form>
</section>
