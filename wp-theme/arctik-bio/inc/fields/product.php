<?php
/**
 * Поля товара и общие поля товаров (Настройки сайта → Товары).
 *
 * Товар-вкус — вариативный товар WC, вариации по атрибуту «Формат» (pa_format: 15 / 30 саше);
 * цена, старая цена (обычная цена при заданной цене распродажи) и подпись варианта («Попробовать · 15 дней» —
 * описание вариации) задаются в вариациях. Набор на 3 месяца — простой товар, скрытый из каталога.
 *
 * Ингредиенты, «Как принимать», «Доставка и хранение», документы: общие — в настройках, у товара можно заменить своими
 * (заполнено у товара — выводится его, иначе общее; arctik_product_content()). «Состав и пищевая ценность» — только у товара.
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Поля карточки товара, общие для товара и настроек: ингредиенты, «Как принимать», документы, «Доставка и хранение».
 * $prefix — arctik_product_ (у товара) или arctik_products_ (в настройках).
 */
function arctik_product_content_fields( string $prefix ): array {
	return [
		'ingredients' => [
			Field::make( 'complex', "{$prefix}ingredients", 'Ингредиенты' )
				->setup_labels( [ 'plural_name' => 'Ингредиенты', 'singular_name' => 'ингредиент' ] )
				->add_fields( [
					Field::make( 'image', 'image', 'Картинка' )
						->set_help_text( 'Квадрат, от 442×442' )
						->set_width( 25 ),
					Field::make( 'text', 'title', 'Название' )
						->set_width( 25 ),
					Field::make( 'textarea', 'text', 'Описание' )
						->set_rows( 3 )
						->set_width( 50 ),
				] )
				->set_header_template( '<%- title %>' ),
		],
		'usage'       => [
			Field::make( 'textarea', "{$prefix}usage", 'Как принимать' )
				->set_help_text( 'Пустая строка — новый абзац' )
				->set_rows( 5 ),
		],
		'documents'   => [
			Field::make( 'complex', "{$prefix}documents", 'Документы' )
				->setup_labels( [ 'plural_name' => 'Документы', 'singular_name' => 'документ' ] )
				->add_fields( [
					Field::make( 'text', 'title', 'Название' )
						->set_width( 50 ),
					Field::make( 'file', 'file', 'Файл (PDF)' )
						->set_type( [ 'application/pdf' ] )
						->set_width( 50 ),
				] )
				->set_header_template( '<%- title %>' ),
		],
		'delivery'    => [
			Field::make( 'textarea', "{$prefix}delivery", 'Доставка и хранение' )
				->set_help_text( 'Пустая строка — новый абзац' )
				->set_rows( 5 ),
		],
	];
}

// Товар.
$arctik_own = arctik_product_content_fields( 'arctik_product_' );

Container::make( 'post_meta', 'Оформление товара' )
	->where( 'post_type', '=', 'product' )

	->add_tab( 'Названия', [
		Field::make( 'text', 'arctik_product_full_title', 'Полное название (заголовок страницы товара)' )
			->set_help_text( 'Например: «Премиальный морской коллаген с дикой черникой». Пусто — название товара' ),
		Field::make( 'text', 'arctik_product_mini_title', 'Заголовок мини-карточки' )
			->set_help_text( 'Первая строка мини-карточки (подвал, корзина, первый экран главной), например «Морской коллаген». Пусто — название товара' )
			->set_width( 50 ),
		Field::make( 'text', 'arctik_product_mini_subtitle', 'Подзаголовок мини-карточки' )
			->set_help_text( 'Например «с дикой черникой»' )
			->set_width( 50 ),
	] )

	->add_tab( 'Картинки', [
		Field::make( 'html', 'arctik_product_images_info' )
			->set_html( '<p>«Изображение товара» (справа) — пачка для карточки каталога, корзины и писем. «Галерея товара» — фото первого экрана страницы товара, первое — большое.</p>' ),
		Field::make( 'image', 'arctik_product_hover_image', 'Фото при наведении (карточка каталога)' )
			->set_help_text( 'Квадрат, от 800×800' )
			->set_width( 50 ),
		Field::make( 'image', 'arctik_product_mini_image', 'Пачка для мини-карточки и выбора формата' )
			->set_help_text( 'От 240×186. Пусто — «Изображение товара»' )
			->set_width( 50 ),
	] )

	->add_tab( 'Ингредиенты', array_merge(
		[ Field::make( 'html', 'arctik_product_ingredients_info' )->set_html( '<p>Пусто — общие из «Настройки сайта → Товары».</p>' ) ],
		$arctik_own['ingredients']
	) )

	->add_tab( 'Аккордеоны', array_merge(
		[ Field::make( 'html', 'arctik_product_accordions_info' )->set_html( '<p>Порядок на странице: «О продукте» — описание товара (редактор выше), «Состав и пищевая ценность», «Как принимать», «Доставка и хранение», «Документы». Пустой пункт не выводится; «Как принимать», «Доставка и хранение» и «Документы»: пусто — общее из «Настройки сайта → Товары».</p>' ) ],
		[
			Field::make( 'textarea', 'arctik_product_composition', 'Состав и пищевая ценность' )
				->set_help_text( 'Пустая строка — новый абзац, перенос строки — новая строка' )
				->set_rows( 8 ),
		],
		$arctik_own['usage'],
		$arctik_own['delivery'],
		$arctik_own['documents']
	) );

// Общее для всех товаров.
$arctik_common = arctik_product_content_fields( 'arctik_products_' );

Container::make( 'theme_options', 'Товары' )
	->set_page_parent( 'arctik-options' )
	->set_page_file( 'arctik-options-products' )

	->add_tab( 'Покупка', [
		Field::make( 'text', 'arctik_products_delivery_note', 'Строка о доставке под кнопкой «В корзину»' ),
	] )

	->add_tab( 'Ингредиенты', $arctik_common['ingredients'] )

	->add_tab( 'Аккордеоны', array_merge(
		$arctik_common['usage'],
		$arctik_common['delivery'],
		$arctik_common['documents']
	) );
