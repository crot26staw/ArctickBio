<?php
/**
 * Главная (src/index.html). Поля — страница, выбранная главной (inc/fields/home.php); видео, «Процесс», курс,
 * преимущества и сравнение — «Общие блоки»; вопросы — «Покупателям», отзывы — записи «Отзывы».
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="main">
	<?php get_template_part( 'template-parts/home/hero' ); ?>
	<?php // Внешняя обёртка: в конце закрепляется, и catalog наезжает на product-scene (js/modules/sachet-journey.js, фаза 3). Отдельный элемент — второй pin на .sachet-journey ломает её первый pin. ?>
	<div class="sachet-journey-cover" data-js-sachet-journey-cover>
		<?php // Стик «перелетает» из блока about в пачку product-scene при скролле (js/modules/sachet-journey.js). ?>
		<div class="sachet-journey" data-js-sachet-journey>
			<?php // Облачная завеса: в конце пина about поднимается над ним, product-scene выходит из неё. ?>
			<div class="cloud-veil" aria-hidden="true" data-js-cloud-veil>
				<img class="cloud-veil__clouds" src="<?php echo esc_url( arctik_asset( 'images/about-clouds.webp' ) ); ?>" alt="" width="648" height="405" loading="lazy">
				<div class="cloud-veil__fill"></div>
				<div class="cloud-veil__theme" data-header-theme="light"></div>
			</div>
			<?php get_template_part( 'template-parts/home/about' ); ?>
			<?php get_template_part( 'template-parts/home/product-scene' ); ?>
			<div class="sachet-journey__sachet" data-js-sachet-journey-item>
				<img src="<?php echo esc_url( arctik_asset( 'images/stick.webp' ) ); ?>" alt="" width="300" height="900" loading="lazy" data-js-sachet-journey-img>
			</div>
		</div>
	</div>
	<?php get_template_part( 'template-parts/home/catalog' ); ?>
	<?php // video наезжает сверху на последний экран дорожки compound (scss/blocks/_overlap-stack.scss). ?>
	<div class="overlap-stack overlap-stack--track">
		<?php get_template_part( 'template-parts/home/compound' ); ?>
		<?php get_template_part( 'template-parts/home/video' ); ?>
	</div>
	<?php get_template_part( 'template-parts/home/research' ); ?>
	<?php get_template_part( 'template-parts/home/production' ); ?>
	<?php get_template_part( 'template-parts/home/comparison' ); ?>
	<?php get_template_part( 'template-parts/home/usage' ); ?>
	<?php get_template_part( 'template-parts/home/benefits' ); ?>
	<?php get_template_part( 'template-parts/home/catalog' ); ?>
	<?php get_template_part( 'template-parts/home/faq' ); ?>
	<?php get_template_part( 'template-parts/home/reviews' ); ?>
</main>
<?php
get_footer();
