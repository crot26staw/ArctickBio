<?php
/**
 * Список товаров каталога (src/partials/catalog/products.html) — основной цикл WooCommerce, сетка по 3 в ряд.
 * Набор на 3 месяца скрыт из каталога (видимость «только поиск») — в цикл не попадает.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="catalog-grid" data-header-theme="light">
	<h2 class="catalog-grid__title section-title"><?php echo arctik_text( arctik_field( 'arctik_catalog_grid_title', (int) wc_get_page_id( 'shop' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
	<ul class="catalog-grid__list products" data-js-fade-up-group>
		<?php
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/product-card', null, [
				'product' => wc_get_product( get_the_ID() ),
				'mod'     => 'product-card--wide',
			] );
		}
		?>
	</ul>
</section>
