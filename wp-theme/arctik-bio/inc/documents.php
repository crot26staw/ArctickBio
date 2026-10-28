<?php
/**
 * «Документы» (этап Кт3): все юридические документы — на одной странице (page-templates/documents.php), по якорям.
 * Эта же страница выбрана страницей политики конфиденциальности WP и страницей условий WC (сид scripts/seed/documents.php).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ссылка на документ: страница «Документы» + якорь ('oferta', 'privacy', 'agreement'…). Страницы нет — '#'.
 */
function arctik_document_url( string $anchor ): string {
	static $page_id = null;

	if ( null === $page_id ) {
		$pages   = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/documents.php', 'number' => 1 ] );
		$page_id = $pages ? (int) $pages[0]->ID : 0;
	}

	return $page_id ? get_permalink( $page_id ) . '#' . $anchor : '#';
}

// Политика конфиденциальности WP (ссылки WP и WC) — к своему документу на странице «Документы».
add_filter( 'privacy_policy_url', function ( string $url, int $page_id ): string {
	if ( $url && 'page-templates/documents.php' === get_page_template_slug( $page_id ) ) {
		$url .= '#privacy';
	}

	return $url;
}, 10, 2 );
