<?php
/**
 * Наполнение «О компании» (этап «О»): шаблон и поля страницы, общий блок «Процесс» (он же на главной), новости —
 * тексты, картинки и видео из статики.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/about.php
 */

require_once __DIR__ . '/_lib.php';

$page = get_page_by_path( 'about' );
if ( ! $page ) {
	WP_CLI::error( 'Нет страницы «О компании» — сначала /seed/base.php' );
}

update_post_meta( $page->ID, '_wp_page_template', 'page-templates/about.php' );

carbon_set_post_meta( $page->ID, 'arctik_about_hero_title', 'Процесс производства' );
carbon_set_post_meta( $page->ID, 'arctik_about_hero_video', arctik_seed_file( 'video/company-hero.mp4' ) );
carbon_set_post_meta( $page->ID, 'arctik_about_hero_photo', arctik_seed_file( 'company-hero.webp' ) );
carbon_set_post_meta( $page->ID, 'arctik_about_hero_photo_mobile', arctik_seed_file( 'company-hero-mobile.webp' ) );

carbon_set_post_meta( $page->ID, 'arctik_about_intro_title', 'Сделано людьми Севера' );
carbon_set_post_meta( $page->ID, 'arctik_about_intro_lead', 'На Кольском полуострове есть всё для создания коллагена. Чистое сырьё и люди, которые понимают, что такое качество' );
carbon_set_post_meta( $page->ID, 'arctik_about_intro_text', 'Мы решили контролировать полный цикл, потому что только так можно гарантировать, что продукт не потеряет свою природную силу. Мы не просто производим коллаген — мы показываем, как он рождается.' );
carbon_set_post_meta( $page->ID, 'arctik_about_intro_photo_small', arctik_seed_file( 'company-about-small.webp', 'Оборудование цеха производства коллагена' ) );
carbon_set_post_meta( $page->ID, 'arctik_about_intro_photo', arctik_seed_file( 'company-about.webp', 'Сотрудники в цеху производства' ) );
carbon_set_post_meta( $page->ID, 'arctik_about_intro_photo_tablet', arctik_seed_file( 'company-about-tablet.webp', 'Сотрудники в цеху производства' ) );

$names = [ 'Алексей Волков', 'Конанова Мария', 'Волков Алексей', 'Афанасьев Владислав', 'Афанасьев Дмитрий', 'Осолодкин Андрей', 'Афанасьев Даниил', 'Думанская Людмила', 'Волкова Элина', 'Волков Глеб' ];
carbon_set_post_meta( $page->ID, 'arctik_team_title', 'Реальные сотрудники производства' );
carbon_set_post_meta( $page->ID, 'arctik_team', array_map( fn( $name, $i ) => [
	'photo' => arctik_seed_file( 'team-' . ( $i + 1 ) . '.webp' ),
	'name'  => $name,
	'job'   => 'Технолог',
	'exp'   => '5 лет в пищевом производстве',
], $names, array_keys( $names ) ) );

carbon_set_post_meta( $page->ID, 'arctik_media_title', 'Больше процессов производства показываем в соцсетях' );
carbon_set_post_meta( $page->ID, 'arctik_news_title', 'Новости' );

// «Процесс изготовления» — «Общие блоки → Процесс». У шага 10 видео нет (картинка попапа), шаг 14 — фото шага 1.
$desc  = 'Для производства коллагена используем атлантическую треску из Баренцева моря.';
$steps = [
	1 => "Хранение сырья\nи ягод", 'Дефростация', 'Сортировка', 'Измельчение', 'Подготовка', 'Гидролиз', 'Фильтрация', 'Ферментация',
	'Подготовка', 'Сушилка', 'Смешивание', 'Фасовка', 'Пастеризация', 'Охлаждение', 'Упаковка', 'Хранение',
];
$rows = [];
foreach ( $steps as $num => $title ) {
	$rows[] = [
		'title'        => $title,
		'image'        => arctik_seed_file( 'production-' . ( 14 === $num ? 1 : $num ) . '.webp' ),
		'popup_title'  => '',
		'popup_text'   => $desc,
		'popup_video'  => 10 === $num ? 0 : arctik_seed_file( "video/production/step-{$num}.mp4" ),
		'popup_poster' => 10 === $num ? arctik_seed_file( 'production-popup.webp' ) : 0,
	];
}
carbon_set_theme_option( 'arctik_production_title', "Процесс\nизготовления" );
carbon_set_theme_option( 'arctik_production_steps', $rows );

// Новости: в статике одна новость повторена 3 раза — так и заносим (метка _arctik_seed — без дублей при повторном запуске).
$existing = get_posts( [ 'post_type' => 'arctik_news', 'post_status' => 'any', 'meta_key' => '_arctik_seed', 'fields' => 'ids', 'numberposts' => -1 ] );
for ( $i = count( $existing ); $i < 3; $i++ ) {
	$existing[] = wp_insert_post( [
		'post_type'    => 'arctik_news',
		'post_status'  => 'publish',
		'post_title'   => 'Представили свою продукцию в Российско-Китайском ЭКСПО, в Харбине',
		'post_excerpt' => 'Наш коллаген — на международной арене! Приняли участие в Российско-Китайском ЭКСПО в Харбине и представили свою продукцию гостям и партнерам из двух стран',
		'post_date'    => '2026-05-18 12:0' . $i . ':00',
		'meta_input'   => [ '_arctik_seed' => 1 ],
	] );
}
foreach ( $existing as $news ) {
	carbon_set_post_meta( $news, 'arctik_news_link', '' );
	carbon_set_post_meta( $news, 'arctik_news_image', arctik_seed_file( 'news-1.webp' ) );
	carbon_set_post_meta( $news, 'arctik_news_image_mobile', arctik_seed_file( 'news-1-mobile.webp' ) );
	carbon_set_post_meta( $news, 'arctik_news_author_photo', arctik_seed_file( 'news-author.webp' ) );
	carbon_set_post_meta( $news, 'arctik_news_author', 'Алина Дмитриева' );
	carbon_set_post_meta( $news, 'arctik_news_author_role', 'CEO' );
}

WP_CLI::success( 'О компании заполнено' );
