<?php
/**
 * Счётчик количества (src/partials/quantity.html; разметка WooCommerce: .quantity > input.qty), кнопки — quantity.js.
 *
 * $args: name — имя поля ("quantity" у формы; без имени — копия в нижней панели мобилки, не отправляется), max — максимум,
 * disabled — счётчик неактивен (товара нет в наличии).
 */

defined( 'ABSPATH' ) || exit;

$arctik_name     = $args['name'] ?? '';
$arctik_disabled = ! empty( $args['disabled'] );
?>
<div class="quantity" data-js-quantity>
	<button class="quantity__btn" type="button" aria-label="Уменьшить количество" data-js-quantity-minus<?php disabled( $arctik_disabled ); ?>>
		<img src="<?php echo esc_url( arctik_asset( 'icons/icn-minus-24.svg' ) ); ?>" alt="" width="24" height="24">
	</button>
	<input class="quantity__input input-text qty text" type="number"<?php echo $arctik_name ? ' name="' . esc_attr( $arctik_name ) . '"' : ''; ?> value="1" min="1" max="<?php echo esc_attr( $args['max'] ?? 99 ); ?>" step="1" inputmode="numeric" aria-label="Количество" data-js-quantity-input<?php disabled( $arctik_disabled ); ?>>
	<button class="quantity__btn" type="button" aria-label="Увеличить количество" data-js-quantity-plus<?php disabled( $arctik_disabled ); ?>>
		<img src="<?php echo esc_url( arctik_asset( 'icons/icn-add-24.svg' ) ); ?>" alt="" width="24" height="24">
	</button>
</div>
