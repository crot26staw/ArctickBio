<?php
/**
 * SEO — плагин Yoast SEO (решение пользователя): заголовки и описания задаются в нём (сид scripts/seed/seo.php).
 * Здесь — только то, чего у Yoast нет: переменная %%arctik_full_title%% (полное название товара — h1 карточки)
 * для шаблона заголовка товаров.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wpseo_register_extra_replacements', function () {
	wpseo_register_var_replacement(
		'%%arctik_full_title%%',
		function () {
			$product = is_singular( 'product' ) ? wc_get_product( get_queried_object_id() ) : null;

			return $product ? arctik_product_full_title( $product ) : '';
		},
		'advanced',
		'Полное название товара (h1 карточки)'
	);
} );
