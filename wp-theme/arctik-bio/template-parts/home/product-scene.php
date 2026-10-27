<?php
/**
 * Сцена с пачкой (src/partials/home/product-scene.html): скалы и пачка — картинки темы, подогнаны под летящий стик
 * (цель перелёта — [data-js-sachet-journey-target], js/modules/sachet-journey.js). Полей нет.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="product-scene" data-header-theme="dark" aria-label="Упаковка АрктикБиоМарин">
	<div class="product-scene__rock-top" aria-hidden="true" data-js-cloud-veil-edge>
		<img src="<?php echo esc_url( arctik_asset( 'images/rock-top.webp' ) ); ?>" alt="" width="1672" height="941" loading="lazy">
	</div>
	<div class="product-scene__rock-bottom" aria-hidden="true">
		<img src="<?php echo esc_url( arctik_asset( 'images/rock-bottom.webp' ) ); ?>" alt="" width="2169" height="725" loading="lazy">
	</div>
	<div class="product-scene__product">
		<div class="product-scene__pack">
			<img src="<?php echo esc_url( arctik_asset( 'images/pack.webp' ) ); ?>" alt="Премиальный морской коллаген с дикой черникой, 30 саше" width="1122" height="1402" loading="lazy">
		</div>
		<span class="product-scene__sachet" aria-hidden="true" data-js-sachet-journey-target></span>
		<div class="product-scene__pack-front" aria-hidden="true">
			<img src="<?php echo esc_url( arctik_asset( 'images/pack.webp' ) ); ?>" alt="" width="1122" height="1402" loading="lazy">
		</div>
	</div>
</section>
