<?php
/**
 * Контент товаров от заказчика (этап Кт1): тексты из content/ArcticBerries_products_content_clean.txt.
 * Названия (полное, мини-карточка), подписи форматов (описание вариации), «О продукте» (описание товара),
 * «Состав и пищевая ценность» — у товара; «Как принимать», «Доставка и хранение», документы — общие
 * («Настройки сайта → Товары», берутся у первого товара в файле). Старые «Преимущества» удаляются.
 * Каждая строка документа — абзац.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/content-products.php (после products.php; его вызывает и он сам).
 * Повторный запуск безопасен: товары не пересоздаются, меняются только тексты.
 */

require_once __DIR__ . '/_lib.php';

// Раздел документа (ягода в заголовке) → ярлык товара.
$arctik_slugs = [
	'МОРОШКОЙ'  => 'kollagen-moroshka',
	'ЧЕРНИКОЙ'  => 'kollagen-chernika',
	'КЛЮКВОЙ'   => 'kollagen-klyukva',
	'ГОЛУБИКОЙ' => 'kollagen-golubika',
	'БРУСНИКОЙ' => 'kollagen-brusnika',
	'НАБОР'     => 'nabor-na-3-mesyaca',
];

$arctik_accordions = [ 'О продукте', 'Состав и пищевая ценность', 'Как принимать', 'Доставка и хранение', 'Документы' ];

/**
 * Разбор раздела товара: [ 'mini_title', 'mini_subtitle', 'full_title', 'formats' => [ '15 саше' => подпись ], 'texts' => [ пункт => строки ] ].
 */
function arctik_seed_parse_product( string $text, array $accordions ): array {
	$data    = [ 'formats' => [], 'texts' => [] ];
	$current = null;
	$page    = false;

	foreach ( preg_split( '/\R/u', $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( 'СТРАНИЦА ТОВАРА' === $line ) {
			$page = true;
			continue;
		}
		if ( in_array( $line, $accordions, true ) ) {
			$current = $line;
			continue;
		}
		if ( $current ) {
			$data['texts'][ $current ][] = $line;
		} elseif ( preg_match( '/^Название: (.+)$/u', $line, $m ) ) {
			$data[ $page ? 'full_title' : 'mini_title' ] = $m[1];
		} elseif ( preg_match( '/^Подзаголовок: (.+)$/u', $line, $m ) ) {
			$data['mini_subtitle'] = $m[1];
		} elseif ( preg_match( '/^(\d+ саше) — (.+)$/u', $line, $m ) ) {
			$data['formats'][ $m[1] ] = $m[2];
		}
	}

	return $data;
}

$arctik_file     = file_get_contents( __DIR__ . '/content/ArcticBerries_products_content_clean.txt' );
$arctik_sections = preg_split( '/^═+\R(?=\d+\. )/mu', $arctik_file, -1, PREG_SPLIT_NO_EMPTY );
$arctik_common   = null;

foreach ( $arctik_sections as $arctik_section ) {
	$arctik_slug = null;
	foreach ( $arctik_slugs as $arctik_word => $arctik_candidate ) {
		if ( preg_match( '/^\d+\. .*' . $arctik_word . '/u', trim( $arctik_section ) ) ) {
			$arctik_slug = $arctik_candidate;
			break;
		}
	}
	$arctik_post = $arctik_slug ? get_page_by_path( $arctik_slug, OBJECT, 'product' ) : null;
	if ( ! $arctik_post ) {
		continue;
	}

	$arctik_data    = arctik_seed_parse_product( $arctik_section, $arctik_accordions );
	$arctik_texts   = $arctik_data['texts'];
	$arctik_product = wc_get_product( $arctik_post->ID );

	$arctik_product->set_description( implode( "\n\n", $arctik_texts['О продукте'] ?? [] ) );
	$arctik_product->save();

	carbon_set_post_meta( $arctik_post->ID, 'arctik_product_full_title', $arctik_data['full_title'] ?? '' );
	carbon_set_post_meta( $arctik_post->ID, 'arctik_product_mini_title', $arctik_data['mini_title'] ?? '' );
	carbon_set_post_meta( $arctik_post->ID, 'arctik_product_mini_subtitle', $arctik_data['mini_subtitle'] ?? '' );
	carbon_set_post_meta( $arctik_post->ID, 'arctik_product_composition', implode( "\n\n", $arctik_texts['Состав и пищевая ценность'] ?? [] ) );

	// Подписи форматов: «15 саше» — название термина pa_format вариации.
	foreach ( $arctik_product->get_children() as $arctik_child ) {
		$arctik_variation = wc_get_product( $arctik_child );
		$arctik_term      = get_term_by( 'slug', (string) ( $arctik_variation->get_attributes()['pa_format'] ?? '' ), 'pa_format' );
		if ( $arctik_term && isset( $arctik_data['formats'][ $arctik_term->name ] ) ) {
			$arctik_variation->set_description( $arctik_data['formats'][ $arctik_term->name ] );
			$arctik_variation->save();
		}
	}

	$arctik_common ??= $arctik_texts;
}

if ( $arctik_common ) {
	carbon_set_theme_option( 'arctik_products_usage', implode( "\n\n", $arctik_common['Как принимать'] ?? [] ) );
	carbon_set_theme_option( 'arctik_products_delivery', implode( "\n\n", $arctik_common['Доставка и хранение'] ?? [] ) );
	carbon_set_theme_option( 'arctik_products_documents', array_map(
		fn( $title ) => [ 'title' => $title, 'file' => '' ],
		$arctik_common['Документы'] ?? []
	) );
}

// «Преимущества» больше не выводятся — значения прежних полей удаляем.
global $wpdb;
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( '_arctik_products_benefits' ) . '%' ) );
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $wpdb->esc_like( '_arctik_product_benefits' ) . '%' ) );

WP_CLI::success( 'Тексты товаров заполнены из документа заказчика' );
