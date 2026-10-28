<?php
/**
 * Картинки вкусов (новые фото от пользователя, 28.10.2026; файлы — src/assets/images/product-<вкус>*.webp):
 * пачка без фона — изображение товара (карточка каталога), мини-пачка — «Пачка мини-карточки» и картинка вариаций,
 * кадр на мху — «Фото при наведении»; галерея — на чёрном (главное), на ягодах, на мху, на белом.
 * Набор на 3 месяца не трогается. Прежние общие картинки (product-card / -hover / -mini) удаляются из медиатеки.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/product-images.php (его вызывает и products.php).
 * Повторный запуск безопасен: товары и вариации не пересоздаются, меняются только картинки.
 */

require_once __DIR__ . '/_lib.php';

// Ярлык товара => [ файл вкуса, ягода в alt ].
$arctik_flavor_images = [
	'kollagen-chernika' => [ 'chernika', 'черникой', 'черники' ],
	'kollagen-brusnika' => [ 'brusnika', 'брусникой', 'брусники' ],
	'kollagen-golubika' => [ 'golubika', 'голубикой', 'голубики' ],
	'kollagen-klyukva'  => [ 'klyukva', 'клюквой', 'клюквы' ],
	'kollagen-moroshka' => [ 'moroshka', 'морошкой', 'морошки' ],
];

foreach ( $arctik_flavor_images as $slug => [ $file, $with, $of ] ) {
	$post = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $post ) {
		WP_CLI::warning( "Нет товара {$slug}" );
		continue;
	}

	$card    = arctik_seed_image( "product-{$file}.webp", "Морской коллаген с дикой {$with}" );
	$mini    = arctik_seed_image( "product-{$file}-mini.webp" );
	$moss    = arctik_seed_image( "product-{$file}-3.webp", 'Упаковка коллагена на мху' );
	$gallery = [
		arctik_seed_image( "product-{$file}-1.webp", "Премиальный морской коллаген с дикой {$with}" ),
		arctik_seed_image( "product-{$file}-2.webp", "Упаковка коллагена среди ягод {$of}" ),
		$moss,
		arctik_seed_image( "product-{$file}-4.webp", "Упаковка коллагена с дикой {$with}" ),
	];

	$product = wc_get_product( $post->ID );
	$product->set_image_id( $card );
	$product->set_gallery_image_ids( $gallery );
	$product->save();

	foreach ( $product->get_children() as $child ) {
		$variation = wc_get_product( $child );
		$variation?->set_image_id( $mini );
		$variation?->save();
	}

	carbon_set_post_meta( $post->ID, 'arctik_product_hover_image', $moss );
	carbon_set_post_meta( $post->ID, 'arctik_product_mini_image', $mini );
}

// Прежние общие картинки вкусов больше нигде не используются.
foreach ( [ 'product-card.webp', 'product-card-hover.webp', 'product-mini.webp' ] as $old ) {
	foreach ( get_posts( [ 'post_type' => 'attachment', 'post_status' => 'any', 'meta_key' => '_arctik_seed', 'meta_value' => $old, 'fields' => 'ids', 'numberposts' => -1 ] ) as $id ) {
		wp_delete_attachment( $id, true );
	}
}

WP_CLI::success( 'Картинки вкусов обновлены' );
