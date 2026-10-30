<?php
/**
 * Чеки (Настройки сайта → Чеки): облачная касса LIFE PAY — доступы API, тестовый режим, налоги.
 * Используются в inc/receipt.php.
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'theme_options', 'Чеки' )
	->set_page_parent( 'arctik-options' )
	->set_page_file( 'arctik-options-receipt' )

	->add_tab( 'LIFE PAY', [
		Field::make( 'checkbox', 'arctik_receipt_enabled', 'Отправлять чеки' )
			->set_option_value( 'yes' )
			->set_help_text( 'Чек прихода — когда заказ оплачен, чек возврата — когда заказ переведён в «Возвращён». Покупателю чек приходит на email' ),
		Field::make( 'checkbox', 'arctik_receipt_test', 'Тестовый режим' )
			->set_option_value( 'yes' )
			->set_default_value( 'yes' )
			->set_help_text( 'LIFE PAY проверяет данные чека, но не пробивает его и не отправляет в налоговую' ),
		Field::make( 'text', 'arctik_receipt_login', 'Логин' )
			->set_width( 50 )
			->set_help_text( 'Телефон, с которым входите в кабинет LIFE PAY, в любом виде' ),
		Field::make( 'text', 'arctik_receipt_apikey', 'API-ключ' )
			->set_attribute( 'type', 'password' )
			->set_width( 50 )
			->set_help_text( 'Кабинет LIFE PAY → Настройки → Разработчикам' ),
		Field::make( 'select', 'arctik_receipt_tax_system', 'Система налогообложения' )
			->set_options( [
				'usn6'  => 'УСН «доходы»',
				'usn15' => 'УСН «доходы минус расходы»',
				'osn'   => 'ОСН',
			] )
			->set_default_value( 'usn6' )
			->set_width( 50 ),
		Field::make( 'select', 'arctik_receipt_tax', 'НДС' )
			->set_options( [
				'vat5'  => 'НДС 5%',
				'vat7'  => 'НДС 7%',
				'vat22' => 'НДС 22%',
				'none'  => 'Без НДС',
			] )
			->set_default_value( 'vat5' )
			->set_width( 50 )
			->set_help_text( 'У всех позиций чека, включая доставку' ),
	] );
