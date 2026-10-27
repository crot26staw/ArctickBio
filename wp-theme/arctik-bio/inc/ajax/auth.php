<?php
/**
 * Попап входа: свои wc-ajax обработчики (?wc-ajax=arctik_…), вызывает auth.js (window.arctik.auth — inc/auth.php).
 * Поля — как в формах попапа (template-parts/auth-modal.php). Ошибка — { errors: { имя поля: текст } }, их показывает field.js.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Проверка nonce; неверный — ошибка у поля $field.
 */
function arctik_auth_check_nonce( string $field ): void {
	if ( ! check_ajax_referer( 'arctik-auth', 'security', false ) ) {
		wp_send_json_error( [ 'errors' => [ $field => "Сессия устарела, обновите страницу" ] ], 403 );
	}
}

function arctik_auth_post( string $name ): string {
	return trim( sanitize_text_field( wp_unslash( $_POST[ $name ] ?? '' ) ) );
}

/**
 * Пароль — без sanitize_text_field (он меняет пробелы и теги), как в wp-login.php.
 */
function arctik_auth_password( string $name ): string {
	return is_string( $_POST[ $name ] ?? null ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
}

// Вход: username (email или логин), password.
add_action( 'wc_ajax_arctik_login', function () {
	arctik_auth_check_nonce( 'password' );

	$user = wp_signon( [
		'user_login'    => arctik_auth_post( 'username' ),
		'user_password' => arctik_auth_password( 'password' ),
		'remember'      => true,
	], is_ssl() );

	if ( is_wp_error( $user ) ) {
		wp_send_json_error( [ 'errors' => [ 'password' => "Неверный логин или пароль" ] ] );
	}

	wp_send_json_success();
} );

// Регистрация: email, first_name, password, password_repeat, privacy_policy. Логин WC создаёт из email, после — сразу вход.
add_action( 'wc_ajax_arctik_register', function () {
	arctik_auth_check_nonce( 'email' );

	$email    = sanitize_email( arctik_auth_post( 'email' ) );
	$name     = arctik_auth_post( 'first_name' );
	$password = arctik_auth_password( 'password' );
	$errors   = [];

	if ( ! is_email( $email ) ) {
		$errors['email'] = "Введите email в\u{00A0}формате name@example.com";
	}
	if ( '' === $name ) {
		$errors['first_name'] = 'Введите имя';
	}
	if ( '' === $password ) {
		$errors['password'] = 'Введите пароль';
	}
	if ( arctik_auth_password( 'password_repeat' ) !== $password ) {
		$errors['password_repeat'] = "Пароли не\u{00A0}совпадают";
	}
	if ( empty( $_POST['privacy_policy'] ) ) {
		$errors['privacy_policy'] = "Подтвердите согласие";
	}
	if ( $errors ) {
		wp_send_json_error( [ 'errors' => $errors ] );
	}

	$customer_id = wc_create_new_customer( $email, '', $password, [ 'first_name' => $name ] );

	if ( is_wp_error( $customer_id ) ) {
		$message = 'registration-error-email-exists' === $customer_id->get_error_code()
			? "Аккаунт с\u{00A0}этим email уже есть\u{00A0}— войдите"
			: wp_strip_all_tags( $customer_id->get_error_message() );

		wp_send_json_error( [ 'errors' => [ 'email' => $message ] ] );
	}

	wc_set_customer_auth_cookie( $customer_id );
	wp_send_json_success();
} );

// Ссылка для сброса пароля: user_login (email). Экраны «Забыли пароль?» и «Письмо отправлено» («Отправить снова»).
add_action( 'wc_ajax_arctik_forgot', function () {
	arctik_auth_check_nonce( 'user_login' );

	$login = arctik_auth_post( 'user_login' );

	if ( ! $login || ! get_user_by( 'email', $login ) ) {
		wp_send_json_error( [ 'errors' => [ 'user_login' => "Аккаунт с\u{00A0}таким email не\u{00A0}найден" ] ] );
	}

	$result = retrieve_password( $login );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( [ 'errors' => [ 'user_login' => wp_strip_all_tags( $result->get_error_message() ) ] ] );
	}

	wp_send_json_success();
} );

// Новый пароль: password_1, password_2; логин и ключ — из cookie (inc/auth.php). В ответе — email для поля входа.
add_action( 'wc_ajax_arctik_reset', function () {
	arctik_auth_check_nonce( 'password_1' );

	$password = arctik_auth_password( 'password_1' );
	$reset    = arctik_auth_reset_cookie();
	$user     = $reset ? check_password_reset_key( $reset[1], $reset[0] ) : null;

	if ( ! $user || is_wp_error( $user ) ) {
		wp_send_json_error( [ 'errors' => [ 'password_1' => "Ссылка устарела. Запросите новую" ] ] );
	}
	if ( '' === $password ) {
		wp_send_json_error( [ 'errors' => [ 'password_1' => 'Введите пароль' ] ] );
	}
	if ( arctik_auth_password( 'password_2' ) !== $password ) {
		wp_send_json_error( [ 'errors' => [ 'password_2' => "Пароли не\u{00A0}совпадают" ] ] );
	}

	reset_password( $user, $password );
	arctik_auth_set_reset_cookie( '' );

	wp_send_json_success( [ 'login' => $user->user_email ] );
} );
