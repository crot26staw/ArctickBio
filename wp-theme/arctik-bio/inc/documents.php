<?php
/**
 * «Документы»: каждый юридический документ — своя страница (шаблон page-templates/document.php), дочерняя страница
 * «Документов» (page-templates/documents.php): /documents/oferta/, /documents/privacy/… Порядок в меню — «Порядок» страницы.
 * У «Документов» — общие поля (подпись, фон, подпись под меню), сама она ведёт на первый документ (301).
 * Страница privacy — политика конфиденциальности WP, oferta — страница условий WC.
 */

defined( 'ABSPATH' ) || exit;

const ARCTIK_DOCUMENTS_TEMPLATE = 'page-templates/documents.php';
const ARCTIK_DOCUMENT_TEMPLATE  = 'page-templates/document.php';

/**
 * Страница «Документы» (по шаблону). Нет — 0.
 */
function arctik_documents_page_id(): int {
	static $page_id = null;

	if ( null === $page_id ) {
		$pages   = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => ARCTIK_DOCUMENTS_TEMPLATE, 'number' => 1 ] );
		$page_id = $pages ? (int) $pages[0]->ID : 0;
	}

	return $page_id;
}

/**
 * Опубликованные документы — дочерние страницы «Документов», по порядку.
 *
 * @return WP_Post[]
 */
function arctik_documents(): array {
	$parent = arctik_documents_page_id();

	return $parent ? get_pages( [ 'parent' => $parent, 'sort_column' => 'menu_order,post_title' ] ) : [];
}

/**
 * Ссылка на документ по ярлыку страницы ('oferta', 'privacy', 'agreement'…). Нет такого — «Документы», нет и их — '#'.
 */
function arctik_document_url( string $slug ): string {
	foreach ( arctik_documents() as $document ) {
		if ( $document->post_name === $slug ) {
			return get_permalink( $document );
		}
	}

	$parent = arctik_documents_page_id();

	return $parent ? get_permalink( $parent ) : '#';
}

// «Документы» — на первый документ.
add_action( 'template_redirect', function () {
	if ( ! is_page() || ARCTIK_DOCUMENTS_TEMPLATE !== get_page_template_slug( get_queried_object_id() ) ) {
		return;
	}

	$documents = arctik_documents();
	if ( $documents ) {
		wp_safe_redirect( get_permalink( $documents[0] ), 301 );
		exit;
	}
} );

// Страница-редирект в карте сайта Yoast не нужна.
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function ( $ids ) {
	$parent = arctik_documents_page_id();

	return $parent ? array_merge( (array) $ids, [ $parent ] ) : $ids;
} );

/**
 * Создаёт или обновляет страницы документов под «Документами» и переводит на них ссылки WP, WC, меню и баннера cookie.
 * Сид scripts/seed/documents.php и разовый перенос со старой единой страницы (ниже).
 *
 * @param array $documents [ [ 'slug', 'title', 'menu', 'text', 'description' ], … ] — по порядку меню.
 */
function arctik_documents_save( int $parent, array $documents ): void {
	$parent_url = get_permalink( $parent );
	$links      = [];

	foreach ( array_values( $documents ) as $index => $document ) {
		$page = get_page_by_path( get_page_uri( $parent ) . '/' . $document['slug'] );
		$id   = wp_insert_post( [
			'ID'          => $page ? $page->ID : 0,
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_parent' => $parent,
			'post_title'  => $document['title'],
			'post_name'   => $document['slug'],
			'menu_order'  => $index,
		] );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}

		update_post_meta( $id, '_wp_page_template', ARCTIK_DOCUMENT_TEMPLATE );
		// Простые поля Carbon Fields хранятся как _<имя> — так их можно записать и без загруженных контейнеров
		update_post_meta( $id, '_arctik_document_menu', wp_slash( (string) $document['menu'] ) );
		update_post_meta( $id, '_arctik_document_text', wp_slash( (string) $document['text'] ) );
		if ( ! empty( $document['description'] ) ) {
			update_post_meta( $id, '_yoast_wpseo_metadesc', wp_slash( $document['description'] ) );
		}

		$links[ $parent_url . '#' . $document['slug'] ] = get_permalink( $id );

		if ( 'privacy' === $document['slug'] ) {
			update_option( 'wp_page_for_privacy_policy', $id );
		}
		if ( 'oferta' === $document['slug'] ) {
			update_option( 'woocommerce_terms_page_id', $id );
		}
	}

	if ( ! $links ) {
		return;
	}

	// Старые ссылки по якорям /documents/#oferta: пункты меню и тексты баннера cookie
	$items = get_posts( [ 'post_type' => 'nav_menu_item', 'numberposts' => -1, 'meta_key' => '_menu_item_url', 'meta_compare' => 'LIKE', 'meta_value' => $parent_url . '#' ] );
	foreach ( $items as $item ) {
		$url = (string) get_post_meta( $item->ID, '_menu_item_url', true );
		if ( isset( $links[ $url ] ) ) {
			update_post_meta( $item->ID, '_menu_item_url', $links[ $url ] );
		}
	}

	foreach ( [ '_arctik_cookie_banner_text', '_arctik_cookie_text' ] as $option ) {
		$value = get_option( $option );
		if ( is_string( $value ) && '' !== $value ) {
			update_option( $option, strtr( $value, $links ) );
		}
	}
}

/**
 * Разовый перенос (29.10.2026): документы из списка (complex arctik_documents) на странице «Документы» — в отдельные
 * страницы. На хостинге нет SSH, поэтому запускается сама тема при первом запросе после выгрузки. Список после переноса удаляется.
 */
add_action( 'init', function () {
	if ( get_option( 'arctik_documents_split' ) ) {
		return;
	}

	$parent = arctik_documents_page_id();
	$meta   = $parent ? get_post_meta( $parent ) : [];
	if ( ! isset( $meta['_arctik_documents|||0|value'] ) ) {
		update_option( 'arctik_documents_split', 1 );
		return;
	}

	// Защита от двух одновременных запросов: add_option не добавит уже существующую опцию
	if ( ! add_option( 'arctik_documents_split_lock', time(), '', false ) ) {
		return;
	}

	$value       = fn( string $field, int $index ): string => (string) ( $meta[ "_arctik_documents|{$field}|{$index}|0|value" ][0] ?? '' );
	$description = [
		'oferta'        => 'Публичная оферта интернет-магазина АрктикБиоМарин: условия дистанционной продажи, оформление заказа, оплата, доставка и возврат.',
		'privacy'       => 'Политика обработки персональных данных интернет-магазина АрктикБиоМарин: какие данные собираются, зачем и как защищаются.',
		'cookie'        => 'Политика использования cookie на сайте АрктикБиоМарин: какие файлы cookie используются и как управлять ими.',
		'agreement'     => 'Пользовательское соглашение сайта АрктикБиоМарин: правила использования сайта и личного кабинета.',
		'personal-data' => 'Согласие на обработку персональных данных покупателей интернет-магазина АрктикБиоМарин.',
		'marketing'     => 'Согласие на получение рекламных и информационных рассылок АрктикБиоМарин.',
	];

	$documents = [];
	for ( $index = 0; isset( $meta[ "_arctik_documents|||{$index}|value" ] ); $index++ ) {
		$slug = sanitize_title( $value( 'anchor', $index ) ) ?: 'document-' . ( $index + 1 );
		if ( '' === trim( $value( 'title', $index ) ) ) {
			continue;
		}

		$documents[] = [
			'slug'        => $slug,
			'title'       => $value( 'title', $index ),
			'menu'        => $value( 'menu', $index ),
			'text'        => $value( 'text', $index ),
			'description' => $description[ $slug ] ?? '',
		];
	}

	arctik_documents_save( $parent, $documents );

	global $wpdb;
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s", $parent, $wpdb->esc_like( '_arctik_documents|' ) . '%' ) );
	clean_post_cache( $parent );

	update_option( 'arctik_documents_split', 1 );
	delete_option( 'arctik_documents_split_lock' );
}, 20 );
