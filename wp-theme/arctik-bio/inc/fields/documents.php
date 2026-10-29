<?php
/**
 * «Документы» (шаблон page-templates/documents.php) — общие поля страниц документов;
 * документ (page-templates/document.php, дочерняя страница «Документов») — название в меню и текст.
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'post_meta', 'Документы' )
	->where( 'post_template', '=', 'page-templates/documents.php' )

	->add_tab( 'Документы', [
		Field::make( 'html', 'arctik_documents_info' )
			->set_html( '<p>Каждый документ — отдельная страница: дочерняя страница «Документов» с шаблоном «Документ» (адрес /documents/&lt;ярлык&gt;/). Порядок в меню — «Порядок» в атрибутах страницы. Сама страница «Документы» ведёт на первый документ. Ярлыки oferta, privacy, agreement, cookie — на них ссылаются подвал, согласия при регистрации и оформлении, баннер cookie.</p>' ),
		Field::make( 'textarea', 'arctik_documents_lead', 'Подпись над заголовком' )
			->set_rows( 2 )
			->set_help_text( 'Перенос строки — как в тексте' ),
		Field::make( 'image', 'arctik_documents_bg', 'Фон' )
			->set_help_text( 'Вертикальный, от 936×1024' ),
		Field::make( 'rich_text', 'arctik_documents_note', 'Подпись под меню документов' )
			->set_settings( [ 'media_buttons' => false ] )
			->set_rows( 3 )
			->set_help_text( 'Одна строка, можно ссылку. Видна только на десктопе' ),
	] );

Container::make( 'post_meta', 'Документ' )
	->where( 'post_template', '=', 'page-templates/document.php' )

	->add_tab( 'Документ', [
		Field::make( 'html', 'arctik_document_info' )
			->set_html( '<p>Заголовок документа — название страницы. Подпись над заголовком, фон и подпись под меню — общие, на странице «Документы».</p>' ),
		Field::make( 'text', 'arctik_document_menu', 'Название в меню' )
			->set_help_text( 'Пусто — заголовок' ),
		Field::make( 'rich_text', 'arctik_document_text', 'Текст' )
			->set_settings( [ 'media_buttons' => false ] )
			->set_help_text( 'Разделы документа — «Заголовок 3»' ),
	] );
