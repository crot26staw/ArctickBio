<?php
/**
 * Сотрудники (src/partials/about/team.html): поля страницы, вкладка «Сотрудники». Нет сотрудников — секция не выводится.
 */

defined( 'ABSPATH' ) || exit;

$arctik_page = (int) get_the_ID();
$arctik_team = array_filter( (array) arctik_field( 'arctik_team', $arctik_page ), fn( $item ) => '' !== trim( (string) $item['name'] ) );
if ( ! $arctik_team ) {
	return;
}
?>
<section class="team" data-header-theme="light" data-js-scroll-shift>
	<div class="container">
		<h2 class="team__title" data-js-title-reveal><?php echo arctik_text( arctik_field( 'arctik_team_title', $arctik_page ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
	</div>

	<?php // Лента листается зажатием мышью и свайпом (js/modules/drag-slider.js); на десктопе по скроллу уезжает к левому полю (scroll-shift.js) ?>
	<div class="team__viewport" data-js-drag-slider data-drag-slider-align="start">
		<ul class="team__list" data-js-drag-slider-track data-js-scroll-shift-track data-js-fade-up-group>
			<?php foreach ( $arctik_team as $arctik_person ) : ?>
				<?php get_template_part( 'template-parts/team-card', null, $arctik_person ); ?>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
