<?php
/**
 * Отзыв (тип записи arctik_review): заголовок отзыва — название записи, дата — дата публикации.
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'post_meta', 'Отзыв' )
	->where( 'post_type', '=', 'arctik_review' )

	->add_fields( [
		Field::make( 'html', 'arctik_review_info' )
			->set_html( '<p>Заголовок отзыва («Очень вкусно!») — название записи, дата — дата публикации. На главной — по дате, новые первыми.</p>' ),
		Field::make( 'text', 'arctik_review_name', 'Имя' )
			->set_width( 50 ),
		Field::make( 'select', 'arctik_review_rating', 'Оценка' )
			->set_options( [ '5' => '5', '4' => '4', '3' => '3', '2' => '2', '1' => '1' ] )
			->set_width( 50 ),
		Field::make( 'textarea', 'arctik_review_text', 'Текст' )
			->set_rows( 6 ),
		Field::make( 'association', 'arctik_review_product', 'Товар' )
			->set_types( [ [ 'type' => 'post', 'post_type' => 'product' ] ] )
			->set_max( 1 )
			->set_help_text( 'Внизу карточки: пачка и «Заголовок / подзаголовок мини-карточки» товара. Пусто — без товара' ),
	] );
