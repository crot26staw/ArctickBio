<?php
/**
 * «Документы» (этап Кт3; 29.10.2026 — каждый документ своей страницей): общие поля страницы «Документы» и 6 дочерних
 * страниц-документов из scripts/seed/content/ с заглушками [УТОЧНИТЬ] (arctik_documents_save() в inc/documents.php темы):
 * privacy — политика конфиденциальности WP, oferta — страница условий WC; прежние страницы «Публичная оферта»
 * и «Политика конфиденциальности» удаляются. Страница и меню подвала — /seed/base.php (запустить раньше).
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/documents.php
 */

require_once __DIR__ . '/_lib.php';

$page = get_page_by_path( 'documents' );
if ( ! $page ) {
	WP_CLI::error( 'Нет страницы «Документы» — сначала /seed/base.php' );
}

update_post_meta( $page->ID, '_wp_page_template', 'page-templates/documents.php' );

// Порядок, названия в меню и ярлыки страниц — решение пользователя (WORDPRESS.md, Кт3); описания — Yoast.
$documents = [
	[ 'file' => 'ArcticBerries_public_offer.txt', 'menu' => '', 'slug' => 'oferta', 'description' => 'Публичная оферта интернет-магазина АрктикБиоМарин: условия дистанционной продажи, оформление заказа, оплата, доставка и возврат.' ],
	[ 'file' => '01_privacy_policy.txt', 'menu' => 'Политика конфиденциальности', 'slug' => 'privacy', 'description' => 'Политика обработки персональных данных интернет-магазина АрктикБиоМарин: какие данные собираются, зачем и как защищаются.' ],
	[ 'file' => '02_cookie_policy.txt', 'menu' => 'Политика cookie', 'slug' => 'cookie', 'description' => 'Политика использования cookie на сайте АрктикБиоМарин: какие файлы cookie используются и как управлять ими.' ],
	[ 'file' => '03_user_agreement.txt', 'menu' => '', 'slug' => 'agreement', 'description' => 'Пользовательское соглашение сайта АрктикБиоМарин: правила использования сайта и личного кабинета.' ],
	[ 'file' => '04_personal_data_consent.txt', 'menu' => 'Согласие на обработку данных', 'slug' => 'personal-data', 'description' => 'Согласие на обработку персональных данных покупателей интернет-магазина АрктикБиоМарин.' ],
	[ 'file' => '05_marketing_consent.txt', 'menu' => 'Согласие на рассылки', 'slug' => 'marketing', 'description' => 'Согласие на получение рекламных и информационных рассылок АрктикБиоМарин.' ],
];

$contacts = get_page_by_path( 'contacts' );

carbon_set_post_meta( $page->ID, 'arctik_documents_lead', "Условия покупки\nи работы с данными" );
carbon_set_post_meta( $page->ID, 'arctik_documents_bg', arctik_seed_file( 'buyers-bg.webp' ) );
carbon_set_post_meta( $page->ID, 'arctik_documents_note', sprintf(
	'Остались вопросы по документам? <a href="%s">Напишите</a> нам — ответим в кратчайшие сроки.',
	$contacts ? get_permalink( $contacts ) : '#'
) );

// Прежние страницы-заглушки: оферта (seed checkout.php) и политика конфиденциальности WP (если это не документ).
foreach ( [ get_page_by_path( 'oferta' ), get_post( (int) get_option( 'wp_page_for_privacy_policy' ) ) ] as $old ) {
	if ( $old && 'page' === $old->post_type && $old->ID !== $page->ID && $old->post_parent !== $page->ID ) {
		wp_delete_post( $old->ID, true );
	}
}

arctik_documents_save( $page->ID, array_map( function ( $document ) {
	$parsed = arctik_seed_document( $document['file'] );

	return [ 'slug' => $document['slug'], 'title' => $parsed['title'], 'menu' => $document['menu'], 'text' => $parsed['body'], 'description' => $document['description'] ];
}, $documents ) );

WP_CLI::success( 'Документы заполнены' );
