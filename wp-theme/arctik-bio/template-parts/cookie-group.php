<?php
/**
 * Категория cookie в окне настроек (src/partials/cookie-group.html): аккордеон (accordion.js) + переключатель.
 *
 * $args: category — necessary / analytics / marketing (имя переключателя, cookies.js), name — название, text — описание,
 * open — раскрыта, badge — подпись рядом с названием, label — aria-label переключателя, required — всегда включена.
 */

defined( 'ABSPATH' ) || exit;

$arctik_id       = 'cookie-group-' . $args['category'];
$arctik_open     = ! empty( $args['open'] );
$arctik_required = ! empty( $args['required'] );
?>
<div class="cookie-group<?php echo $arctik_open ? ' is-open' : ''; ?>" data-js-accordion>
	<div class="cookie-group__row">
		<div class="cookie-group__head">
			<button class="cookie-group__toggle" type="button" aria-expanded="<?php echo $arctik_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $arctik_id ); ?>" data-js-accordion-toggle>
				<span class="cookie-group__icon" aria-hidden="true">
					<img src="<?php echo esc_url( arctik_asset( 'icons/icn-plus-16.svg' ) ); ?>" alt="" width="16" height="16">
					<img src="<?php echo esc_url( arctik_asset( 'icons/icn-minus-16.svg' ) ); ?>" alt="" width="16" height="16">
				</span>
				<span class="cookie-group__name"><?php echo arctik_text( $args['name'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			</button>
			<span class="cookie-group__badge"><?php echo esc_html( $args['badge'] ?? '' ); ?></span>
		</div>
		<label class="switch">
			<input class="switch__input" type="checkbox" name="<?php echo esc_attr( $args['category'] ); ?>" value="1" aria-label="<?php echo esc_attr( $args['label'] ); ?>"<?php echo $arctik_required ? ' checked disabled' : ''; ?>>
			<span class="switch__track" aria-hidden="true">
				<span class="switch__knob"><img src="<?php echo esc_url( arctik_asset( 'icons/icn-switch-check.svg' ) ); ?>" alt="" width="8" height="5"></span>
			</span>
		</label>
	</div>
	<div class="cookie-group__panel" id="<?php echo esc_attr( $arctik_id ); ?>" data-js-accordion-panel>
		<p class="cookie-group__text"><?php echo arctik_lines( $args['text'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	</div>
</div>
