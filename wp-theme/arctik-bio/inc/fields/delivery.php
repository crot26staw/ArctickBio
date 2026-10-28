<?php
/**
 * Доставка (Настройки сайта → Доставка): доступы СДЭК, город отправки, тарифы, упаковка по умолчанию, ключи карт и подсказок.
 * Используются в inc/cdek.php (API СДЭК), inc/shipping.php (стоимость и срок), попапах пункта выдачи и адреса.
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'theme_options', 'Доставка' )
	->set_page_parent( 'arctik-options' )
	->set_page_file( 'arctik-options-delivery' )

	->add_tab( 'СДЭК', [
		Field::make( 'checkbox', 'arctik_cdek_test', 'Тестовый режим' )
			->set_option_value( 'yes' )
			->set_default_value( 'yes' )
			->set_help_text( 'Запросы идут в тестовую среду СДЭК (api.edu.cdek.ru) — заказы не настоящие. Пустые доступы в тестовом режиме — общий тестовый аккаунт СДЭК' ),
		Field::make( 'text', 'arctik_cdek_client_id', 'Идентификатор клиента (Account)' )
			->set_width( 50 )
			->set_help_text( 'Личный кабинет СДЭК → Интеграция → Ключи API' ),
		Field::make( 'text', 'arctik_cdek_client_secret', 'Секретный ключ (Secure password)' )
			->set_attribute( 'type', 'password' )
			->set_width( 50 ),
		Field::make( 'text', 'arctik_cdek_from_city', 'Город отправки — код СДЭК' )
			->set_attribute( 'type', 'number' )
			->set_default_value( '44' )
			->set_width( 34 )
			->set_help_text( 'Откуда отправляются заказы. 44 — Москва, 137 — Санкт-Петербург; код города — в справочнике СДЭК' ),
		Field::make( 'text', 'arctik_cdek_tariff_pickup', 'Тариф до пункта выдачи' )
			->set_attribute( 'type', 'number' )
			->set_default_value( '136' )
			->set_width( 33 )
			->set_help_text( '136 — «Посылка склад-склад», 138 — «дверь-склад» (если СДЭК забирает со склада)' ),
		Field::make( 'text', 'arctik_cdek_tariff_courier', 'Тариф курьером' )
			->set_attribute( 'type', 'number' )
			->set_default_value( '137' )
			->set_width( 33 )
			->set_help_text( '137 — «Посылка склад-дверь», 139 — «дверь-дверь»' ),
		Field::make( 'separator', 'arctik_cdek_package_sep', 'Упаковка по умолчанию' )
			->set_help_text( 'Для товаров без веса и габаритов (WooCommerce → товар → Доставка). Одна единица товара' ),
		Field::make( 'text', 'arctik_cdek_weight', 'Вес, г' )
			->set_attribute( 'type', 'number' )
			->set_default_value( '300' )
			->set_width( 25 ),
		Field::make( 'text', 'arctik_cdek_length', 'Длина, см' )
			->set_attribute( 'type', 'number' )
			->set_default_value( '15' )
			->set_width( 25 ),
		Field::make( 'text', 'arctik_cdek_width', 'Ширина, см' )
			->set_attribute( 'type', 'number' )
			->set_default_value( '10' )
			->set_width( 25 ),
		Field::make( 'text', 'arctik_cdek_height', 'Высота, см' )
			->set_attribute( 'type', 'number' )
			->set_default_value( '8' )
			->set_width( 25 ),
	] )

	->add_tab( 'Карты и подсказки', [
		Field::make( 'text', 'arctik_yandex_maps_key', 'Ключ Яндекс Карт' )
			->set_help_text( 'Кабинет разработчика Яндекса → «JavaScript API и HTTP Геокодер». Карта в попапе пункта выдачи' ),
		Field::make( 'text', 'arctik_dadata_key', 'Ключ DaData' )
			->set_help_text( 'dadata.ru → Профиль → API-ключ. Подсказки адреса для курьера' ),
	] );
