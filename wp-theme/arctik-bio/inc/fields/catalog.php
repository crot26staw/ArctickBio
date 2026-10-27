<?php
/**
 * Страница «Каталог» (страница магазина WooCommerce, шаблон woocommerce/archive-product.php). Вкладка = секция.
 * Видео, курс, преимущества и сравнение — общие блоки (inc/fields/shared-blocks.php).
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'post_meta', 'Каталог' )
	->where( 'post_id', '=', (int) get_option( 'woocommerce_shop_page_id' ) )

	->add_tab( 'Первый экран', [
		Field::make( 'text', 'arctik_catalog_title', 'Заголовок' ),
		Field::make( 'image', 'arctik_catalog_hero_image', 'Фон' )
			->set_help_text( 'Широкий кадр, от 2172×724' ),
	] )

	->add_tab( 'Список товаров', [
		Field::make( 'text', 'arctik_catalog_grid_title', 'Заголовок над карточками' )
			->set_help_text( 'Виден на мобилке, на остальных ширинах — только для скринридеров. Товары и порядок — «Товары» (сортировка)' ),
	] )

	->add_tab( 'Баннер набора', [
		Field::make( 'textarea', 'arctik_catalog_offer_title', 'Заголовок' )
			->set_rows( 2 ),
		Field::make( 'complex', 'arctik_catalog_offer_list', 'Список' )
			->setup_labels( [ 'plural_name' => 'Пункты', 'singular_name' => 'пункт' ] )
			->add_fields( [
				Field::make( 'text', 'text', 'Пункт' ),
			] )
			->set_header_template( '<%- text %>' ),
		Field::make( 'association', 'arctik_catalog_offer_product', 'Товар в плашке' )
			->set_types( [ [ 'type' => 'post', 'post_type' => 'product' ] ] )
			->set_max( 1 )
			->set_help_text( 'Название и подпись плашки — «Заголовок / подзаголовок мини-карточки» товара, цена — из товара' ),
		Field::make( 'image', 'arctik_catalog_offer_image', 'Фото справа' )
			->set_help_text( 'Вертикальное, от 1024×1536' ),
	] );
