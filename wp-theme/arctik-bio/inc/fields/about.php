<?php
/**
 * Страница «О компании» (шаблон page-templates/about.php). «Процесс» — «Общие блоки», ссылки соцсетей — «Настройки сайта → Соцсети»,
 * новости — записи «Новости».
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'post_meta', 'О компании' )
	->where( 'post_template', '=', 'page-templates/about.php' )

	->add_tab( 'Первый экран', [
		Field::make( 'text', 'arctik_about_hero_title', 'Заголовок' )
			->set_help_text( 'Пусто — название страницы' ),
		Field::make( 'file', 'arctik_about_hero_video', 'Видео (mp4, без звука, по кругу)' )
			->set_type( [ 'video' ] )
			->set_help_text( '1920×1080. Пусто — только фото' ),
		Field::make( 'image', 'arctik_about_hero_photo', 'Фото' )
			->set_help_text( 'Видно, пока видео грузится. От 2880×1400' )
			->set_width( 50 ),
		Field::make( 'image', 'arctik_about_hero_photo_mobile', 'Фото на мобилке' )
			->set_help_text( 'Вертикальное, от 750×1400. Пусто — общее фото' )
			->set_width( 50 ),
	] )

	->add_tab( 'Сделано людьми Севера', [
		Field::make( 'text', 'arctik_about_intro_title', 'Заголовок' ),
		Field::make( 'textarea', 'arctik_about_intro_lead', 'Подзаголовок' )
			->set_rows( 3 ),
		Field::make( 'textarea', 'arctik_about_intro_text', 'Текст' )
			->set_rows( 3 )
			->set_help_text( 'На планшете и мобилке скрыт' ),
		Field::make( 'image', 'arctik_about_intro_photo_small', 'Маленькое фото' )
			->set_help_text( 'От 500×640. На планшете и мобилке скрыто' )
			->set_width( 34 ),
		Field::make( 'image', 'arctik_about_intro_photo', 'Большое фото' )
			->set_help_text( 'От 1290×1320' )
			->set_width( 33 ),
		Field::make( 'image', 'arctik_about_intro_photo_tablet', 'Большое фото на планшете и мобилке' )
			->set_help_text( 'От 1456×1200. Пусто — общее фото' )
			->set_width( 33 ),
	] )

	->add_tab( 'Сотрудники', [
		Field::make( 'text', 'arctik_team_title', 'Заголовок' ),
		Field::make( 'complex', 'arctik_team', 'Сотрудники' )
			->setup_labels( [ 'plural_name' => 'Сотрудники', 'singular_name' => 'сотрудника' ] )
			->set_collapsed( true )
			->set_header_template( '<%- name %>' )
			->add_fields( [
				Field::make( 'image', 'photo', 'Фото' )
					->set_help_text( 'От 736×920' )
					->set_width( 25 ),
				Field::make( 'text', 'name', 'Имя' )
					->set_width( 25 ),
				Field::make( 'text', 'job', 'Должность' )
					->set_width( 25 ),
				Field::make( 'text', 'exp', 'Опыт' )
					->set_help_text( '«5 лет в пищевом производстве»' )
					->set_width( 25 ),
			] ),
	] )

	->add_tab( 'Соцсети', [
		Field::make( 'html', 'arctik_media_info' )
			->set_html( '<p>Карточки — соцсети из «Настройки сайта → Соцсети», в том же порядке.</p>' ),
		Field::make( 'textarea', 'arctik_media_title', 'Заголовок' )
			->set_rows( 2 ),
		Field::make( 'image', 'arctik_media_bg', 'Фон' )
			->set_help_text( 'Фото на весь блок, от 1920 px по ширине. Размытая копия для стекла карточек делается автоматически. Пусто — фон из вёрстки' )
			->set_width( 50 ),
		Field::make( 'image', 'arctik_media_bg_tablet', 'Фон на планшете и мобилке' )
			->set_help_text( 'Вертикальное фото, от 1536 px по ширине. Пусто — основной фон' )
			->set_width( 50 ),
	] )

	->add_tab( 'Новости', [
		Field::make( 'html', 'arctik_news_section_info' )
			->set_html( '<p>Сами новости — в разделе «Новости» в меню админки. Нет новостей — блок не выводится.</p>' ),
		Field::make( 'text', 'arctik_news_title', 'Заголовок' ),
	] );
