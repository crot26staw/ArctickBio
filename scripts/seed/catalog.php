<?php
/**
 * Наполнение каталога (этап «К»): поля страницы «Каталог» и общие блоки (видео, курс, преимущества, сравнение) —
 * тексты и картинки из статики.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/catalog.php
 */

require_once __DIR__ . '/_lib.php';

// Страница «Каталог».
$shop = (int) wc_get_page_id( 'shop' );
$set  = get_page_by_path( 'nabor-na-3-mesyaca', OBJECT, 'product' );

carbon_set_post_meta( $shop, 'arctik_catalog_title', 'Arctic Berries' );
carbon_set_post_meta( $shop, 'arctik_catalog_hero_image', arctik_seed_file( 'catalog-hero.webp' ) );
carbon_set_post_meta( $shop, 'arctik_catalog_grid_title', 'Выберите свой Arctic Berries' );
carbon_set_post_meta( $shop, 'arctik_catalog_offer_title', "3 месяца\nпо особой цене" );
carbon_set_post_meta( $shop, 'arctik_catalog_offer_list', array_map( fn( $t ) => [ 'text' => $t ], [
	'любые вкусы на выбор',
	'подходит для первого знакомства',
	'специальная цена на набор',
] ) );
carbon_set_post_meta( $shop, 'arctik_catalog_offer_product', $set ? [ "post:product:{$set->ID}" ] : [] );
carbon_set_post_meta( $shop, 'arctik_catalog_offer_image', arctik_seed_file( 'catalog-offer.webp' ) );

// Общие блоки: видео.
carbon_set_theme_option( 'arctik_video_title', "Мы собираем ягоды\nв экологически чистых местах Кольского полуострова" );
carbon_set_theme_option( 'arctik_video_button', 'Смотреть (0:42)' );
carbon_set_theme_option( 'arctik_video_preview', arctik_seed_file( 'video/video-preview.mp4' ) );
carbon_set_theme_option( 'arctik_video_poster', arctik_seed_file( 'video-bg.webp' ) );
carbon_set_theme_option( 'arctik_video_main', arctik_seed_file( 'video/video-main.mp4' ) );
carbon_set_theme_option( 'arctik_video_card', arctik_seed_file( 'video-card.webp' ) );

// Рекомендуемый курс.
carbon_set_theme_option( 'arctik_usage_title', "Рекомендуемый курс\nне менее 3 мес" );
carbon_set_theme_option( 'arctik_usage_image', arctik_seed_file( 'usage-main.webp' ) );
carbon_set_theme_option( 'arctik_usage_badges', [
	[ 'icon' => 'sun', 'text' => 'Один раз в день' ],
	[ 'icon' => 'date', 'text' => 'Каждый день' ],
] );
carbon_set_theme_option( 'arctik_usage_glass_image', arctik_seed_file( 'usage-glass.webp' ) );
carbon_set_theme_option( 'arctik_usage_glass_text', 'содержимое пакета растворить в 100–150 мл воды или принять прямо из саше' );
carbon_set_theme_option( 'arctik_usage_time_icon', 'glass' );
carbon_set_theme_option( 'arctik_usage_time_text', 'выпить за 30–40 минут до приёма пищи' );

// Регулярный приём помогает поддерживать.
carbon_set_theme_option( 'arctik_benefits_title', "Регулярный приём помогает\nподдерживать" );
carbon_set_theme_option( 'arctik_benefits_cards', [
	[ 'icon' => 'skin', 'title' => "упругость\nи эластичность кожи" ],
	[ 'icon' => 'joints', 'title' => 'подвижность суставов' ],
	[ 'icon' => 'ligaments', 'title' => 'связки при физических нагрузках суставов' ],
	[ 'icon' => 'connective', 'title' => 'соединительную ткань при ежедневных нагрузках' ],
	[ 'icon' => 'hair-nails', 'title' => "здоровый рост волос\nи ногтей" ],
] );

// Почему выбирают.
carbon_set_theme_option( 'arctik_comparison_title', "Почему выбирают\nАрктикБиоМарин" );
carbon_set_theme_option( 'arctik_comparison_image', arctik_seed_file( 'comparison.webp', 'Премиальный морской коллаген с дикой черникой' ) );
carbon_set_theme_option( 'arctik_comparison_pack', arctik_seed_file( 'comparison-pack.webp', 'АрктикБиоМарин' ) );
carbon_set_theme_option( 'arctik_comparison_head_feature', 'Преимущество' );
carbon_set_theme_option( 'arctik_comparison_head_other', 'Другой продукт' );
carbon_set_theme_option( 'arctik_comparison_rows', [
	[ 'feature' => 'Происхождение сырья', 'ours' => "Дикая атлантическая треска\nБаренцева моря", 'other' => "Покупное сырьё, иногда говяжий\nили свиной коллаген вместо рыбного" ],
	[ 'feature' => 'Производство', 'ours' => 'Контроль качества на всех этапах производства', 'other' => 'Производственные этапы могут контролироваться разными компаниями' ],
	[ 'feature' => 'Исследования', 'ours' => "Аминокислотный профиль\nи молекулярная масса исследованы", 'other' => 'Лабораторные данные не всегда доступны покупателю' ],
	[ 'feature' => 'Состав', 'ours' => '5 г коллагена + 5 г настоящих ягод', 'other' => 'Нередко используются искусственные вкусовые добавки' ],
] );

WP_CLI::success( 'Каталог и общие блоки заполнены' );
