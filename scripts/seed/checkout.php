<?php
/**
 * Оформление заказа (этап «Оф1»): классический шорткод вместо блока Checkout,
 * аккаунт при оформлении, зона доставки «Россия» с «Пунктом выдачи» и «Курьером», способы оплаты СБП / карта (заглушки),
 * меню ссылок внизу формы. Повторный запуск безопасен.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/checkout.php
 */

require_once __DIR__ . '/_lib.php';

// Страница оформления — шорткод (тема переопределяет его шаблоны), а не блок Checkout.
$checkout_id = wc_get_page_id( 'checkout' );
wp_update_post( [
	'ID'           => $checkout_id,
	'post_title'   => 'Оформление заказа',
	'post_content' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->',
] );

// «Публичная оферта» (страница условий WC) — документ на странице «Документы», ставит /seed/documents.php.

// Аккаунт: гостю создаётся при оформлении (логин и пароль — автоматически, письмо со ссылкой на пароль), см. inc/checkout.php.
update_option( 'woocommerce_enable_guest_checkout', 'yes' );
update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );
update_option( 'woocommerce_registration_generate_username', 'yes' );
update_option( 'woocommerce_registration_generate_password', 'yes' );
update_option( 'woocommerce_enable_coupons', 'yes' );
update_option( 'woocommerce_ship_to_countries', 'specific' );
update_option( 'woocommerce_specific_ship_to_countries', [ 'RU' ] );
update_option( 'woocommerce_default_customer_address', 'base' );

// Зона «Россия»: пункт выдачи и курьер, бесплатно, «Завтра» (inc/shipping.php).
$zone = null;
foreach ( WC_Shipping_Zones::get_zones() as $data ) {
	if ( 'Россия' === $data['zone_name'] ) {
		$zone = new WC_Shipping_Zone( $data['id'] );
	}
}
if ( ! $zone ) {
	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'Россия' );
	$zone->add_location( 'RU', 'country' );
	$zone->save();
}
$methods = array_map( fn( $method ) => $method->id, $zone->get_shipping_methods() );
foreach ( [ 'arctik_pickup', 'arctik_courier' ] as $method ) {
	if ( ! in_array( $method, $methods, true ) ) {
		$zone->add_shipping_method( $method );
	}
}

// Оплата: СБП и карта (заглушки, inc/payment.php) — включены, в этом порядке.
foreach ( [ 'arctik_sbp', 'arctik_card' ] as $gateway ) {
	$settings            = get_option( "woocommerce_{$gateway}_settings", [] );
	$settings['enabled'] = 'yes';
	update_option( "woocommerce_{$gateway}_settings", $settings );
}
update_option( 'woocommerce_gateway_order', [ 'arctik_sbp' => 0, 'arctik_card' => 1 ] );

// Меню ссылок внизу формы — разделы «Покупателям».
$name = 'Оформление заказа';
$menu = wp_get_nav_menu_object( $name );
if ( $menu ) {
	wp_delete_nav_menu( $menu->term_id );
}
$menu_id = wp_create_nav_menu( $name );
$buyers  = get_permalink( get_page_by_path( 'buyers' ) );
foreach ( [ [ 'Оплата', 'help-payment' ], [ 'Доставка', 'help-delivery' ], [ 'Правила возврата', 'help-returns' ] ] as $position => [ $title, $anchor ] ) {
	wp_update_nav_menu_item( $menu_id, 0, [
		'menu-item-title'    => $title,
		'menu-item-status'   => 'publish',
		'menu-item-position' => $position + 1,
		'menu-item-type'     => 'custom',
		'menu-item-url'      => $buyers . '#' . $anchor,
	] );
}
$locations             = get_theme_mod( 'nav_menu_locations', [] );
$locations['checkout'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

WP_CLI::success( 'Оформление заказа настроено' );
