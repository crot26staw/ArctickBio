<?php
/**
 * Наполнение главной (этап Г1): страница «Главная» как главная сайта, поля hero, «О бренде», «Каталог» — тексты, фото и видео из статики.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/home.php
 */

require_once __DIR__ . '/_lib.php';

$page = get_page_by_path( 'home' );
$id   = $page ? $page->ID : wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Главная', 'post_name' => 'home' ] );

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $id );

carbon_set_post_meta( $id, 'arctik_home_hero_title', 'Коллаген из дикой трески' );
carbon_set_post_meta( $id, 'arctik_home_hero_title_accent', 'Баренцева моря' );
carbon_set_post_meta( $id, 'arctik_home_hero_text', 'АрктикБиоМарин® это поддержка кожи, суставов и волос, природным коллагеном без добавок' );
carbon_set_post_meta( $id, 'arctik_home_hero_video', arctik_seed_file( 'video/hero.mp4' ) );
carbon_set_post_meta( $id, 'arctik_home_hero_photo', arctik_seed_file( 'hero-preview.webp' ) );

carbon_set_post_meta( $id, 'arctik_home_about_title', "АрктикБиоМарин\nэто коллаген собственного производства" );
carbon_set_post_meta( $id, 'arctik_home_about_text', "Мы отвечаем за происхождение сырья, состав\nи каждый этап производства, поэтому можем\nне просить верить нам на слово,\nа показываем, как создаётся продукт" );
carbon_set_post_meta( $id, 'arctik_home_about_cards', [
	[ 'icon' => 'fruit', 'title' => "Высококачественные\nингредиенты", 'text' => "На вкус, как ягодный гель,\nа не как добавка. Ваш вкусный полезный ежедневный ритуал" ],
	[ 'icon' => 'absorption', 'title' => 'Создан для впитывания', 'text' => 'Низкомолекулярная форма легче усваивается по сравнению с цельным белком. Более 57% пептидов в диапазоне от 1 до 5 кДа' ],
	[ 'icon' => 'fish', 'title' => '100% морское сырьё', 'text' => "Используем только треску из чистого\nи холодного Баренцева моря" ],
] );

carbon_set_post_meta( $id, 'arctik_home_catalog_title', 'Выберите свой Arctic Berries' );

WP_CLI::success( "Главная заполнена (страница {$id})" );
