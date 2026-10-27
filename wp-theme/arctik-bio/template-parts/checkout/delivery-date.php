<?php
/**
 * Дата доставки курьером (src/partials/checkout/delivery-date.html). $args: value (Y-m-d), day («Ср, 30 сент»), price, checked.
 */

defined( 'ABSPATH' ) || exit;
?>
<?php /* Дата доставки курьером (макет 18252:77174): радиокнопка-плашка, выбранная — рамка 2px */ ?>
<label class="checkout-date">
	<input class="checkout-date__input" type="radio" name="delivery_date" value="<?php echo esc_attr( $args['value'] ?? '' ); ?>" <?php checked( ! empty( $args['checked'] ) ); ?>>
	<span><?php echo esc_html( $args['day'] ?? '' ); ?></span>
	<span><?php echo esc_html( $args['price'] ?? '' ); ?></span>
</label>
