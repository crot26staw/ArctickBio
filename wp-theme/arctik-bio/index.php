<?php
/**
 * Запасной шаблон: страницы без своего шаблона.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="main" data-header-theme="light">
	<div class="container">
		<?php while ( have_posts() ) : the_post(); ?>
			<h1 data-js-title-reveal><?php the_title(); ?></h1>
			<?php the_content(); ?>
		<?php endwhile; ?>
	</div>
</main>
<?php
get_footer();
