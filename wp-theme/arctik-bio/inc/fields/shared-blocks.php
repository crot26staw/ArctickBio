<?php
/**
 * Общие блоки (Настройки сайта → Общие блоки): секции с одинаковым содержимым на главной, в каталоге и карточке товара —
 * видео, «Рекомендуемый курс» (usage), «Регулярный приём помогает» (benefits), «Почему выбирают» (comparison).
 * Решение пользователя: контент хранится один раз здесь. Вкладка = секция.
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'theme_options', 'Общие блоки' )
	->set_page_parent( 'arctik-options' )
	->set_page_file( 'arctik-options-blocks' )

	->add_tab( 'Видео', [
		Field::make( 'textarea', 'arctik_video_title', 'Заголовок' )
			->set_rows( 2 )
			->set_help_text( 'Перенос строки — как в макете' ),
		Field::make( 'text', 'arctik_video_button', 'Подпись кнопки' )
			->set_help_text( 'Например «Смотреть (0:42)» — длительность полного видео' ),
		Field::make( 'file', 'arctik_video_preview', 'Превью — фон блока (mp4, без звука, по кругу)' )
			->set_type( [ 'video' ] )
			->set_width( 50 ),
		Field::make( 'image', 'arctik_video_poster', 'Картинка фона, пока превью грузится' )
			->set_help_text( '1920×1080' )
			->set_width( 50 ),
		Field::make( 'file', 'arctik_video_main', 'Полное видео (mp4, со звуком)' )
			->set_type( [ 'video' ] )
			->set_width( 50 ),
		Field::make( 'image', 'arctik_video_card', 'Карточка в углу (обложка полного видео)' )
			->set_help_text( 'Квадрат, от 900×900' )
			->set_width( 50 ),
	] )

	->add_tab( 'Рекомендуемый курс', [
		Field::make( 'textarea', 'arctik_usage_title', 'Заголовок' )
			->set_rows( 2 ),
		Field::make( 'image', 'arctik_usage_image', 'Большое фото' )
			->set_help_text( 'От 1268×1241' ),
		Field::make( 'complex', 'arctik_usage_badges', 'Плашки на фото' )
			->setup_labels( [ 'plural_name' => 'Плашки', 'singular_name' => 'плашку' ] )
			->add_fields( [
				Field::make( 'select', 'icon', 'Иконка' )
					->set_options( arctik_icon_options() )
					->set_width( 30 ),
				Field::make( 'text', 'text', 'Текст' )
					->set_width( 70 ),
			] )
			->set_header_template( '<%- text %>' ),
		Field::make( 'image', 'arctik_usage_glass_image', 'Карточка с фото: фото' )
			->set_width( 50 ),
		Field::make( 'textarea', 'arctik_usage_glass_text', 'Карточка с фото: текст' )
			->set_rows( 2 )
			->set_width( 50 ),
		Field::make( 'select', 'arctik_usage_time_icon', 'Карточка с иконкой: иконка' )
			->set_options( arctik_icon_options() )
			->set_width( 50 ),
		Field::make( 'textarea', 'arctik_usage_time_text', 'Карточка с иконкой: текст' )
			->set_rows( 2 )
			->set_width( 50 ),
	] )

	->add_tab( 'Регулярный приём', [
		Field::make( 'html', 'arctik_benefits_info' )
			->set_html( '<p>Фон блока — картинка темы (для эффекта стекла у карточек нужна её заранее размытая копия), меняется в вёрстке.</p>' ),
		Field::make( 'textarea', 'arctik_benefits_title', 'Заголовок' )
			->set_rows( 2 ),
		Field::make( 'complex', 'arctik_benefits_cards', 'Карточки' )
			->setup_labels( [ 'plural_name' => 'Карточки', 'singular_name' => 'карточку' ] )
			->add_fields( [
				Field::make( 'select', 'icon', 'Иконка' )
					->set_options( arctik_icon_options() )
					->set_width( 30 ),
				Field::make( 'textarea', 'title', 'Текст' )
					->set_rows( 2 )
					->set_width( 70 ),
			] )
			->set_header_template( '<%- title %>' ),
	] )

	->add_tab( 'Почему выбирают', [
		Field::make( 'textarea', 'arctik_comparison_title', 'Заголовок' )
			->set_rows( 2 ),
		Field::make( 'image', 'arctik_comparison_image', 'Фото слева' )
			->set_help_text( 'От 1077×1461' )
			->set_width( 50 ),
		Field::make( 'image', 'arctik_comparison_pack', 'Пачка в шапке таблицы' )
			->set_help_text( 'От 160×120' )
			->set_width( 50 ),
		Field::make( 'text', 'arctik_comparison_head_feature', 'Шапка: первая колонка' )
			->set_width( 50 ),
		Field::make( 'text', 'arctik_comparison_head_other', 'Шапка: другой продукт' )
			->set_width( 50 ),
		Field::make( 'complex', 'arctik_comparison_rows', 'Строки таблицы' )
			->setup_labels( [ 'plural_name' => 'Строки', 'singular_name' => 'строку' ] )
			->add_fields( [
				Field::make( 'text', 'feature', 'Преимущество' ),
				Field::make( 'textarea', 'ours', 'АрктикБиоМарин' )
					->set_rows( 2 )
					->set_width( 50 ),
				Field::make( 'textarea', 'other', 'Другой продукт' )
					->set_rows( 2 )
					->set_width( 50 ),
			] )
			->set_header_template( '<%- feature %>' ),
	] );
