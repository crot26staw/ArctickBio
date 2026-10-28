<?php
/**
 * Способы получения (часть src/partials/checkout/main.html): «Пункт выдачи» / «Курьер» со сроком и стоимостью СДЭК.
 * Фрагмент [data-js-checkout-rates] — обновляется после смены города и способа (inc/checkout.php, inc/ajax/delivery.php).
 * $args: rates — arctik_shipping_rates(), chosen — ID выбранной ставки (по умолчанию — из сессии).
 */

defined( 'ABSPATH' ) || exit;

$arctik_rates  = $args['rates'] ?? arctik_shipping_rates();
$arctik_chosen = $args['chosen'] ?? arctik_checkout_chosen_rate( $arctik_rates );
?>
<div class="checkout__options checkout__options--delivery" data-js-checkout-rates>
	<?php foreach ( $arctik_rates as [ 'rate' => $arctik_r, 'slot' => $arctik_s, 'term' => $arctik_term ] ) : ?>
		<?php $arctik_cost = (float) $arctik_r->get_cost() + (float) $arctik_r->get_shipping_tax(); ?>
		<label class="checkout-ship">
			<input class="checkout-ship__input shipping_method" type="radio" name="shipping_method[0]" value="<?php echo esc_attr( $arctik_r->get_id() ); ?>" <?php checked( $arctik_r->get_id(), $arctik_chosen ); ?> data-js-checkout-shipping="<?php echo esc_attr( $arctik_s ); ?>">
			<span class="checkout-ship__title"><?php echo arctik_text( $arctik_r->get_label() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<span class="checkout-ship__meta">
				<?php echo $arctik_term ? arctik_text( $arctik_term ) . ', ' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $arctik_cost > 0 ) : ?>
					<?php echo esc_html( arctik_price_text( $arctik_cost ) ); ?>
				<?php else : ?>
					<span class="checkout-ship__free">бесплатно</span>
				<?php endif; ?>
			</span>
		</label>
	<?php endforeach; ?>
	<?php if ( ! $arctik_rates ) : ?>
		<p class="checkout__error">В&nbsp;этот город доставки пока нет&nbsp;— выберите другой город в&nbsp;пункте выдачи</p>
	<?php endif; ?>
</div>
