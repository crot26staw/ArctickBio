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
