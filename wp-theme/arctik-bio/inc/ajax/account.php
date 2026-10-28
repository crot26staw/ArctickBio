<?php
/**
 * Личный кабинет: свои wc-ajax обработчики (?wc-ajax=arctik_…), вызывает account.js (window.arctik.account — inc/account.php).
 * Поля — как в формах кабинета (template-parts/account/{profile,addresses}.php). Ошибка — { errors: { имя поля: текст } }.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Проверка nonce и входа; иначе — ошибка у поля $field.
 */
function arctik_account_check( string $field ): WP_User {
	if ( ! is_user_logged_in() || ! check_ajax_referer( 'arctik-account', 'security', false ) ) {
		wp_send_json_error( [ 'errors' => [ $field => "Сессия устарела, обновите страницу" ] ], 403 );
	}

	return wp_get_current_user();
}

/**
 * Ответ со списком адресов (разметка карточек).
 */
function arctik_account_addresses_response(): void {
	ob_start();
	arctik_address_cards();

	wp_send_json_success( [ 'html' => ob_get_clean() ] );
}

// «Изменить информацию об аккаунте»: account_display_name (имя и фамилия через пробел), account_email.
add_action( 'wc_ajax_arctik_account_details', function () {
	$user  = arctik_account_check( 'account_display_name' );
	$name  = arctik_auth_post( 'account_display_name' );
	$email = sanitize_email( wp_unslash( $_POST['account_email'] ?? '' ) );

	$errors = [];
	if ( '' === $name ) {
		$errors['account_display_name'] = 'Обязательное поле';
	}
	if ( ! is_email( $email ) ) {
		$errors['account_email'] = "Введите email в\u{00A0}формате name@example.com";
	} elseif ( ( $owner = email_exists( $email ) ) && $owner !== $user->ID ) {
		$errors['account_email'] = "Этот email уже занят другим аккаунтом";
	}
	if ( $errors ) {
		wp_send_json_error( [ 'errors' => $errors ] );
	}

	[ $first, $last ] = array_pad( preg_split( '/\s+/u', $name, 2 ), 2, '' );
	$result = wp_update_user( [
		'ID'           => $user->ID,
		'first_name'   => $first,
		'last_name'    => $last,
		'display_name' => $name,
		'user_email'   => $email,
	] );
	if ( is_wp_error( $result ) ) {
		wp_send_json_error( [ 'errors' => [ 'account_email' => "Не\u{00A0}удалось сохранить, попробуйте ещё раз" ] ] );
	}

	// Email оформления заказа — вслед за email аккаунта, если совпадал
	$customer = new WC_Customer( $user->ID );
	if ( ! $customer->get_billing_email() || $customer->get_billing_email() === $user->user_email ) {
		$customer->set_billing_email( $email );
		$customer->save();
	}

	wp_send_json_success( [ 'name' => $name ] );
} );

// «Изменить пароль»: password_current, password_1, password_2. После смены — новый cookie входа, nonce меняется вместе с сессией.
add_action( 'wc_ajax_arctik_account_password', function () {
	$user     = arctik_account_check( 'password_current' );
	$current  = arctik_auth_password( 'password_current' );
	$password = arctik_auth_password( 'password_1' );

	$errors = [];
	if ( ! wp_check_password( $current, $user->user_pass, $user->ID ) ) {
		$errors['password_current'] = 'Неверный пароль';
	}
	if ( '' === $password ) {
		$errors['password_1'] = 'Обязательное поле';
	} elseif ( arctik_auth_password( 'password_2' ) !== $password ) {
		$errors['password_2'] = "Пароли не\u{00A0}совпадают";
	}
	if ( $errors ) {
		wp_send_json_error( [ 'errors' => $errors ] );
	}

	// wp_update_user у текущего пользователя сам перевыпускает cookie входа; nonce считается по токену сессии —
	// новый nonce — уже по токену нового cookie
	add_action( 'set_logged_in_cookie', function ( string $cookie ) {
		$_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
	} );
	wp_update_user( [ 'ID' => $user->ID, 'user_pass' => $password ] );

	wp_send_json_success( [ 'nonce' => wp_create_nonce( 'arctik-account' ) ] );
} );

// Добавить / изменить адрес: address_id (пусто — новый) + поля shipping_* формы.
add_action( 'wc_ajax_arctik_address_save', function () {
	$user = arctik_account_check( 'shipping_full_name' );

	$address = [
		'name'      => arctik_auth_post( 'shipping_full_name' ),
		'phone'     => arctik_auth_post( 'shipping_phone' ),
		'address_1' => arctik_auth_post( 'shipping_address_1' ),
		'address_2' => arctik_auth_post( 'shipping_address_2' ),
		'country'   => arctik_auth_post( 'shipping_country' ),
		'city'      => arctik_auth_post( 'shipping_city' ),
		'postcode'  => arctik_auth_post( 'shipping_postcode' ),
	];

	$errors = [];
	foreach ( [ 'name' => 'shipping_full_name', 'address_1' => 'shipping_address_1' ] as $key => $field ) {
		if ( '' === $address[ $key ] ) {
			$errors[ $field ] = 'Обязательное поле';
		}
	}
	if ( '' !== $address['phone'] && ! arctik_is_phone( $address['phone'] ) ) {
		$errors['shipping_phone'] = "Введите телефон в\u{00A0}формате +7\u{00A0}900\u{00A0}000-00-00";
	}
	if ( $errors ) {
		wp_send_json_error( [ 'errors' => $errors ] );
	}

	$addresses = arctik_addresses( $user->ID );
	$id        = arctik_auth_post( 'address_id' );
	if ( ! isset( $addresses[ $id ] ) ) {
		$id = wp_generate_password( 8, false );
	}
	$addresses[ $id ] = $address;

	arctik_save_addresses( $user->ID, $addresses, arctik_default_address_id( $user->ID ) );
	arctik_account_addresses_response();
} );

// Удалить адрес: address_id. Удалён основной — основным становится первый.
add_action( 'wc_ajax_arctik_address_remove', function () {
	$user      = arctik_account_check( 'shipping_full_name' );
	$addresses = arctik_addresses( $user->ID );
	unset( $addresses[ arctik_auth_post( 'address_id' ) ] );

	arctik_save_addresses( $user->ID, $addresses, arctik_default_address_id( $user->ID ) );
	arctik_account_addresses_response();
} );

// Основной адрес (радиокнопка в карточке): address_id.
add_action( 'wc_ajax_arctik_address_default', function () {
	$user = arctik_account_check( 'shipping_full_name' );

	arctik_save_addresses( $user->ID, arctik_addresses( $user->ID ), arctik_auth_post( 'address_id' ) );
	arctik_account_addresses_response();
} );
