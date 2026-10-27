<?php
/**
 * Template Name: Покупателям
 *
 * «Покупателям» (src/buyers.html): вопросы по разделам с меню разделов и «Где нас купить?».
 * Контент — поля страницы (inc/fields/buyers.php).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="main">
	<?php get_template_part( 'template-parts/buyers/help' ); ?>
	<?php // «Где нас купить?» пока скрыт (просьба пользователя), поля — в inc/fields/buyers.php под комментарием. ?>
	<?php // get_template_part( 'template-parts/buyers/stores' ); ?>
</main>
<?php
get_footer();
