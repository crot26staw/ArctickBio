<?php
/**
 * Типограф: неразрывные пробелы в текстах из админки — по правилам из CLAUDE.md
 * (после коротких слов и предлогов, перед ли/же/бы и тире, между числом и словом, в разрядах чисел).
 * Вручную &nbsp; в поля не вписывать — расставляется здесь на выводе.
 */

defined( 'ABSPATH' ) || exit;

const ARCTIK_NBSP = "\u{00A0}";

/**
 * Расставляет неразрывные пробелы в тексте (HTML можно — теги и их атрибуты не трогаются).
 */
function arctik_typograph( ?string $html ): string {
	if ( null === $html || '' === $html ) {
		return '';
	}

	// Пробел — обычный или уже неразрывный (в т.ч. сущностью).
	$space = '(?:[ \t]|&nbsp;|&#160;|\x{00A0})+';

	$rules = [
		// После слов из 1–2 букв и коротких служебных слов.
		'/(?<![\p{L}\p{N}&#-])(\p{L}{1,2}|без|для|при|про|под|над|или|что|как)' . $space . '(?=[\p{L}\p{N}«"(])/iu' => '$1' . ARCTIK_NBSP,
		// Перед частицами ли / же / бы.
		'/' . $space . '(ли|же|бы)(?![\p{L}\p{N}])/iu' => ARCTIK_NBSP . '$1',
		// Перед тире.
		'/' . $space . '(—|–|&mdash;|&ndash;)/u' => ARCTIK_NBSP . '$1',
		// Разряды чисел: 5 000, 1 120.
		'/(\p{N})' . $space . '(?=\p{N}{3}(?!\p{N}))/u' => '$1' . ARCTIK_NBSP,
		// Число и слово / единица: 15 саше, 1440 ₽, 98 %.
		'/(\p{N})' . $space . '(?=[\p{L}₽%°])/u' => '$1' . ARCTIK_NBSP,
	];

	// Обрабатываем только текст между тегами; содержимое script/style не трогаем.
	$parts = preg_split( '/(<script\b.*?<\/script>|<style\b.*?<\/style>|<[^>]*>)/isu', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	if ( false === $parts ) {
		return $html;
	}

	foreach ( $parts as $i => $part ) {
		if ( '' === $part || '<' === $part[0] ) {
			continue;
		}

		$parts[ $i ] = preg_replace( array_keys( $rules ), array_values( $rules ), $part ) ?? $part;
	}

	return implode( '', $parts );
}

// Тексты WordPress и WooCommerce на фронте.
add_action( 'init', function () {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}

	foreach ( [ 'the_title', 'the_content', 'the_excerpt', 'woocommerce_short_description', 'nav_menu_item_title' ] as $hook ) {
		add_filter( $hook, 'arctik_typograph', 20 );
	}
} );
