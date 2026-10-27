<?php
/**
 * Каталог на главной (src/partials/home/catalog.html) — на странице дважды. Заголовок — главная, вкладка «Каталог»;
 * карточки — все вкусы (arctik_products()). На планшете ряд листается свайпом (js/modules/drag-slider.js).
 */

defined( 'ABSPATH' ) || exit;

$arctik_products = arctik_products();
if ( ! $arctik_products ) {
	return;
}
?>
<section class="catalog" data-header-theme="light" data-js-reveal-sequence data-reveal-sequence-end="top 40%">
	<h2 class="catalog__title section-title" data-js-reveal-sequence-item data-reveal-sequence-split><?php echo arctik_text( arctik_field( 'arctik_home_catalog_title', (int) get_option( 'page_on_front' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
	<div class="catalog__viewport" data-js-drag-slider data-drag-slider-align="start">
		<ul class="catalog__list products container" data-js-drag-slider-track data-js-reveal-sequence-item data-reveal-sequence-children>
			<?php
			foreach ( $arctik_products as $arctik_product ) {
				get_template_part( 'template-parts/product-card', null, [ 'product' => $arctik_product ] );
			}
			?>
		</ul>
	</div>
</section>
