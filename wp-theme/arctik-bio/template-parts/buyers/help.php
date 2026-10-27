<?php
/**
 * Вопросы покупателей (src/partials/buyers/help.html): заголовок — название страницы, подпись, фон, подпись под меню
 * и разделы с вопросами — поля страницы (вкладка «Вопросы»). Раздел без вопросов не выводится.
 */

defined( 'ABSPATH' ) || exit;

$arctik_page = (int) get_the_ID();
$arctik_lead = (string) arctik_field( 'arctik_buyers_lead', $arctik_page );
$arctik_bg   = (int) arctik_field( 'arctik_buyers_bg', $arctik_page );
$arctik_note = (string) arctik_field( 'arctik_buyers_note', $arctik_page );

// Подпись — одна строка: из редактора оставляем только строчные теги.
$arctik_note = trim( wp_kses( $arctik_note, [ 'a' => [ 'href' => [], 'target' => [], 'rel' => [] ], 'strong' => [], 'em' => [], 'br' => [] ] ) );

$arctik_groups = [];
foreach ( (array) arctik_field( 'arctik_buyers_groups', $arctik_page ) as $arctik_index => $arctik_group ) {
	$arctik_questions = array_filter( (array) ( $arctik_group['questions'] ?? [] ), fn( $item ) => '' !== trim( (string) ( $item['question'] ?? '' ) ) );
	if ( ! $arctik_questions || '' === trim( (string) $arctik_group['title'] ) ) {
		continue;
	}

	$arctik_groups[] = [
		'id'        => sanitize_html_class( (string) $arctik_group['anchor'] ) ?: 'help-' . ( $arctik_index + 1 ),
		'title'     => (string) $arctik_group['title'],
		'menu'      => trim( (string) $arctik_group['menu'] ) ?: (string) $arctik_group['title'],
		'questions' => array_values( $arctik_questions ),
	];
}
?>
<section class="help" data-header-theme="dark">
	<?php if ( $arctik_bg ) : ?>
		<div class="help__bg" aria-hidden="true">
			<?php echo arctik_image( $arctik_bg, 'full', [ 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	<?php endif; ?>

	<div class="help__intro">
		<?php if ( $arctik_lead ) : ?>
			<p class="help__lead" data-js-title-reveal data-title-reveal-start="top bottom"><?php echo arctik_lines( $arctik_lead ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		<?php endif; ?>
		<h1 class="help__title" data-js-title-reveal data-title-reveal-start="top bottom"><?php the_title(); ?></h1>
	</div>

	<?php if ( $arctik_groups ) : ?>
		<?php // Панель вопросов: меню разделов прилипает при скролле, активный раздел — js/modules/anchor-nav.js ?>
		<div class="help__panel" data-header-theme="light">
			<aside class="help__aside">
				<nav aria-label="Разделы">
					<ul class="help__menu" data-js-anchor-nav>
						<?php foreach ( $arctik_groups as $arctik_index => $arctik_group ) : ?>
							<li><a class="help__link<?php echo 0 === $arctik_index ? ' is-active' : ''; ?>" href="#<?php echo esc_attr( $arctik_group['id'] ); ?>"<?php echo 0 === $arctik_index ? ' aria-current="true"' : ''; ?> data-js-roll><?php echo arctik_text( $arctik_group['menu'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
				<?php if ( $arctik_note ) : ?>
					<p class="help__note"><?php echo arctik_typograph( $arctik_note ); // phpcs:ignore WordPress.Security.EscapeOutput -- wp_kses выше ?></p>
				<?php endif; ?>
			</aside>

			<div class="help__content" data-js-accordion-group>
				<?php foreach ( $arctik_groups as $arctik_index => $arctik_group ) : ?>
					<section class="help__group" id="<?php echo esc_attr( $arctik_group['id'] ); ?>" aria-labelledby="<?php echo esc_attr( $arctik_group['id'] ); ?>-title">
						<h2 class="help__group-title" id="<?php echo esc_attr( $arctik_group['id'] ); ?>-title" data-js-title-reveal><?php echo arctik_text( $arctik_group['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
						<div class="help__list">
							<?php foreach ( $arctik_group['questions'] as $arctik_number => $arctik_item ) : ?>
								<?php
								get_template_part( 'template-parts/accordion-item', null, [
									'mod'      => 'accordion--line',
									'id'       => $arctik_group['id'] . '-' . ( $arctik_number + 1 ),
									'open'     => 0 === $arctik_index && 0 === $arctik_number,
									'question' => $arctik_item['question'],
									'answer'   => $arctik_item['answer'] ?? '',
								] );
								?>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
