<?php
/**
 * Главная (страница, выбранная в «Настройки → Чтение» как главная, шаблон front-page.php). Вкладка = секция.
 * Слайдер товаров в hero и карточки каталога — все вкусы автоматически (arctik_products()).
 * Картинки сцен (горы и облака about, скалы, пачка, стик) — картинки темы, подогнаны друг под друга.
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'post_meta', 'Главная' )
	->where( 'post_id', '=', (int) get_option( 'page_on_front' ) )

	->add_tab( 'Первый экран', [
		Field::make( 'text', 'arctik_home_hero_title', 'Заголовок' )
			->set_width( 50 ),
		Field::make( 'text', 'arctik_home_hero_title_accent', 'Заголовок: выделенная часть' )
			->set_help_text( 'Идёт после заголовка полупрозрачным цветом' )
			->set_width( 50 ),
		Field::make( 'textarea', 'arctik_home_hero_text', 'Текст' )
			->set_rows( 2 ),
		Field::make( 'file', 'arctik_home_hero_video', 'Видео (mp4, без звука, по кругу)' )
			->set_type( [ 'video' ] )
			->set_help_text( '1920×1080. Пусто — только фото' )
			->set_width( 50 ),
		Field::make( 'image', 'arctik_home_hero_photo', 'Фото' )
			->set_help_text( 'Первый кадр видео — видно, пока видео грузится. От 1920×1080' )
			->set_width( 50 ),
		Field::make( 'html', 'arctik_home_hero_products_info' )
			->set_html( '<p>Слайдер товаров — все вкусы из каталога, в порядке сортировки товаров.</p>' ),
	] )

	->add_tab( 'О бренде', [
		Field::make( 'textarea', 'arctik_home_about_title', 'Заголовок' )
			->set_rows( 2 )
			->set_help_text( 'Перенос строки — как в макете' ),
		Field::make( 'textarea', 'arctik_home_about_text', 'Текст' )
			->set_rows( 4 )
			->set_help_text( 'Переносы строк — только на мобилке. На планшете текст скрыт' ),
		Field::make( 'complex', 'arctik_home_about_cards', 'Карточки' )
			->setup_labels( [ 'plural_name' => 'Карточки', 'singular_name' => 'карточку' ] )
			->set_max( 3 )
			->set_header_template( '<%- title %>' )
			->add_fields( [
				Field::make( 'select', 'icon', 'Иконка' )
					->set_options( arctik_icon_options() )
					->set_width( 20 ),
				Field::make( 'textarea', 'title', 'Заголовок' )
					->set_rows( 2 )
					->set_width( 35 ),
				Field::make( 'textarea', 'text', 'Текст' )
					->set_rows( 3 )
					->set_help_text( 'Переносы строк — только на десктопе' )
					->set_width( 45 ),
			] ),
	] )

	->add_tab( 'Каталог', [
		Field::make( 'text', 'arctik_home_catalog_title', 'Заголовок' )
			->set_help_text( 'Блок стоит на странице дважды — заголовок общий. Товары — все вкусы, порядок — «Товары» (сортировка)' ),
	] );
