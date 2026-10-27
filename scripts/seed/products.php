<?php
/**
 * Наполнение товаров (этап «Т»): атрибут «Формат», 5 вкусов (вариативные: 15 / 30 саше), набор на 3 месяца,
 * общие поля товаров — тексты и картинки из статики (цены и фото там заглушки — как в вёрстке).
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/products.php
 * Повторный запуск безопасен: товары находятся по ярлыку и обновляются, вариации пересоздаются.
 */

require_once __DIR__ . '/_lib.php';

function arctik_seed_product( string $slug, string $class ): WC_Product {
	$post = get_page_by_path( $slug, OBJECT, 'product' );

	return $post ? wc_get_product( $post->ID ) : new $class();
}

// Атрибут «Формат».
if ( ! wc_attribute_taxonomy_id_by_name( 'format' ) ) {
	wc_create_attribute( [ 'name' => 'Формат', 'slug' => 'format', 'type' => 'select', 'order_by' => 'menu_order' ] );
}
if ( ! taxonomy_exists( 'pa_format' ) ) {
	register_taxonomy( 'pa_format', 'product' );
}
$formats = [ '15-sashe' => '15 саше', '30-sashe' => '30 саше' ];
foreach ( $formats as $slug => $name ) {
	if ( ! term_exists( $slug, 'pa_format' ) ) {
		wp_insert_term( $name, 'pa_format', [ 'slug' => $slug ] );
	}
}

// Картинки (одни на все вкусы — как в статике).
$img_card  = arctik_seed_image( 'product-card.webp', 'Морской коллаген' );
$img_hover = arctik_seed_image( 'product-card-hover.webp' );
$img_mini  = arctik_seed_image( 'product-mini.webp' );
$gallery   = [
	arctik_seed_image( 'product-1.webp', 'Премиальный морской коллаген с дикой черникой' ),
	arctik_seed_image( 'product-2.webp', 'Упаковка коллагена на камнях у моря' ),
	arctik_seed_image( 'product-3.webp', 'Упаковка коллагена на берегу Баренцева моря' ),
	arctik_seed_image( 'product-4.webp', 'Упаковка коллагена среди ягод черники' ),
	arctik_seed_image( 'product-5.webp', 'Саше коллагена и черника во льду' ),
	arctik_seed_image( 'product-6.webp', 'Упаковка коллагена на мху' ),
	arctik_seed_image( 'product-7.webp', 'Упаковка коллагена среди кристаллов льда' ),
];

$description = "<ul>\n<li>Содержит 100 % суточной нормы 20 витаминов и минералов</li>\n<li>Помогает восполнить суточную потребность в питательных веществах благодаря точному дозированию</li>\n<li>Разработано для усвоения на всех этапах желудочно-кишечного тракта</li>\n<li>Содержит биодоступные питательные вещества, которые может использовать ваш организм</li>\n<li>Поддерживает здоровое старение и иммунную функцию</li>\n</ul>";

// Вкусы: ярлык => [ название, родительный падеж ягоды ].
$flavors = [
	'kollagen-chernika' => [ 'Коллаген с черникой', 'черникой' ],
	'kollagen-brusnika' => [ 'Коллаген с брусникой', 'брусникой' ],
	'kollagen-golubika' => [ 'Коллаген с голубикой', 'голубикой' ],
	'kollagen-klyukva'  => [ 'Коллаген с клюквой', 'клюквой' ],
	'kollagen-moroshka' => [ 'Коллаген с морошкой', 'морошкой' ],
];

$order = 0;
foreach ( $flavors as $slug => [ $name, $berry ] ) {
	/** @var WC_Product_Variable $product */
	$product = arctik_seed_product( $slug, WC_Product_Variable::class );

	$attribute = new WC_Product_Attribute();
	$attribute->set_id( wc_attribute_taxonomy_id_by_name( 'format' ) );
	$attribute->set_name( 'pa_format' );
	$attribute->set_options( array_keys( $formats ) );
	$attribute->set_visible( true );
	$attribute->set_variation( true );

	$product->set_props( [
		'name'               => $name,
		'slug'               => $slug,
		'status'             => 'publish',
		'catalog_visibility' => 'visible',
		'menu_order'         => $order++,
		'description'        => $description,
		'image_id'           => $img_card,
		'gallery_image_ids'  => $gallery,
		'default_attributes' => [ 'pa_format' => '30-sashe' ],
	] );
	$product->set_attributes( [ $attribute ] );
	$id = $product->save();

	foreach ( $product->get_children() as $child ) {
		wc_get_product( $child )?->delete( true );
	}
	foreach ( array_keys( $formats ) as $format ) {
		$variation = new WC_Product_Variation();
		$variation->set_props( [
			'parent_id'     => $id,
			'status'        => 'publish',
			'regular_price' => '1440',
			'description'   => 'Попробовать · 15 дней',
			'image_id'      => $img_mini,
		] );
		$variation->set_attributes( [ 'pa_format' => $format ] );
		$variation->save();
	}
	WC_Product_Variable::sync( $id );

	carbon_set_post_meta( $id, 'arctik_product_full_title', "Премиальный морской коллаген с дикой {$berry}" );
	carbon_set_post_meta( $id, 'arctik_product_mini_title', 'Морской коллаген' );
	carbon_set_post_meta( $id, 'arctik_product_mini_subtitle', "с дикой {$berry}" );
	carbon_set_post_meta( $id, 'arctik_product_hover_image', $img_hover );
	carbon_set_post_meta( $id, 'arctik_product_mini_image', $img_mini );
}

// Набор на 3 месяца: простой товар, скрыт из каталога (баннер в каталоге ведёт на него).
$set = arctik_seed_product( 'nabor-na-3-mesyaca', WC_Product_Simple::class );
$set->set_props( [
	'name'               => 'Набор на 3 месяца',
	'slug'               => 'nabor-na-3-mesyaca',
	'status'             => 'publish',
	'catalog_visibility' => 'search',
	'menu_order'         => 100,
	'regular_price'      => '1440',
	'description'        => $description,
	'image_id'           => arctik_seed_image( 'offer-plan.webp', 'Набор на 3 месяца' ),
	'gallery_image_ids'  => $gallery,
] );
$set_id = $set->save();
carbon_set_post_meta( $set_id, 'arctik_product_mini_title', 'Набор на 3 месяца' );
carbon_set_post_meta( $set_id, 'arctik_product_mini_subtitle', 'Спеццена' );

// Общие поля товаров (Настройки сайта → Товары).
$ingredients = [
	[ 'ingredient-1.webp', 'Витамин С', 'Поступает из цельных северных ягод. Выступает кофактором синтеза собственного коллагена организмом' ],
	[ 'ingredient-2.webp', 'Витамин E', 'Защищает клетки от окислительного стресса. Помогает сохранять целостность клеточных мембран' ],
	[ 'ingredient-3.webp', 'Витамин K', 'Участвует в процессе нормального свертывания крови. Способствует поддержанию здоровья костной ткани' ],
	[ 'ingredient-4.webp', 'Витамин H', 'Участвует в обмене белков и жиров. Способствует поддержанию нормального состояния кожи и волос' ],
	[ 'ingredient-5.webp', 'Витамин PP', 'Участвует в энергетическом обмене клеток. Помогает поддерживать нормальное состояние кожи и нервной системы' ],
	[ 'ingredient-6.webp', 'Витамин A', 'Способствует обновлению клеток кожи. Поддерживает нормальное состояние слизистых оболочек и зрения' ],
];
carbon_set_theme_option( 'arctik_products_ingredients', array_map(
	fn( $i ) => [ 'image' => arctik_seed_image( $i[0] ), 'title' => $i[1], 'text' => $i[2] ],
	$ingredients
) );
carbon_set_theme_option( 'arctik_products_benefits', array_map( fn( $t ) => [ 'text' => $t ], [
	'Упругость и эластичность кожи',
	'Подвижность суставов',
	'Связки при физических нагрузках суставов',
	'Соединительную ткань при ежедневных нагрузках',
	'Здоровый рост волос и ногтей',
] ) );
carbon_set_theme_option( 'arctik_products_documents', array_map( fn( $t ) => [ 'title' => $t, 'file' => '' ], [
	'Аминокислотный профиль',
	'Протоколы испытаний продукции',
	'Отчет РАН',
] ) );
carbon_set_theme_option( 'arctik_products_delivery', 'Бесплатная доставка от 3 000 ₽ Ozon' );
carbon_set_theme_option( 'arctik_products_delivery_note', 'Бесплатная доставка от 3 000 ₽ Ozon' );

WP_CLI::success( 'Товары и общие поля товаров заполнены' );
