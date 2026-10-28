<?php
/**
 * Страница «Документы» (шаблон page-templates/documents.php): юридические документы с меню слева.
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'post_meta', 'Документы' )
	->where( 'post_template', '=', 'page-templates/documents.php' )

	->add_tab( 'Документы', [
		Field::make( 'html', 'arctik_documents_info' )
			->set_html( '<p>Заголовок — название страницы. Документ без текста не выводится. На документы ссылаются по якорю: /documents/#oferta — подвал, согласия при регистрации и оформлении (oferta, privacy, agreement).</p>' ),
		Field::make( 'textarea', 'arctik_documents_lead', 'Подпись над заголовком' )
			->set_rows( 2 )
			->set_help_text( 'Перенос строки — как в тексте' ),
		Field::make( 'image', 'arctik_documents_bg', 'Фон' )
			->set_help_text( 'Вертикальный, от 936×1024' ),
		Field::make( 'rich_text', 'arctik_documents_note', 'Подпись под меню документов' )
			->set_rows( 3 )
			->set_help_text( 'Одна строка, можно ссылку. Видна только на десктопе' ),
		Field::make( 'complex', 'arctik_documents', 'Документы' )
			->setup_labels( [ 'plural_name' => 'Документы', 'singular_name' => 'документ' ] )
			->set_collapsed( true )
			->set_header_template( '<%- title %>' )
			->add_fields( [
				Field::make( 'text', 'title', 'Заголовок' )
					->set_width( 34 ),
				Field::make( 'text', 'menu', 'Название в меню' )
					->set_help_text( 'Пусто — заголовок' )
					->set_width( 33 ),
				Field::make( 'text', 'anchor', 'Якорь' )
					->set_help_text( 'Латиницей, для ссылок вида /documents/#oferta. Пусто — document-<номер>' )
					->set_width( 33 ),
				Field::make( 'rich_text', 'text', 'Текст' )
					->set_help_text( 'Разделы документа — «Заголовок 3»' ),
			] ),
	] );
