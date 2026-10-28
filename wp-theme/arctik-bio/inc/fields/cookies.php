<?php
/**
 * «Настройки сайта → Cookie» (этап Ку): тексты баннера cookie и окна настроек (решение пользователя — отдельная вкладка).
 * Категории фиксированные (necessary / analytics / marketing — от них зависят скрипты), у каждой — название и описание.
 * Вывод — template-parts/cookies.php.
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

$arctik_cookie_category = static function ( string $key, string $title ): array {
	return [
		Field::make( 'separator', "arctik_cookie_{$key}_sep", $title ),
		Field::make( 'text', "arctik_cookie_{$key}_name", 'Название' )
			->set_width( 30 ),
		Field::make( 'textarea', "arctik_cookie_{$key}_text", 'Описание' )
			->set_rows( 3 )
			->set_width( 70 ),
	];
};

Container::make( 'theme_options', 'Cookie' )
	->set_page_parent( 'arctik-options' )
	->set_page_file( 'arctik-cookies' )

	->add_tab( 'Баннер', [
		Field::make( 'rich_text', 'arctik_cookie_banner_text', 'Текст полосы внизу экрана' )
			->set_settings( [ 'media_buttons' => false ] )
			->set_help_text( 'Одной строкой. Ссылка «Подробнее» — на раздел cookie страницы «Документы» (/documents/#cookie)' ),
	] )

	->add_tab( 'Окно настроек', array_merge(
		[
			Field::make( 'text', 'arctik_cookie_title', 'Заголовок' ),
			Field::make( 'rich_text', 'arctik_cookie_text', 'Текст' )
				->set_settings( [ 'media_buttons' => false ] )
				->set_help_text( 'Ссылки — например, на /documents/#cookie' ),
		],
		$arctik_cookie_category( 'necessary', 'Необходимые (всегда включены)' ),
		$arctik_cookie_category( 'analytics', 'Аналитика' ),
		$arctik_cookie_category( 'marketing', 'Маркетинг и реклама' )
	) );
