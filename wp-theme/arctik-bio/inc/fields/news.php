<?php
/**
 * Новость (тип записи arctik_news): заголовок — название записи, анонс — «Отрывок», дата — дата публикации.
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'post_meta', 'Новость' )
	->where( 'post_type', '=', 'arctik_news' )

	->add_tab( 'Новость', [
		Field::make( 'html', 'arctik_news_info' )
			->set_html( '<p>Заголовок — название записи, текст — «Отрывок» ниже, дата — дата публикации. Новости в слайдере — по дате, новые первыми.</p>' ),
		Field::make( 'text', 'arctik_news_link', 'Ссылка' )
			->set_attribute( 'type', 'url' )
			->set_help_text( 'Куда ведёт заголовок (пост в соцсети, статья). Пусто — без ссылки' ),
		Field::make( 'image', 'arctik_news_image', 'Фото' )
			->set_help_text( 'От 1290×946' )
			->set_width( 50 ),
		Field::make( 'image', 'arctik_news_image_mobile', 'Фото на мобилке' )
			->set_help_text( 'Вертикальное, от 710×878. Пусто — общее фото' )
			->set_width( 50 ),
	] )

	->add_tab( 'Автор', [
		Field::make( 'image', 'arctik_news_author_photo', 'Фото' )
			->set_help_text( 'От 108×112' )
			->set_width( 34 ),
		Field::make( 'text', 'arctik_news_author', 'Имя' )
			->set_width( 33 ),
		Field::make( 'text', 'arctik_news_author_role', 'Должность' )
			->set_width( 33 ),
	] );
