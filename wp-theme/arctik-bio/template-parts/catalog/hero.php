<?php
/**
 * Первый экран каталога (src/partials/catalog/hero.html). Поля — страница «Каталог», вкладка «Первый экран».
 */

defined( 'ABSPATH' ) || exit;

$arctik_page = (int) wc_get_page_id( 'shop' );
?>
<section class="page-hero page-hero--short" data-header-theme="dark">
	<div class="page-hero__bg" aria-hidden="true">
		<?php echo arctik_image( (int) arctik_field( 'arctik_catalog_hero_image', $arctik_page ), 'full', [ 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
	<div class="page-hero__inner container">
		<h1 class="page-hero__title" data-js-title-reveal data-title-reveal-start="top bottom"><?php echo arctik_text( arctik_field( 'arctik_catalog_title', $arctik_page ) ?: woocommerce_page_title( false ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
	</div>
</section>
