<?php
/**
 * Оформление заказа (src/checkout.html): страница WC «Оформление заказа» с шорткодом [woocommerce_checkout],
 * свой шаблон страницы (page-templates/checkout.php — шапка и фон без подвала) и формы (woocommerce/checkout/form-checkout.php).
 *
 * Отправка — checkout.js в ?wc-ajax=checkout (jQuery-скрипт WC оформления отключён). Поля — телефон, email, имя, фамилия;
 * страна — всегда Россия. Пункт выдачи / адрес курьера / дата — свои поля формы, сохраняются в заказ (видны в админке).
 * Аккаунт: гостю создаётся по email (письмо WC со ссылкой на пароль); email уже есть — заказ привязывается к этому аккаунту.
 * Разобранные товары — оформить нельзя, ошибка с названиями (arctik_checkout_sold_message()).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Страница оформления (без «Заказ получен» — inc/order.php — и оплаты заказа, у неё стандартный вид WC).
 */
function arctik_is_checkout_form(): bool {
	return function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) && ! is_wc_endpoint_url( 'order-pay' );
}

add_filter( 'template_include', function ( string $template ): string {
	return arctik_is_checkout_form() ? get_theme_file_path( 'page-templates/checkout.php' ) : $template;
} );

// Корзины-страницы в вёрстке нет (корзина — попап): пустая корзина на оформлении и /cart/ ведут в каталог.
add_filter( 'woocommerce_get_cart_url', fn() => wc_get_page_permalink( 'shop' ) );

add_action( 'template_redirect', function () {
	if ( is_cart() ) {
		wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
		exit;
	}
} );

// Скрипты WC оформления не нужны — форма своя (checkout.js).
add_action( 'wp_enqueue_scripts', function () {
	foreach ( [ 'wc-checkout', 'wc-country-select', 'wc-address-i18n', 'selectWoo', 'select2' ] as $handle ) {
		wp_dequeue_script( $handle );
	}
	wp_dequeue_style( 'select2' );
}, 100 );

add_filter( 'arctik_js_data', function ( array $data ): array {
	if ( arctik_is_checkout_form() ) {
		$data['checkout'] = [ 'endpoint' => WC_AJAX::get_endpoint( 'checkout' ) ];
		// Попап пункта выдачи (pickup.js): запросы — через cart-api.js, здесь только ключ карт
		$data['pickup'] = [ 'mapKey' => (string) arctik_option( 'arctik_yandex_maps_key' ) ];
	}

	return $data;
} );

// ---------- Поля ----------

add_filter( 'woocommerce_checkout_fields', function ( array $fields ): array {
	$fields['billing']  = array_intersect_key( $fields['billing'], array_flip( [ 'billing_phone', 'billing_email', 'billing_first_name', 'billing_last_name' ] ) );
	$fields['shipping'] = [];
	$fields['order']    = [];
	$fields['account']  = [];

	$fields['billing']['billing_phone']['required'] = true;

	return $fields;
} );

add_filter( 'woocommerce_checkout_required_field_notice', fn() => 'Обязательное поле' );

add_filter( 'woocommerce_checkout_posted_data', function ( array $data ): array {
	$data['billing_country']  = 'RU';
	$data['shipping_country'] = 'RU';

	return $data;
} );

/**
 * ID выбранной ставки доставки: из сессии WC, если она есть среди ставок, иначе первая ('' — ставок нет).
 */
function arctik_checkout_chosen_rate( array $rates ): string {
	$ids    = array_map( fn( $rate ) => $rate['rate']->get_id(), $rates );
	$chosen = WC()->session->get( 'chosen_shipping_methods' )[0] ?? '';

	return in_array( $chosen, $ids, true ) ? $chosen : (string) ( $ids[0] ?? '' );
}

/**
 * Способ получения из формы: pickup / courier / ''.
 */
function arctik_checkout_slot( array $data ): string {
	return arctik_shipping_slot( (string) ( $data['shipping_method'][0] ?? '' ) );
}

add_action( 'woocommerce_after_checkout_validation', function ( array $data, WP_Error $errors ) {
	// Тексты проверок полей WC («Выставление счёта Телефон — неверный…») — как в field.js
	foreach ( [
		'billing_phone_validation' => "Введите телефон в\u{00A0}формате +7\u{00A0}900\u{00A0}000-00-00",
		'billing_email_validation' => "Введите email в\u{00A0}формате name@example.com",
	] as $code => $message ) {
		if ( $errors->get_error_message( $code ) ) {
			$error_data = $errors->get_error_data( $code );
			$errors->remove( $code );
			$errors->add( $code, $message, $error_data );
		}
	}

	$slot = arctik_checkout_slot( $data );

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce проверил WC_Checkout::process_checkout()
	// Пункт — из пунктов СДЭК города покупателя (город могли сменить после выбора пункта)
	if ( 'pickup' === $slot && ! arctik_cdek_point( arctik_city()['code'], sanitize_text_field( wp_unslash( $_POST['pickup_point'] ?? '' ) ) ) ) {
		$errors->add( 'shipping', 'Выберите пункт выдачи' );
	}
	if ( 'courier' === $slot && '' === trim( (string) ( $_POST['shipping_address_1'] ?? '' ) ) ) {
		$errors->add( 'shipping', 'Укажите адрес доставки' );
	}
	if ( 'courier' === $slot && ! isset( arctik_delivery_dates()[ $_POST['delivery_date'] ?? '' ] ) ) {
		$errors->add( 'shipping', 'Выберите дату доставки' );
	}
	// phpcs:enable
}, 10, 2 );

// Пункт выдачи, адрес и дата — в заказ: адрес доставки заказа + свои поля (в админке под адресом доставки — inc/order.php).
add_action( 'woocommerce_checkout_create_order', function ( WC_Order $order, array $data ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$slot = arctik_checkout_slot( $data );

	// Город СДЭК — для передачи заказа в СДЭК (Оф2б)
	$city = arctik_city();
	$order->update_meta_data( '_arctik_cdek_city', $city['code'] );
	$order->set_shipping_city( $city['name'] );

	if ( 'pickup' === $slot ) {
		// Адрес — из справочника СДЭК (проверен в woocommerce_after_checkout_validation), не из формы
		$point   = arctik_cdek_point( $city['code'], sanitize_text_field( wp_unslash( $_POST['pickup_point'] ?? '' ) ) );
		$address = 'СДЭК, ' . $city['name'] . ', ' . $point['address'];
		$order->update_meta_data( '_arctik_pickup_point', $point['id'] );
		$order->update_meta_data( '_arctik_pickup_address', $address );
		$order->update_meta_data( '_arctik_pickup_service', 'cdek' );
		$order->set_shipping_address_1( $address );
	}

	if ( 'courier' === $slot ) {
		$order->set_shipping_address_1( sanitize_text_field( wp_unslash( $_POST['shipping_address_1'] ?? '' ) ) );
		$order->update_meta_data( '_arctik_delivery_date', sanitize_text_field( wp_unslash( $_POST['delivery_date'] ?? '' ) ) );
	}
	// phpcs:enable

	$order->set_shipping_first_name( $order->get_billing_first_name() );
	$order->set_shipping_last_name( $order->get_billing_last_name() );
	$order->set_shipping_phone( $order->get_billing_phone() );
}, 10, 2 );

// ---------- Аккаунт ----------

/**
 * Email гостя из отправленной формы, если по нему уже есть аккаунт: ID пользователя или 0.
 */
function arctik_checkout_existing_customer(): int {
	if ( is_user_logged_in() ) {
		return 0;
	}

	$email = sanitize_email( wp_unslash( $_POST['billing_email'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	return $email ? (int) email_exists( $email ) : 0;
}

// Гостю аккаунт создаётся всегда (пароль — по ссылке из письма WC), кроме случая, когда email уже зарегистрирован.
add_filter( 'woocommerce_checkout_registration_required', fn() => ! is_user_logged_in() && ! arctik_checkout_existing_customer() );

// Email уже зарегистрирован — заказ привязывается к этому аккаунту (решение пользователя), но данные профиля гость не меняет.
add_filter( 'woocommerce_checkout_customer_id', fn( $customer_id ) => $customer_id ?: arctik_checkout_existing_customer() );
add_filter( 'woocommerce_checkout_update_customer_data', fn( $update ) => $update && is_user_logged_in() );

// После автосоздания аккаунта WC просит перезагрузить оформление — заказ уже создан, не нужно.
add_action( 'woocommerce_checkout_order_processed', function () {
	WC()->session->__unset( 'reload_checkout' );
} );

// ---------- Разобранные товары ----------

/**
 * Текст ошибки, если в корзине есть разобранные товары (нет в наличии или не хватает остатка), иначе ''.
 */
function arctik_checkout_sold_message(): string {
	$names = [];

	foreach ( WC()->cart->get_cart() as $item ) {
		if ( arctik_cart_item_is_sold( $item ) && $item['data'] instanceof WC_Product ) {
			$names[] = '«' . $item['data']->get_title() . '»';
		}
	}

	$names = array_unique( $names );
	if ( ! $names ) {
		return '';
	}

	return 1 === count( $names )
		? sprintf( "%s нет в\u{00A0}наличии\u{00A0}— удалите товар из\u{00A0}корзины", $names[0] )
		: sprintf( "%s нет в\u{00A0}наличии\u{00A0}— удалите эти товары из\u{00A0}корзины", implode( ', ', $names ) );
}

// Вместо проверки остатков WC (её текст — про «измените корзину») — своя ошибка; WC проверяет корзину и при отправке заказа.
add_action( 'woocommerce_check_cart_items', function () {
	$message = arctik_checkout_sold_message();

	if ( $message ) {
		remove_action( 'woocommerce_check_cart_items', [ WC()->cart, 'check_cart_items' ], 1 );
		wc_add_notice( $message, 'error' );
	}
}, 0 );

// ---------- Итоги ----------

/**
 * «2 товара» — число товаров в заказе.
 */
function arctik_checkout_count_text( int $count ): string {
	$mod10  = $count % 10;
	$mod100 = $count % 100;
	$word   = 1 === $mod10 && 11 !== $mod100 ? 'товар' : ( $mod10 >= 2 && $mod10 <= 4 && ( $mod100 < 12 || $mod100 > 14 ) ? 'товара' : 'товаров' );

	return $count . "\u{00A0}" . $word;
}

/**
 * Итог заказа текстом («1 440 ₽») для кнопки оплаты и свёрнутого списка (сумма к оплате WC).
 */
function arctik_checkout_total(): string {
	return arctik_price_text( (float) WC()->cart->get_total( 'edit' ) );
}

// Промокод, город и способ получения на оформлении (cart-api.js) — итоги, сумма в свёрнутом списке и способы получения обновляются фрагментами, как корзина-попап.
add_filter( 'woocommerce_add_to_cart_fragments', function ( array $fragments ): array {
	ob_start();
	get_template_part( 'template-parts/checkout/summary' );
	$fragments['[data-js-checkout-summary]'] = (string) ob_get_clean();
	$fragments['[data-js-checkout-total]']   = '<span class="checkout__toggle-price" data-js-checkout-total>' . esc_html( arctik_checkout_total() ) . '</span>';

	// Способы получения — стоимость и срок зависят от города (inc/ajax/delivery.php)
	ob_start();
	get_template_part( 'template-parts/checkout/rates' );
	$fragments['[data-js-checkout-rates]'] = (string) ob_get_clean();

	return $fragments;
}, 20 );
