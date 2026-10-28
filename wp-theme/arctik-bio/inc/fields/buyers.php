<?php
/**
 * Страница «Покупателям» (шаблон page-templates/buyers.php): вопросы по разделам и «Где нас купить?».
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'post_meta', 'Покупателям' )
	->where( 'post_template', '=', 'page-templates/buyers.php' )

	->add_tab( 'Вопросы', [
		Field::make( 'html', 'arctik_buyers_info' )
			->set_html( '<p>Заголовок — название страницы. Раздел без вопросов не выводится. Открыт первый вопрос первого раздела.</p>' ),
		Field::make( 'textarea', 'arctik_buyers_lead', 'Подпись над заголовком' )
			->set_rows( 2 )
			->set_help_text( 'Перенос строки — как в тексте' ),
		Field::make( 'image', 'arctik_buyers_bg', 'Фон' )
			->set_help_text( 'Вертикальный, от 936×1024' ),
		Field::make( 'rich_text', 'arctik_buyers_note', 'Подпись под меню разделов' )
			->set_settings( [ 'media_buttons' => false ] )
			->set_rows( 3 )
			->set_help_text( 'Одна строка, можно ссылку. Видна только на десктопе' ),
		Field::make( 'complex', 'arctik_buyers_groups', 'Разделы' )
			->setup_labels( [ 'plural_name' => 'Разделы', 'singular_name' => 'раздел' ] )
			->set_collapsed( true )
			->set_header_template( '<%- title %>' )
			->add_fields( [
				Field::make( 'text', 'title', 'Заголовок' )
					->set_width( 34 ),
				Field::make( 'text', 'menu', 'Название в меню' )
					->set_help_text( 'Пусто — заголовок' )
					->set_width( 33 ),
				Field::make( 'text', 'anchor', 'Якорь' )
					->set_help_text( 'Латиницей, для ссылок вида /buyers/#help-delivery. Пусто — help-<номер>' )
					->set_width( 33 ),
				Field::make( 'complex', 'questions', 'Вопросы' )
					->setup_labels( [ 'plural_name' => 'Вопросы', 'singular_name' => 'вопрос' ] )
					->set_collapsed( true )
					->set_header_template( '<%- question %>' )
					->add_fields( [
						Field::make( 'text', 'question', 'Вопрос' ),
						Field::make( 'textarea', 'answer', 'Ответ' )
							->set_rows( 3 ),
					] ),
			] ),
	] );

// «Где нас купить?» пока скрыт (просьба пользователя): секция закомментирована в page-templates/buyers.php.
// Вернуть — снять комментарий и поставить «)» вместо «);» выше.
/*
	->add_tab( 'Где нас купить?', [
		Field::make( 'text', 'arctik_stores_title', 'Заголовок' ),
		Field::make( 'complex', 'arctik_stores_cities', 'Города' )
			->setup_labels( [ 'plural_name' => 'Города', 'singular_name' => 'город' ] )
			->set_collapsed( true )
			->set_header_template( '<%- name %>' )
			->add_fields( [
				Field::make( 'text', 'name', 'Город' )
					->set_help_text( 'Как на сайте: «г. Москва»' ),
				Field::make( 'textarea', 'addresses', 'Адреса' )
					->set_rows( 3 )
					->set_help_text( 'Каждый адрес — с новой строки' ),
			] ),
		Field::make( 'image', 'arctik_stores_map', 'Карта' )
			->set_help_text( 'Картинка карты, от 1958×812' ),
	] );
*/
