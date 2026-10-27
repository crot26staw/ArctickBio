<?php
/**
 * Пункт-аккордеон (src/partials/accordion-item.html), accordion.js.
 *
 * $args: mod — модификатор (accordion--line), id — id панели, open — раскрыт, question — текст вопроса,
 * answer — ответ (многострочный текст, в <p class="accordion__answer">).
 */

defined( 'ABSPATH' ) || exit;

$arctik_open = ! empty( $args['open'] );
?>
<div class="accordion <?php echo esc_attr( $args['mod'] ?? '' ); ?><?php echo $arctik_open ? ' is-open' : ''; ?>" data-js-accordion>
	<button class="accordion__header" type="button" aria-expanded="<?php echo $arctik_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $args['id'] ); ?>" data-js-accordion-toggle>
		<span class="accordion__question"><?php echo arctik_text( $args['question'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<span class="accordion__icon icon-btn icon-btn--24" aria-hidden="true">
			<img src="<?php echo esc_url( arctik_asset( 'icons/icn-plus.svg' ) ); ?>" alt="" width="18" height="18">
			<img src="<?php echo esc_url( arctik_asset( 'icons/icn-minus.svg' ) ); ?>" alt="" width="18" height="18">
		</span>
	</button>
	<div class="accordion__panel" id="<?php echo esc_attr( $args['id'] ); ?>" data-js-accordion-panel>
		<p class="accordion__answer"><?php echo arctik_lines( $args['answer'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	</div>
</div>
