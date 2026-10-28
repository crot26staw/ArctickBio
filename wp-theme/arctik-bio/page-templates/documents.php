<?php
/**
 * Template Name: Документы
 *
 * «Документы» (src/documents.html): юридические документы с меню слева, оформление — как у «Покупателям».
 * Контент — поля страницы (inc/fields/documents.php).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="main">
	<?php get_template_part( 'template-parts/documents/main' ); ?>
</main>
<?php
get_footer();
