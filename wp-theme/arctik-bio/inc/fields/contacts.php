<?php
/**
 * Страница «Контакты» (шаблон page-templates/contacts.php). Контакты и соцсети — «Настройки сайта».
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'post_meta', 'Контакты' )
	->where( 'post_template', '=', 'page-templates/contacts.php' )

	->add_tab( 'Контакты', [
		Field::make( 'html', 'arctik_contacts_info' )
			->set_html( '<p>Заголовок — название страницы. Телефон, соцсети, адрес и почта — «Настройки сайта → Контакты / Соцсети».</p>' ),
		Field::make( 'text', 'arctik_contacts_lead', 'Подпись над заголовком' ),
		Field::make( 'textarea', 'arctik_contacts_text', 'Текст под заголовком' )
			->set_rows( 2 )
			->set_help_text( 'Перенос строки — как в тексте' ),
		Field::make( 'image', 'arctik_contacts_photo', 'Фон' )
			->set_help_text( 'Широкий кадр, от 2880×1590' )
			->set_width( 34 ),
		Field::make( 'image', 'arctik_contacts_photo_tablet', 'Фон на планшете' )
			->set_help_text( 'Вертикальный, от 1086×1448. Пусто — общий фон' )
			->set_width( 33 ),
		Field::make( 'image', 'arctik_contacts_photo_mobile', 'Фон на мобилке' )
			->set_help_text( 'Широкий, от 2448×593. Пусто — общий фон' )
			->set_width( 33 ),
	] );
