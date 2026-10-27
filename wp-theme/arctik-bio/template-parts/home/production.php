<?php
/**
 * «Процесс изготовления» (src/partials/home/production.html) — главная и «О компании».
 * Контент — «Настройки сайта → Общие блоки → Процесс». Нет шагов — секция не выводится.
 */

defined( 'ABSPATH' ) || exit;

$arctik_steps = array_values( array_filter( (array) arctik_option( 'arctik_production_steps' ), fn( $step ) => '' !== trim( (string) $step['title'] ) ) );
if ( ! $arctik_steps ) {
	return;
}
?>
<section class="production" data-header-theme="dark" data-js-ribbon>
	<div class="production__heading">
		<h2 class="production__title section-title section-title--light" data-js-title-reveal><?php echo arctik_lines( arctik_option( 'arctik_production_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		<span class="production__tm" aria-hidden="true">TM</span>
	</div>

	<?php // Лента шагов: секция закреплена, по скроллу страницы лента уезжает влево (js/modules/ribbon.js). ?>
	<ol class="production__steps" data-js-ribbon-track>
		<?php foreach ( $arctik_steps as $arctik_index => $arctik_step ) : ?>
			<?php get_template_part( 'template-parts/production-step', null, [ 'num' => $arctik_index + 1, 'step' => $arctik_step ] ); ?>
		<?php endforeach; ?>
	</ol>
</section>
