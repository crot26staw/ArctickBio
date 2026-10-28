<?php
/**
 * «Документы» (этап Кт3): шаблон страницы и поля — 6 юридических документов из scripts/seed/content/ с заглушками
 * [УТОЧНИТЬ]; страница становится политикой конфиденциальности WP и страницей условий WC (оферта), прежние страницы
 * «Публичная оферта» и «Политика конфиденциальности» удаляются. Страница и меню подвала — /seed/base.php (запустить раньше).
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/documents.php
 */

require_once __DIR__ . '/_lib.php';

$page = get_page_by_path( 'documents' );
if ( ! $page ) {
	WP_CLI::error( 'Нет страницы «Документы» — сначала /seed/base.php' );
}

update_post_meta( $page->ID, '_wp_page_template', 'page-templates/documents.php' );

// Порядок, названия в меню и якоря — решение пользователя (WORDPRESS.md, Кт3).
$documents = [
	[ 'file' => 'ArcticBerries_public_offer.txt', 'menu' => '', 'anchor' => 'oferta' ],
	[ 'file' => '01_privacy_policy.txt', 'menu' => 'Политика конфиденциальности', 'anchor' => 'privacy' ],
	[ 'file' => '02_cookie_policy.txt', 'menu' => 'Политика cookie', 'anchor' => 'cookie' ],
	[ 'file' => '03_user_agreement.txt', 'menu' => '', 'anchor' => 'agreement' ],
	[ 'file' => '04_personal_data_consent.txt', 'menu' => 'Согласие на обработку данных', 'anchor' => 'personal-data' ],
	[ 'file' => '05_marketing_consent.txt', 'menu' => 'Согласие на рассылки', 'anchor' => 'marketing' ],
];

$contacts = get_page_by_path( 'contacts' );

carbon_set_post_meta( $page->ID, 'arctik_documents_lead', "Условия покупки\nи работы с данными" );
carbon_set_post_meta( $page->ID, 'arctik_documents_bg', arctik_seed_file( 'buyers-bg.webp' ) );
carbon_set_post_meta( $page->ID, 'arctik_documents_note', sprintf(
	'Остались вопросы по документам? <a href="%s">Напишите</a> нам — ответим в кратчайшие сроки.',
	$contacts ? get_permalink( $contacts ) : '#'
) );
carbon_set_post_meta( $page->ID, 'arctik_documents', array_map( function ( $document ) {
	$parsed = arctik_seed_document( $document['file'] );

	return [ 'title' => $parsed['title'], 'menu' => $document['menu'], 'anchor' => $document['anchor'], 'text' => $parsed['body'] ];
}, $documents ) );

// Прежние страницы-заглушки: оферта (seed checkout.php) и политика конфиденциальности WP.
foreach ( [ get_page_by_path( 'oferta' ), get_post( (int) get_option( 'wp_page_for_privacy_policy' ) ) ] as $old ) {
	if ( $old && 'page' === $old->post_type && $old->ID !== $page->ID ) {
		wp_delete_post( $old->ID, true );
	}
}

update_option( 'wp_page_for_privacy_policy', $page->ID );
update_option( 'woocommerce_terms_page_id', $page->ID );

WP_CLI::success( 'Документы заполнены' );
