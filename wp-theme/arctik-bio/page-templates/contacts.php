<?php
/**
 * Template Name: Контакты
 *
 * «Контакты» (src/contacts.html): один экран — стеклянная карточка контактов поверх фото.
 * Тексты и фото — поля страницы (inc/fields/contacts.php), контакты и соцсети — «Настройки сайта».
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="main">
	<?php get_template_part( 'template-parts/contacts/main' ); ?>
</main>
<?php
get_footer();
