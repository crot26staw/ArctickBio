<?php
/**
 * Личный кабинет (src/account-orders.html, account-orders-empty.html, account.html, account-addresses.html).
 *
 * Страница WC «Мой аккаунт» (шорткод не выводится): шаблон page-templates/account.php по эндпоинту —
 * orders («Мои заказы», он же главная кабинета), edit-account («Аккаунт»), edit-address («Мои адреса»);
 * view-order — статус заказа (inc/order.php). Гость — на главную с попапом входа, «Выйти» — сразу выход и на главную.
 * Формы — AJAX (inc/ajax/account.php, window.arctik.account).
 * Адреса — список в мете пользователя arctik_addresses (решение пользователя), основной копируется в адрес доставки WC.
 */

defined( 'ABSPATH' ) || exit;

// Статусы «Активных» заказов (решение пользователя: по шкале статусов + ожидает оплаты), остальные — «Завершенные»
const ARCTIK_ACTIVE_STATUSES = [ 'pending', 'on-hold', 'processing', 'arctik-handover', 'arctik-transit', 'arctik-ready' ];

/**
 * Раздел кабинета: orders / edit-account / edit-address, '' — не кабинет (гость, статус заказа, служебные эндпоинты).
 */
function arctik_account_section(): string {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || ! is_user_logged_in() ) {
		return '';
	}

	foreach ( [ 'edit-account', 'edit-address', 'orders' ] as $endpoint ) {
		if ( is_wc_endpoint_url( $endpoint ) ) {
			return $endpoint;
		}
	}

	return '';
}

// ---------- Переходы ----------

add_action( 'template_redirect', function () {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}

	// Сброс пароля по ссылке из письма — inc/auth.php (приоритет 5), выход — обработчик WC
	if ( is_wc_endpoint_url( 'lost-password' ) || is_wc_endpoint_url( 'customer-logout' ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/#auth-login' ) );
		exit;
	}

	// Главная кабинета и разделы, которых в вёрстке нет (загрузки, способы оплаты…), — «Мои заказы»
	if ( ! arctik_account_section() && ! is_wc_endpoint_url( 'view-order' ) ) {
		wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
		exit;
	}
}, 20 );

// «Выйти» без страницы подтверждения WC: ссылка с nonce (wc_logout_url()), после выхода — главная
add_filter( 'woocommerce_logout_default_redirect_url', fn() => home_url( '/' ) );

add_filter( 'template_include', function ( string $template ): string {
	return arctik_account_section() ? get_theme_file_path( 'page-templates/account.php' ) : $template;
}, 20 );

add_filter( 'body_class', function ( array $classes ): array {
	if ( arctik_account_section() ) {
		array_push( $classes, 'page--checkout', 'page--account' );
	}

	return $classes;
} );

// <title>: «Мои заказы» / «Аккаунт» / «Мои адреса» вместо «Мой аккаунт» (заголовок задаёт Yoast SEO — inc/seo.php)
add_filter( 'wpseo_title', function ( string $title ): string {
	$titles = [
		'orders'       => 'Мои заказы',
		'edit-account' => 'Аккаунт',
		'edit-address' => 'Мои адреса',
	];
	$section = arctik_account_section();

	return $section ? $titles[ $section ] . ' — ' . get_bloginfo( 'name' ) : $title;
} );

// ---------- Заказы ----------

/**
 * Заказы текущего покупателя, новые первыми: [ 'active' => WC_Order[], 'done' => WC_Order[] ].
 */
function arctik_account_orders(): array {
	$orders = wc_get_orders( [
		'customer_id' => get_current_user_id(),
		'limit'       => -1,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'status'      => array_keys( wc_get_order_statuses() ),
	] );
	$result = [ 'active' => [], 'done' => [] ];

	foreach ( $orders as $order ) {
		if ( $order instanceof WC_Order && 'checkout-draft' !== $order->get_status() ) {
			$result[ in_array( $order->get_status(), ARCTIK_ACTIVE_STATUSES, true ) ? 'active' : 'done' ][] = $order;
		}
	}

	return $result;
}

/**
 * Статус в карточке заказа — как шаг шкалы на странице статуса («В сборке» … «Получен»), вне шкалы — свои подписи.
 */
function arctik_order_card_status( WC_Order $order ): string {
	$steps = [ 1 => "В\u{00A0}сборке", "Передается в\u{00A0}доставку", "В\u{00A0}пути", "Готов к\u{00A0}получению", 'Получен' ];
	$step  = arctik_order_step( $order );

	if ( $step ) {
		return $steps[ $step ];
	}

	return match ( $order->get_status() ) {
		'cancelled' => 'Отменён',
		'refunded'  => 'Возвращён',
		default     => "Не\u{00A0}оплачен",
	};
}

// ---------- Профиль ----------

/**
 * Имя в «Аккаунте»: имя и фамилия, иначе отображаемое имя.
 */
function arctik_account_name( WP_User $user ): string {
	return trim( $user->first_name . ' ' . $user->last_name ) ?: $user->display_name;
}

// ---------- Адреса ----------

const ARCTIK_ADDRESS_FIELDS = [ 'name', 'phone', 'address_1', 'address_2', 'country', 'city', 'postcode' ];

/**
 * Адреса пользователя: [ id => [ name, phone, address_1, address_2, country, city, postcode ] ] в порядке добавления.
 */
function arctik_addresses( int $user_id = 0 ): array {
	$addresses = get_user_meta( $user_id ?: get_current_user_id(), 'arctik_addresses', true );

	return is_array( $addresses ) ? $addresses : [];
}

function arctik_default_address_id( int $user_id = 0 ): string {
	$addresses = arctik_addresses( $user_id );
	$id        = (string) get_user_meta( $user_id ?: get_current_user_id(), 'arctik_address_default', true );

	return isset( $addresses[ $id ] ) ? $id : (string) array_key_first( $addresses );
}

/**
 * Адрес строкой: «140016, Люберцы, Некрасовский проезд, 8» (как в статике, account.js); без индекса — для курьера на оформлении.
 */
function arctik_address_line( array $address, bool $postcode = true ): string {
	$parts = [ $postcode ? $address['postcode'] ?? '' : '', $address['city'] ?? '', $address['address_1'] ?? '', $address['address_2'] ?? '' ];

	return implode( ', ', array_filter( array_map( 'trim', $parts ) ) );
}

/**
 * Сохраняет список и основной адрес; основной копируется в адрес доставки WC (пусто — адрес WC очищается).
 */
function arctik_save_addresses( int $user_id, array $addresses, string $default_id ): void {
	if ( ! isset( $addresses[ $default_id ] ) ) {
		$default_id = (string) array_key_first( $addresses );
	}

	update_user_meta( $user_id, 'arctik_addresses', $addresses );
	update_user_meta( $user_id, 'arctik_address_default', $default_id );

	$address  = $addresses[ $default_id ] ?? [];
	$name     = preg_split( '/\s+/u', trim( $address['name'] ?? '' ), 2 );
	$customer = new WC_Customer( $user_id );

	$customer->set_shipping_first_name( $name[0] ?? '' );
	$customer->set_shipping_last_name( $name[1] ?? '' );
	$customer->set_shipping_phone( $address['phone'] ?? '' );
	$customer->set_shipping_address_1( $address['address_1'] ?? '' );
	$customer->set_shipping_address_2( $address['address_2'] ?? '' );
	$customer->set_shipping_city( $address['city'] ?? '' );
	$customer->set_shipping_postcode( $address['postcode'] ?? '' );
	$customer->set_shipping_country( 'RU' );
	$customer->save();
}

/**
 * Карточки адресов (li) для списка «Мои адреса»: в шаблоне и в ответе AJAX.
 */
function arctik_address_cards(): void {
	$default = arctik_default_address_id();

	foreach ( arctik_addresses() as $id => $address ) {
		get_template_part( 'template-parts/account/address-card', null, [
			'id'      => $id,
			'address' => $address,
			'checked' => (string) $id === $default,
		] );
	}
}

// Курьер на оформлении: адрес — основной из «Моих адресов» одной строкой (город, улица, квартира)
add_filter( 'woocommerce_checkout_get_value', function ( $value, string $input ) {
	if ( 'shipping_address_1' !== $input || ! is_user_logged_in() || ( isset( $_POST[ $input ] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return $value;
	}

	$address = arctik_addresses()[ arctik_default_address_id() ] ?? null;

	return $address ? arctik_address_line( $address, false ) : $value;
}, 10, 2 );

add_filter( 'arctik_js_data', function ( array $data ): array {
	if ( arctik_account_section() ) {
		$data['account'] = [
			'endpoint' => WC_AJAX::get_endpoint( '%%endpoint%%' ),
			'nonce'    => wp_create_nonce( 'arctik-account' ),
		];
	}

	return $data;
} );
