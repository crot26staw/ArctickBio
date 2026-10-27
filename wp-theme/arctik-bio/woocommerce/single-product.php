<?php
/**
 * Карточка товара (src/product.html): своя разметка вместо стандартной WC.
 * Микроразметка товара (JSON-LD) — WC_Structured_Data, выводится в подвале.
 * Ниже первого экрана — общие блоки (Настройки сайта → Общие блоки) и баннер набора (поля страницы «Каталог»).
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
	<?php // video наезжает сверху на предыдущую секцию, когда её низ дошёл до низа экрана (scss/blocks/_overlap-stack.scss). ?>
	<div class="overlap-stack overlap-stack--tall" data-js-overlap-stack>
		<?php get_template_part( 'template-parts/product/main', null, [ 'product' => $arctik_product ] ); ?>
		<?php get_template_part( 'template-parts/home/video' ); ?>
	</div>
	<?php get_template_part( 'template-parts/home/usage' ); ?>
	<?php get_template_part( 'template-parts/home/benefits', null, [ 'mod' => 'benefits--hidden-mobile' ] ); ?>
	<?php get_template_part( 'template-parts/home/comparison', null, [ 'mod' => 'comparison--spaced' ] ); ?>
	<?php get_template_part( 'template-parts/catalog/offer' ); ?>
</main>
<?php
get_footer();
