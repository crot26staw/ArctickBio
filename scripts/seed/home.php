<?php
/**
 * Наполнение главной (этапы Г1, Г2): страница «Главная» как главная сайта, поля hero, «О бренде», «Каталог», «Состав», «Исследования» — тексты, фото и видео из статики.
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

// «Состав»: в статике у K, H, PP, A двойной пробел в названии — здесь одинарный.
carbon_set_post_meta( $id, 'arctik_home_compound_title', 'Показываем состав полностью' );
carbon_set_post_meta( $id, 'arctik_home_compound_stats', [
	[ 'amount' => '98%', 'unit' => '', 'label' => "коллагена\nв сухом веществе", 'notes' => '18 аминокислот в составе' ],
	[ 'amount' => '5 000', 'unit' => 'мг', 'label' => 'коллагена', 'notes' => "5 грамм коллагена\n5 грамм натуральных ягод" ],
] );
$vitamins = [
	'c'  => 'Поступает из цельных северных ягод. Выступает кофактором синтеза собственного коллагена организмом',
	'e'  => 'Защищает клетки от окислительного стресса. Помогает сохранять целостность клеточных мембран',
	'k'  => 'Участвует в процессе нормального свертывания крови. Способствует поддержанию здоровья костной ткани',
	'h'  => 'Участвует в обмене белков и жиров. Способствует поддержанию нормального состояния кожи и волос',
	'pp' => 'Участвует в энергетическом обмене клеток. Помогает поддерживать нормальное состояние кожи и нервной системы',
	'a'  => 'Способствует обновлению клеток кожи. Поддерживает нормальное состояние слизистых оболочек и зрения',
];
carbon_set_post_meta( $id, 'arctik_home_compound_vitamins', array_map( fn( $key, $text ) => [
	'name'  => 'Витамин ' . ( 'c' === $key ? 'С' : strtoupper( $key ) ),
	'image' => arctik_seed_file( "vitamin-{$key}.webp" ),
	'text'  => $text,
], array_keys( $vitamins ), $vitamins ) );

// «Исследования»: ссылок на PDF в статике нет (#) — документы без файлов.
carbon_set_post_meta( $id, 'arctik_home_research_title', 'Продукт исследован ФИЦ Биотехнологии РАН' );
carbon_set_post_meta( $id, 'arctik_home_research_image', arctik_seed_file( 'research.webp' ) );
carbon_set_post_meta( $id, 'arctik_home_research_items', [
	[ 'label' => '98% коллагеновой основы', 'prefix' => '≈', 'number' => '98', 'suffix' => '%', 'title' => 'коллагена в сухом веществе', 'text' => 'В сухом веществе преобладает белковая часть — вы получаете продукт с высокой долей коллагенового материала, а не наполнителей', 'image' => arctik_seed_file( 'research-sample.webp' ) ],
	[ 'label' => '> 57% пептидов — 1–5 кДа', 'prefix' => '>', 'number' => '57', 'suffix' => '%', 'title' => 'пептидов в диапазоне 1–5 кДа', 'text' => "Небольшие пептидные цепочки легче усваиваются организмом\nпо сравнению с цельным белком", 'image' => arctik_seed_file( 'research-peptides.webp' ) ],
	[ 'label' => '18 аминокислот в составе', 'prefix' => '', 'number' => '18', 'suffix' => '', 'title' => 'аминокислот в составе', 'text' => 'Аминокислоты — основа коллагена. 18 аминокислот в составе поддерживают кожу, суставы и связки', 'image' => arctik_seed_file( 'research-amino.webp' ) ],
] );
carbon_set_post_meta( $id, 'arctik_home_research_docs_label', 'Протоколы испытаний продукции' );
carbon_set_post_meta( $id, 'arctik_home_research_docs', [
	[ 'title' => "Аминокислот-\nный профиль", 'file' => '' ],
	[ 'title' => 'Протоколы испытаний продукции', 'file' => '' ],
	[ 'title' => 'Отчет РАН', 'file' => '' ],
] );

WP_CLI::success( "Главная заполнена (страница {$id})" );
