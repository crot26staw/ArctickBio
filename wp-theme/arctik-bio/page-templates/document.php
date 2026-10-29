<?php
/**
 * Template Name: Документ
 *
 * Юридический документ (src/oferta.html и др.): дочерняя страница «Документов», меню документов слева (inc/documents.php).
 * Текст — поле страницы (inc/fields/documents.php).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="main">
	<?php get_template_part( 'template-parts/documents/main' ); ?>
</main>
<?php
get_footer();
