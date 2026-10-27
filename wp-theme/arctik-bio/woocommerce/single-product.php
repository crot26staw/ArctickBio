<?php
/**
 * Карточка товара (src/product.html): своя разметка вместо стандартной WC.
 * Микроразметка товара (JSON-LD) — WC_Structured_Data, выводится в подвале.
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();
$arctik_product = wc_get_product( get_the_ID() );

if ( $arctik_product && isset( WC()->structured_data ) ) {
	WC()->structured_data->generate_product_data( $arctik_product );
}
?>
<main class="main">
	<?php get_template_part( 'template-parts/product/main', null, [ 'product' => $arctik_product ] ); ?>
</main>
<?php
get_footer();
