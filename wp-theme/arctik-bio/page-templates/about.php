<?php
/**
 * Template Name: О компании
 *
 * «О компании» (src/about.html): первый экран, «Сделано людьми Севера», «Процесс изготовления» (общий блок),
 * сотрудники, соцсети, новости. Контент — поля страницы (inc/fields/about.php), «Общие блоки», записи «Новости».
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="main">
	<?php get_template_part( 'template-parts/about/hero' ); ?>
	<?php get_template_part( 'template-parts/about/intro' ); ?>
	<?php get_template_part( 'template-parts/home/production' ); ?>
	<?php get_template_part( 'template-parts/about/team' ); ?>
	<?php get_template_part( 'template-parts/about/media' ); ?>
	<?php get_template_part( 'template-parts/about/news' ); ?>
</main>
<?php
get_footer();
