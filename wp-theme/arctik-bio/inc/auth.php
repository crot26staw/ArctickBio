<?php
/**
 * Попап входа (template-parts/auth-modal.php): данные для auth.js, ссылка сброса пароля.
 * Обработчики форм — inc/ajax/auth.php.
 *
 * Сброс пароля: письмо WP (retrieve_password) ведёт не на wp-login.php, а на сайт — /?reset-login=…&reset-key=….
 * Ключ проверяется, кладётся в cookie (httponly, как у wp-login.php) и происходит переход на /#auth-reset —
 * auth.js открывает экран нового пароля, обработчик берёт логин и ключ из cookie.
 * Ключ неверный или устарел — экран «Забыли пароль?» с ошибкой в поле (arctik_auth_error()).
 */

defined( 'ABSPATH' ) || exit;

const ARCTIK_RESET_COOKIE = 'arctik-resetpass';

/**
 * Ошибка формы попапа, выведенная сервером: [ 'id поля' => 'текст' ]. Поле выводится с is-error и текстом,
 * auth.js открывает попап на его экране.
 */
function arctik_auth_error( ?array $error = null ): array {
	static $current = [];

	if ( null !== $error ) {
		$current = $error;
	}

	return $current;
}

/**
 * Аргументы поля попапа с серверной ошибкой (mod is-error + hint), если она у этого поля.
 */
function arctik_auth_field( array $args ): array {
	$error = arctik_auth_error()[ $args['id'] ] ?? '';

	if ( $error ) {
		$args['mod']  = trim( ( $args['mod'] ?? '' ) . ' is-error' );
		$args['hint'] = $error;
	}

	return $args;
}

/**
 * Логин и ключ сброса из cookie: [ login, key ] или null.
 */
function arctik_auth_reset_cookie(): ?array {
	$value = sanitize_text_field( wp_unslash( $_COOKIE[ ARCTIK_RESET_COOKIE ] ?? '' ) );

	if ( ! str_contains( $value, ':' ) ) {
		return null;
	}

	return explode( ':', $value, 2 );
}

function arctik_auth_set_reset_cookie( string $value ): void {
	setcookie( ARCTIK_RESET_COOKIE, $value, [
		'expires'  => $value ? 0 : time() - YEAR_IN_SECONDS,
		'path'     => COOKIEPATH,
		'domain'   => COOKIE_DOMAIN,
		'secure'   => is_ssl(),
		'httponly' => true,
		'samesite' => 'Lax',
	] );
}

// Ссылка в письме сброса — на сайт, а не на wp-login.php (остальной текст письма — стандартный WP).
add_filter( 'retrieve_password_message', function ( string $message, string $key, string $user_login ): string {
	$url = add_query_arg( [
		'reset-login' => rawurlencode( $user_login ),
		'reset-key'   => $key,
	], home_url( '/' ) );

	return preg_replace( '#\S*wp-login\.php\?\S+#', $url, $message );
}, 10, 3 );

// Ссылки WC на установку/сброс пароля (письмо «Аккаунт создан» после оформления заказа, сброс из «Моего аккаунта»)
// ведут на страницу «Забыли пароль» WC — переводим их в наш поток: проверка ключа ниже, экран #auth-reset.
add_action( 'template_redirect', function () {
	if ( ! is_wc_endpoint_url( 'lost-password' ) || empty( $_GET['key'] ) ) {
		return;
	}

	$login = isset( $_GET['login'] ) ? sanitize_user( rawurldecode( wp_unslash( $_GET['login'] ) ) ) : '';
	if ( ! $login && ! empty( $_GET['id'] ) ) {
		$login = (string) ( get_userdata( absint( $_GET['id'] ) )->user_login ?? '' );
	}

	wp_safe_redirect( add_query_arg( [
		'reset-login' => rawurlencode( $login ),
		'reset-key'   => sanitize_text_field( wp_unslash( $_GET['key'] ) ),
	], home_url( '/' ) ) );
	exit;
}, 5 );

// Переход по ссылке из письма.
add_action( 'template_redirect', function () {
	if ( ! isset( $_GET['reset-key'], $_GET['reset-login'] ) ) {
		return;
	}

	$login = sanitize_user( rawurldecode( wp_unslash( $_GET['reset-login'] ) ) );
	$key   = sanitize_text_field( wp_unslash( $_GET['reset-key'] ) );
	$user  = check_password_reset_key( $key, $login );

	if ( is_wp_error( $user ) ) {
		arctik_auth_set_reset_cookie( '' );
		arctik_auth_error( [ 'auth-forgot-email' => "Ссылка устарела. Запросите новую" ] );
		return;
	}

	// Попап входа выводится только гостям: сброс пароля из письма — с выходом из текущего аккаунта.
	if ( is_user_logged_in() ) {
		wp_logout();
	}

	arctik_auth_set_reset_cookie( $login . ':' . $key );
	wp_safe_redirect( home_url( '/#auth-reset' ) );
	exit;
} );

add_filter( 'arctik_js_data', function ( array $data ): array {
	if ( ! is_user_logged_in() ) {
		$data['auth'] = [
			'endpoint' => WC_AJAX::get_endpoint( '%%endpoint%%' ),
			'nonce'    => wp_create_nonce( 'arctik-auth' ),
		];
	}

	return $data;
} );
