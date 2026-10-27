<?php
/**
 * Хелперы вывода для шаблонов.
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL статичного файла темы: картинки, иконки, видео (копируются из src/assets сборкой).
 * arctik_asset( 'icons/icn-cart.svg' ) → …/themes/arctik-bio/assets/icons/icn-cart.svg
 */
function arctik_asset( string $path ): string {
	return get_theme_file_uri( 'assets/' . ltrim( $path, '/' ) );
}

/**
 * Обычный текст из поля: экранирование + типограф.
 */
function arctik_text( ?string $text ): string {
	return arctik_typograph( esc_html( (string) $text ) );
}

/**
 * Текст с разметкой из поля (rich text): разрешённые теги + типограф.
 */
function arctik_rich( ?string $html ): string {
	return arctik_typograph( wp_kses_post( (string) $html ) );
}

/**
 * <img> из медиатеки: width/height, srcset, alt из медиатеки; ниже первого экрана — loading="lazy" (по умолчанию WP),
 * на первом экране передавать [ 'loading' => 'eager', 'fetchpriority' => 'high' ].
 */
function arctik_image( int $attachment_id, string $size = 'full', array $attr = [] ): string {
	if ( ! $attachment_id ) {
		return '';
	}

	return wp_get_attachment_image( $attachment_id, $size, false, $attr );
}

/**
 * Значение поля Carbon Fields у записи (по умолчанию — текущей). Имя — полное, с префиксом: 'arctik_hero_title'.
 */
function arctik_field( string $name, ?int $post_id = null ): mixed {
	if ( ! function_exists( 'carbon_get_post_meta' ) ) {
		return null;
	}

	return carbon_get_post_meta( $post_id ?? (int) get_the_ID(), $name );
}

/**
 * Значение из настроек темы (Theme Options Carbon Fields).
 */
function arctik_option( string $name ): mixed {
	if ( ! function_exists( 'carbon_get_theme_option' ) ) {
		return null;
	}

	return carbon_get_theme_option( $name );
}

/**
 * Ссылка tel: из телефона в любом виде: «+7 921 153 3553» → tel:+79211533553.
 */
function arctik_phone_href( string $phone ): string {
	$digits = preg_replace( '/\D+/', '', $phone );

	if ( 11 === strlen( $digits ) && '8' === $digits[0] ) {
		$digits = '7' . substr( $digits, 1 );
	}

	return 'tel:+' . $digits;
}

/**
 * Соцсети из настроек сайта: [ [ 'network' => 'vk', 'url' => '…', 'label' => 'ВКонтакте' ], … ].
 */
function arctik_socials(): array {
	$labels = [
		'vk'       => 'ВКонтакте',
		'whatsapp' => 'WhatsApp',
		'inst'     => 'Instagram',
		'telegram' => 'Telegram',
	];

	$socials = [];
	foreach ( (array) arctik_option( 'arctik_socials' ) as $item ) {
		$network = $item['network'] ?? '';

		if ( isset( $labels[ $network ] ) && ! empty( $item['url'] ) ) {
			$socials[] = [
				'network' => $network,
				'url'     => $item['url'],
				'label'   => $labels[ $network ],
			];
		}
	}

	return $socials;
}

/**
 * Товары-вкусы: опубликованные и видимые в каталоге, в порядке сортировки из админки
 * (подвал, «Попробуйте также», «Другие вкусы»). Набор на 3 месяца скрыт из каталога — сюда не попадает.
 *
 * @return WC_Product[]
 */
function arctik_products(): array {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return [];
	}

	return wc_get_products( [
		'status'     => 'publish',
		'visibility' => 'catalog',
		'limit'      => -1,
		'orderby'    => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
	] );
}

/**
 * Меню из области темы с нашими классами (разметка wp_nav_menu: ul.menu > li.menu-item > a).
 * $attrs — атрибуты <ul> (data-js-swipe-group и т.п.).
 */
function arctik_menu( string $location, string $class, string $attrs = '' ): void {
	wp_nav_menu( [
		'theme_location' => $location,
		'container'      => false,
		'menu_class'     => $class,
		'menu_id'        => '',
		'items_wrap'     => '<ul class="%2$s"' . ( $attrs ? ' ' . $attrs : '' ) . '>%3$s</ul>',
		'depth'          => 1,
		'fallback_cb'    => false,
	] );
}

/**
 * Название меню в области темы (заголовки колонок подвала).
 */
function arctik_menu_name( string $location ): string {
	return (string) wp_get_nav_menu_name( $location );
}

/**
 * Текст из многострочного поля: переносы строк → <br> (заголовки «как в макете»), экранирование + типограф.
 */
function arctik_lines( ?string $text ): string {
	return arctik_typograph( nl2br( esc_html( trim( (string) $text ) ), false ) );
}

/**
 * Иконки темы (assets/icons/icn-*.svg) для выбора в полях: [ 'sun' => 'Солнце', … ].
 */
function arctik_icon_options(): array {
	return [
		'sun'        => 'Солнце',
		'date'       => 'Календарь',
		'glass'      => 'Стакан',
		'skin'       => 'Кожа',
		'joints'     => 'Суставы',
		'ligaments'  => 'Связки',
		'connective' => 'Соединительная ткань',
		'hair-nails' => 'Волосы и ногти',
	];
}

/**
 * URL иконки темы по ключу из arctik_icon_options().
 */
function arctik_icon_url( string $icon ): string {
	return arctik_asset( 'icons/icn-' . sanitize_file_name( $icon ) . '.svg' );
}

/**
 * URL файла из медиатеки (поле file / image) или ''.
 */
function arctik_file_url( mixed $attachment_id ): string {
	return $attachment_id ? (string) wp_get_attachment_url( (int) $attachment_id ) : '';
}
