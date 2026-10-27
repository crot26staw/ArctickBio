<?php
/**
 * Данные товара для шаблонов (поля — inc/fields/product.php).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Полное название товара (заголовок страницы товара).
 */
function arctik_product_full_title( WC_Product $product ): string {
	return (string) arctik_field( 'arctik_product_full_title', $product->get_id() ) ?: $product->get_name();
}

/**
 * Заголовок и подзаголовок мини-карточки: [ 'Морской коллаген', 'с дикой черникой' ].
 */
function arctik_product_mini_titles( WC_Product $product ): array {
	return [
		(string) arctik_field( 'arctik_product_mini_title', $product->get_id() ) ?: $product->get_name(),
		(string) arctik_field( 'arctik_product_mini_subtitle', $product->get_id() ),
	];
}

/**
 * ID картинки товара: 'main' — «Изображение товара», 'hover' — фото при наведении, 'mini' — пачка мини-карточки.
 */
function arctik_product_image_id( WC_Product $product, string $kind = 'main' ): int {
	$main = (int) $product->get_image_id();

	return match ( $kind ) {
		'hover' => (int) arctik_field( 'arctik_product_hover_image', $product->get_id() ),
		'mini'  => (int) arctik_field( 'arctik_product_mini_image', $product->get_id() ) ?: $main,
		default => $main,
	};
}

/**
 * Контент карточки товара с заменой: своё у товара, иначе общее из настроек.
 * $key: ingredients | benefits | documents | delivery.
 */
function arctik_product_content( WC_Product $product, string $key ): mixed {
	$own = arctik_field( "arctik_product_{$key}", $product->get_id() );

	return ! empty( $own ) ? $own : arctik_option( "arctik_products_{$key}" );
}

/**
 * ID для добавления в корзину из карточки (data-product_id): у вариативного товара — вариация по умолчанию
 * (WC_AJAX::add_to_cart принимает ID вариации), у простого — сам товар.
 */
function arctik_product_cart_id( WC_Product $product ): int {
	if ( $product->is_type( 'variable' ) ) {
		$variation_id = ( new WC_Product_Data_Store_CPT() )->find_matching_product_variation( $product, array_combine(
			array_map( fn( $name ) => 'attribute_' . $name, array_keys( $product->get_default_attributes() ) ),
			$product->get_default_attributes()
		) );

		return $variation_id ?: (int) ( $product->get_children()[0] ?? $product->get_id() );
	}

	return $product->get_id();
}

/**
 * Цена текстом для разметки, которую меняет JS (data-price, [data-js-product-price]): «1 440 ₽» без тегов wc_price().
 */
function arctik_price_text( float|string $price ): string {
	return html_entity_decode( wp_strip_all_tags( wc_price( $price ) ), ENT_QUOTES, 'UTF-8' );
}

/**
 * Сколько штук можно купить за раз (max счётчика): остаток при учёте запасов, иначе 99, как в вёрстке.
 */
function arctik_product_max_qty( WC_Product $product ): int {
	$max = $product->get_max_purchase_quantity();

	return $max > 0 ? min( $max, 99 ) : 99;
}

/**
 * Форматы вариативного товара для выбора на странице товара (атрибут pa_format), в порядке вариаций.
 * Выбран формат по умолчанию, если он в наличии, иначе первый в наличии; нет ни одного — не выбран никакой.
 *
 * @return array<int, array{variation: WC_Product_Variation, slug: string, title: string, available: bool, checked: bool}>
 */
function arctik_product_formats( WC_Product $product ): array {
	if ( ! $product->is_type( 'variable' ) ) {
		return [];
	}

	$formats = [];
	foreach ( $product->get_children() as $id ) {
		$variation = wc_get_product( $id );
		if ( ! $variation instanceof WC_Product_Variation || ! $variation->variation_is_visible() ) {
			continue;
		}

		$slug = (string) ( $variation->get_attributes()['pa_format'] ?? '' );
		$term = get_term_by( 'slug', $slug, 'pa_format' );

		$formats[] = [
			'variation' => $variation,
			'slug'      => $slug,
			'title'     => $term ? $term->name : $slug,
			'available' => $variation->is_purchasable() && $variation->is_in_stock(),
			'checked'   => false,
		];
	}

	$available = array_filter( $formats, fn( $format ) => $format['available'] );
	$default   = $product->get_default_attributes()['pa_format'] ?? '';
	$checked   = array_key_first( array_filter( $available, fn( $format ) => $format['slug'] === $default ) ) ?? array_key_first( $available );

	if ( null !== $checked ) {
		$formats[ $checked ]['checked'] = true;
	}

	return $formats;
}
