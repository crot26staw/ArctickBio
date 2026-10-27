<?php
/**
 * Поле с плавающей подписью (src/partials/form-field.html, components/_field.scss).
 *
 * $args: id, type, name, label, autocomplete, mod, attrs — готовые атрибуты (data-validate="email" …), выводятся как есть.
 */

defined( 'ABSPATH' ) || exit;

$arctik_id = $args['id'] ?? '';
?>
<div class="field <?php echo esc_attr( $args['mod'] ?? '' ); ?>" data-js-field>
	<div class="field__box">
		<input class="field__input" id="<?php echo esc_attr( $arctik_id ); ?>" type="<?php echo esc_attr( $args['type'] ?? 'text' ); ?>" name="<?php echo esc_attr( $args['name'] ?? '' ); ?>" autocomplete="<?php echo esc_attr( $args['autocomplete'] ?? '' ); ?>" placeholder=" " aria-describedby="<?php echo esc_attr( $arctik_id ); ?>-hint" data-js-field-input <?php echo $args['attrs'] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<label class="field__label" for="<?php echo esc_attr( $arctik_id ); ?>"><?php echo wp_kses_post( $args['label'] ?? '' ); ?></label>
	</div>
	<p class="field__hint" id="<?php echo esc_attr( $arctik_id ); ?>-hint" data-js-field-hint></p>
</div>
