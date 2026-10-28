<?php
/**
 * Баннер набора (src/partials/catalog/offer.html). Поля — страница «Каталог», вкладка «Баннер набора».
 */

defined( 'ABSPATH' ) || exit;

$arctik_page    = (int) wc_get_page_id( 'shop' );
$arctik_product = wc_get_product( (int) ( arctik_field( 'arctik_catalog_offer_product', $arctik_page )[0]['id'] ?? 0 ) );
$arctik_list    = (array) arctik_field( 'arctik_catalog_offer_list', $arctik_page );

// На странице самого набора баннер с ним не нужен
if ( $arctik_product && is_singular( 'product' ) && get_queried_object_id() === $arctik_product->get_id() ) {
	return;
}
?>
<section class="offer" data-header-theme="dark">
	<div class="offer__content">
		<div class="offer__header">
			<h2 class="offer__title section-title section-title--white" data-js-title-reveal><?php echo arctik_lines( arctik_field( 'arctik_catalog_offer_title', $arctik_page ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php if ( $arctik_list ) : ?>
				<ul class="offer__list" data-js-fade-up-group>
					<?php foreach ( $arctik_list as $arctik_item ) : ?>
						<li><?php echo arctik_text( $arctik_item['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
		if ( $arctik_product ) {
			get_template_part( 'template-parts/product-plan', null, [ 'product' => $arctik_product ] );
		}
		?>
	</div>
	<div class="offer__media" aria-hidden="true">
		<?php echo arctik_image( (int) arctik_field( 'arctik_catalog_offer_image', $arctik_page ), 'full', [ 'alt' => '', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>
