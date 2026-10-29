<?php
/**
 * Начальное наполнение (этап 0.3): страницы разделов, меню, настройки сайта — тексты и ссылки из статики.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/base.php
 * Повторный запуск безопасен: существующие страницы и меню не дублируются, меню пересобираются.
 */

// Страницы разделов (шаблоны — на этапах страниц).
$pages = [
	'about'     => 'О компании',
	'buyers'    => 'Покупателям',
	'contacts'  => 'Контакты',
	'documents' => 'Документы',
];
foreach ( $pages as $slug => $title ) {
	if ( ! get_page_by_path( $slug ) ) {
		wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug ] );
	}
}

$page = fn( string $slug ) => get_page_by_path( $slug );
$url  = fn( string $path ) => home_url( $path );

$shop    = (int) wc_get_page_id( 'shop' );
$about   = $page( 'about' )->ID;
$buyers  = $page( 'buyers' )->ID;
$contact = $page( 'contacts' )->ID;

// [ заголовок, ID страницы | URL ]
$menus = [
	'header'        => [ 'Шапка', [
		[ 'Каталог', $shop ],
		[ 'О бренде', $about ],
		[ 'Контакты', $contact ],
	] ],
	'mobile'        => [ 'Мобильное меню', [
		[ 'Главная', $url( '/' ) ],
		[ 'Каталог', $shop ],
		[ 'О нас', $about ],
		[ 'Покупателям', $buyers ],
		[ 'Контакты', $contact ],
	] ],
	'footer_about'  => [ 'О нас', [
		[ 'Как работает', '#' ],
		[ 'О бренде', $about ],
		[ 'Производство', '#' ],
	] ],
	'footer_buyers' => [ 'Покупателям', [
		[ 'Оплата', $url( '/buyers/#help-payment' ) ],
		[ 'Доставка', $url( '/buyers/#help-delivery' ) ],
		[ 'Реквизиты', $url( '/documents/#oferta' ) ],
		[ 'Отзывы', $url( '/#reviews' ) ],
		[ 'Частые вопросы', $buyers ],
	] ],
	'footer_docs'   => [ 'Документы', [
		[ 'Публичная оферта', $url( '/documents/#oferta' ) ],
		[ 'Политика конфиденциальности', $url( '/documents/#privacy' ) ],
		[ 'Пользовательское соглашение', $url( '/documents/#agreement' ) ],
		[ 'Политика Cookies', $url( '/documents/#cookie' ) ],
	] ],
];

$locations = get_theme_mod( 'nav_menu_locations', [] );
foreach ( $menus as $location => [ $name, $items ] ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		wp_delete_nav_menu( $menu->term_id );
	}
	$menu_id = wp_create_nav_menu( $name );

	foreach ( $items as $position => [ $title, $target ] ) {
		$item = [ 'menu-item-title' => $title, 'menu-item-status' => 'publish', 'menu-item-position' => $position + 1 ];
		if ( is_int( $target ) ) {
			$item += [ 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $target ];
		} else {
			$item += [ 'menu-item-type' => 'custom', 'menu-item-url' => $target ];
		}
		wp_update_nav_menu_item( $menu_id, 0, $item );
	}

	$locations[ $location ] = $menu_id;
}
set_theme_mod( 'nav_menu_locations', $locations );

// Настройки сайта: один набор контактов (из подвала статики).
$options = [
	'arctik_phone'                 => '+7 921 153 3553',
	'arctik_email'                 => 'arcticbiomarine@yandex.ru',
	'arctik_address'               => 'Мурманская область, Кольский район, п. Молочный, территория ПТФ «Снежная»',
	'arctik_question_link'         => '#',
	'arctik_footer_question_title' => 'Остались вопросы?',
	'arctik_footer_question_text'  => 'Напишите нам — поможем с выбором продукта или заказом',
	'arctik_copyright'             => 'АрктикБиоМарин. Все права защищены',
	'arctik_requisites'            => "ИП Волков Алексей Петрович\nИНН 510502631360\nОГРНИП 310510509700021",
	'arctik_disclaimer'            => 'Продукт является пищевой продукцией и не является лекарственным средством. Возможна индивидуальная непереносимость компонентов',
	'arctik_developer_link'        => '',
	'arctik_socials'               => [
		[ 'network' => 'vk', 'url' => 'https://vk.ru/arcticbiomarine' ],
		[ 'network' => 'max', 'url' => 'https://max.ru/channel_arcticbiomarine' ],
		[ 'network' => 'inst', 'url' => 'https://www.instagram.com/arcticbiomarine' ],
		[ 'network' => 'telegram', 'url' => 'https://t.me/arcticbiomarine' ],
	],
];
foreach ( $options as $name => $value ) {
	carbon_set_theme_option( $name, $value );
}

WP_CLI::success( 'Страницы, меню и настройки сайта заполнены' );
