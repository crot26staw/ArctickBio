<?php
/**
 * Настройки сайта (Theme Options): общее для всех страниц — контакты, соцсети, подвал.
 * Один набор контактов на весь сайт (решение пользователя): шапка, мобильное меню, подвал, «Контакты».
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'theme_options', 'Настройки сайта' )
	->set_page_file( 'arctik-options' )
	->set_icon( 'dashicons-admin-generic' )
	->set_page_menu_position( 59 )

	->add_tab( 'Контакты', [
		Field::make( 'text', 'arctik_phone', 'Телефон' )
			->set_help_text( 'В любом виде, например +7 921 153 3553 — ссылка для звонка собирается автоматически' ),
		Field::make( 'text', 'arctik_email', 'Почта' ),
		Field::make( 'textarea', 'arctik_address', 'Адрес' )
			->set_rows( 2 ),
		Field::make( 'text', 'arctik_question_link', 'Ссылка кнопки «Задать вопрос»' )
			->set_help_text( 'Мобильное меню. Например, ссылка на мессенджер или mailto:' ),
	] )

	->add_tab( 'Соцсети', [
		Field::make( 'complex', 'arctik_socials', 'Соцсети' )
			->set_help_text( 'Шапка (мобильное меню) и подвал. Порядок — перетаскиванием' )
			->setup_labels( [
				'plural_name'   => 'Соцсети',
				'singular_name' => 'соцсеть',
			] )
			->add_fields( [
				Field::make( 'select', 'network', 'Соцсеть' )
					->set_options( [
						'vk'       => 'ВКонтакте',
						'max'      => 'MAX',
						'inst'     => 'Instagram',
						'telegram' => 'Telegram',
					] )
					->set_width( 30 ),
				Field::make( 'text', 'url', 'Ссылка' )
					->set_attribute( 'type', 'url' )
					->set_width( 70 ),
			] )
			->set_header_template( '<%- network %>' ),
	] )

	->add_tab( 'Подвал', [
		Field::make( 'text', 'arctik_footer_question_title', 'Заголовок колонки контактов' ),
		Field::make( 'textarea', 'arctik_footer_question_text', 'Текст колонки контактов' )
			->set_rows( 2 ),
		Field::make( 'text', 'arctik_copyright', 'Копирайт' )
			->set_help_text( 'Без «©» и года — они подставляются автоматически' ),
		Field::make( 'textarea', 'arctik_disclaimer', 'Предупреждение внизу' )
			->set_rows( 2 ),
		Field::make( 'text', 'arctik_developer_link', 'Ссылка на разработчика (Alta)' )
			->set_attribute( 'type', 'url' ),
	] );
