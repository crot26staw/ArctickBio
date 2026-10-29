<?php
/**
 * Template Name: Документы
 *
 * «Документы»: общие поля страниц документов (inc/fields/documents.php). Есть документы — редирект на первый
 * (inc/documents.php), здесь выводится, только пока их нет.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="main">
	<?php get_template_part( 'template-parts/documents/main' ); ?>
</main>
<?php
get_footer();
