<?php
/**
 * Каталог (src/catalog.html): страница магазина и архивы товаров WooCommerce.
 * Своя разметка вместо стандартной WC; товары — основной цикл (template-parts/catalog/products.php).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="main">
	<?php get_template_part( 'template-parts/catalog/hero' ); ?>
	<?php // video наезжает сверху на предыдущую секцию, когда её низ дошёл до низа экрана (scss/blocks/_overlap-stack.scss). ?>
	<div class="overlap-stack overlap-stack--tall" data-js-overlap-stack>
		<?php get_template_part( 'template-parts/catalog/products' ); ?>
		<?php get_template_part( 'template-parts/home/video' ); ?>
	</div>
	<?php get_template_part( 'template-parts/catalog/offer' ); ?>
	<?php get_template_part( 'template-parts/home/usage' ); ?>
	<?php get_template_part( 'template-parts/home/benefits', null, [ 'mod' => 'benefits--hidden-mobile' ] ); ?>
	<?php get_template_part( 'template-parts/home/comparison', null, [ 'mod' => 'comparison--spaced' ] ); ?>
</main>
<?php
get_footer();
