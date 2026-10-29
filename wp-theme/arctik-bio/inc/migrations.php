<?php
/**
 * Разовые правки базы для прода: SSH на хостинге нет, поэтому их выполняет сама тема при первом запросе после выгрузки.
 * Каждая — один раз (опция arctik_migration_<имя>); на свежей базе то же делают сиды scripts/seed/.
 * (Перенос «Документов» на отдельные страницы — в inc/documents.php.)
 */

defined( 'ABSPATH' ) || exit;

/**
 * Выполняет $callback один раз. Защита от двух одновременных запросов: add_option не добавит существующую опцию.
 */
function arctik_migrate( string $name, callable $callback ): void {
	$option = 'arctik_migration_' . $name;
	if ( get_option( $option ) || ! add_option( $option . '_lock', time(), '', false ) ) {
		return;
	}

	$callback();

	update_option( $option, 1 );
	delete_option( $option . '_lock' );
}

add_action( 'init', function () {
	// 29.10.2026: «Где нас купить?» снова на «Покупателям» — пункт «Где купить» в шапке и в колонке подвала «Покупателям»
	// (последним, как до удаления), заголовок блока. Города заполняются в админке.
	arctik_migrate( 'where_to_buy', function () {
		$buyers = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/buyers.php', 'number' => 1 ] );
		if ( ! $buyers ) {
			return;
		}

		$url       = get_permalink( $buyers[0] ) . '#where-to-buy';
		$locations = get_nav_menu_locations();

		foreach ( [ 'header', 'footer_buyers' ] as $location ) {
			$menu_id = (int) ( $locations[ $location ] ?? 0 );
			if ( ! $menu_id ) {
				continue;
			}

			$items = (array) wp_get_nav_menu_items( $menu_id );
			foreach ( $items as $item ) {
				if ( str_contains( (string) $item->url, '#where-to-buy' ) ) {
					continue 2;
				}
			}

			wp_update_nav_menu_item( $menu_id, 0, [
				'menu-item-title'    => 'Где купить',
				'menu-item-url'      => $url,
				'menu-item-type'     => 'custom',
				'menu-item-status'   => 'publish',
				'menu-item-position' => count( $items ) + 1,
			] );
		}

		if ( '' === (string) get_post_meta( $buyers[0]->ID, '_arctik_stores_title', true ) ) {
			update_post_meta( $buyers[0]->ID, '_arctik_stores_title', 'Где нас купить?' );
		}
	} );

	// 29.10.2026: первый город блока (решение пользователя), остальные города и карту заполняют в админке.
	// Complex Carbon Fields — сырыми ключами: _<поле>|||<n>|value и _<поле>|<подполе>|<n>|0|value
	arctik_migrate( 'where_to_buy_city', function () {
		$buyers = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/buyers.php', 'number' => 1 ] );
		if ( ! $buyers || metadata_exists( 'post', $buyers[0]->ID, '_arctik_stores_cities|||0|value' ) ) {
			return;
		}

		update_post_meta( $buyers[0]->ID, '_arctik_stores_cities|||0|value', '_' );
		update_post_meta( $buyers[0]->ID, '_arctik_stores_cities|name|0|0|value', 'г. Екатеринбург' );
		update_post_meta( $buyers[0]->ID, '_arctik_stores_cities|addresses|0|0|value', 'ул. Сакко и Ванцетти, д.48' );
	} );
}, 20 );
