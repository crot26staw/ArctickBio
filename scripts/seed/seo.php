<?php
/**
 * SEO (Yoast SEO): название сайта, разделитель, заголовки и описания страниц и товаров — тексты <title> / description из статики.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/seo.php
 */

require_once __DIR__ . '/_lib.php';

if ( ! defined( 'WPSEO_VERSION' ) ) {
	WP_CLI::error( 'Yoast SEO не активен' );
}

// Бренд в статике — «АрктикБиоМарин» (заголовки всех страниц)
update_option( 'blogname', 'АрктикБиоМарин' );

// «Страница — АрктикБиоМарин»: длинное тире, как в статике
WPSEO_Options::set( 'separator', 'sc-mdash' );
// Товары — полное название (h1 карточки, переменная из inc/seo.php): «Премиальный морской коллаген с дикой черникой — АрктикБиоМарин»
WPSEO_Options::set( 'title-product', '%%arctik_full_title%% %%sep%% %%sitename%%' );

$pages = [
	(int) get_option( 'page_on_front' ) => [
		'АрктикБиоМарин — морской коллаген из дикой трески Баренцева моря',
		'АрктикБиоМарин — природный морской коллаген из дикой трески Баренцева моря с северными ягодами. Поддержка кожи, суставов и волос.',
	],
	(int) ( get_page_by_path( 'about' )->ID ?? 0 )    => [
		'',
		'АрктикБиоМарин — производство морского коллагена на Кольском полуострове: полный цикл, процесс изготовления и люди, которые делают продукт.',
	],
	wc_get_page_id( 'shop' )                          => [
		'',
		'Каталог Arctic Berries: морской коллаген из дикой трески Баренцева моря с северными ягодами — черникой, брусникой, голубикой, клюквой и морошкой.',
	],
	(int) ( get_page_by_path( 'buyers' )->ID ?? 0 )   => [
		'',
		'Ответы на частые вопросы о коллагене Arctic Berries, доставке, оплате и возврате.',
	],
	(int) ( get_page_by_path( 'contacts' )->ID ?? 0 ) => [
		'',
		'Контакты АрктикБиоМарин: телефон, почта, адрес производства и мессенджеры.',
	],
];

foreach ( $pages as $id => [ $title, $description ] ) {
	if ( ! $id ) {
		continue;
	}
	if ( $title ) {
		update_post_meta( $id, '_yoast_wpseo_title', $title );
	}
	update_post_meta( $id, '_yoast_wpseo_metadesc', $description );
}

// Товары-вкусы: описание как у черники в статике, с ягодой товара (из полного названия «… с дикой черникой»)
foreach ( wc_get_products( [ 'type' => 'variable', 'limit' => -1 ] ) as $product ) {
	$berry = preg_replace( '/^.*\sс\s/u', '', arctik_product_full_title( $product ) );
	update_post_meta( $product->get_id(), '_yoast_wpseo_metadesc', "Премиальный морской коллаген Arctic Berries с {$berry}: пептиды дикой трески Баренцева моря и витамины C, E, K, H, PP, A. 15 или 30 саше." );
}

WP_CLI::success( 'SEO заполнено' );
