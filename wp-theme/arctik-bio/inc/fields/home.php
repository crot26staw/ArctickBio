<?php
/**
 * Главная (страница, выбранная в «Настройки → Чтение» как главная, шаблон front-page.php). Вкладка = секция.
 * Слайдер товаров в hero и карточки каталога — все вкусы автоматически (arctik_products()).
 * Картинки сцен (горы и облака about, скалы, пачка, стик, видео стика и камни «Состава») — файлы темы, подогнаны друг под друга.
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
	] )

	->add_tab( 'Состав', [
		Field::make( 'text', 'arctik_home_compound_title', 'Заголовок' ),
		Field::make( 'complex', 'arctik_home_compound_stats', 'Цифры (пока стик цельный)' )
			->setup_labels( [ 'plural_name' => 'Цифры', 'singular_name' => 'цифру' ] )
			->set_max( 2 )
			->set_help_text( 'Первая — слева от стика, вторая — справа' )
			->set_header_template( '<%- amount %> <%- unit %>' )
			->add_fields( [
				Field::make( 'text', 'amount', 'Значение' )
					->set_help_text( '«98%», «5 000»' )
					->set_width( 25 ),
				Field::make( 'text', 'unit', 'Единица' )
					->set_help_text( 'Мельче значения: «мг»' )
					->set_width( 25 ),
				Field::make( 'textarea', 'label', 'Подпись' )
					->set_rows( 2 )
					->set_width( 50 ),
				Field::make( 'textarea', 'notes', 'Пункты' )
					->set_rows( 2 )
					->set_help_text( 'Каждый пункт — с новой строки' ),
			] ),
		Field::make( 'complex', 'arctik_home_compound_vitamins', 'Витамины (когда стик раскрылся)' )
			->setup_labels( [ 'plural_name' => 'Витамины', 'singular_name' => 'витамин' ] )
			->set_max( 6 )
			->set_help_text( 'Места вокруг стика — по порядку: первые три слева сверху вниз, остальные справа' )
			->set_header_template( '<%- name %>' )
			->add_fields( [
				Field::make( 'text', 'name', 'Название' )
					->set_width( 25 ),
				Field::make( 'image', 'image', 'Ягода' )
					->set_help_text( 'Круглая, 80×80' )
					->set_width( 25 ),
				Field::make( 'textarea', 'text', 'Описание' )
					->set_rows( 3 )
					->set_width( 50 ),
			] ),
	] )

	->add_tab( 'Исследования', [
		Field::make( 'text', 'arctik_home_research_title', 'Заголовок' ),
		Field::make( 'image', 'arctik_home_research_image', 'Фото' )
			->set_help_text( 'От 1440×1600' ),
		Field::make( 'complex', 'arctik_home_research_items', 'Пункты' )
			->setup_labels( [ 'plural_name' => 'Пункты', 'singular_name' => 'пункт' ] )
			->set_collapsed( true )
			->set_help_text( 'Пункт с документами — всегда последний' )
			->set_header_template( '<%- label %>' )
			->add_fields( [
				Field::make( 'text', 'label', 'Подпись в свёрнутом виде' ),
				Field::make( 'text', 'prefix', 'Знак перед числом' )
					->set_help_text( '«≈», «>», пусто' )
					->set_width( 33 ),
				Field::make( 'text', 'number', 'Число' )
					->set_help_text( 'Целое — отсчитывается от 0' )
					->set_width( 33 ),
				Field::make( 'text', 'suffix', 'Знак после числа' )
					->set_help_text( '«%», пусто' )
					->set_width( 34 ),
				Field::make( 'text', 'title', 'Заголовок' ),
				Field::make( 'textarea', 'text', 'Текст' )
					->set_rows( 3 )
					->set_width( 70 ),
				Field::make( 'image', 'image', 'Фото' )
					->set_help_text( 'От 450×600' )
					->set_width( 30 ),
			] ),
		Field::make( 'text', 'arctik_home_research_docs_label', 'Пункт с документами: подпись' ),
		Field::make( 'complex', 'arctik_home_research_docs', 'Документы' )
			->setup_labels( [ 'plural_name' => 'Документы', 'singular_name' => 'документ' ] )
			->set_max( 3 )
			->set_help_text( 'Нет документов — пункта нет' )
			->set_header_template( '<%- title %>' )
			->add_fields( [
				Field::make( 'textarea', 'title', 'Название' )
					->set_rows( 2 )
					->set_help_text( 'Перенос строки — как в макете' )
					->set_width( 50 ),
				Field::make( 'file', 'file', 'Файл (PDF)' )
					->set_type( [ 'application/pdf' ] )
					->set_help_text( 'Размер подставится сам. Без файла — плитка без ссылки' )
					->set_width( 50 ),
			] ),
	] );
