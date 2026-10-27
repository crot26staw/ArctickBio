<?php
/**
 * Пункт выдачи в списке попапа (src/partials/checkout/pickup-point.html). $args: id, service, title, address, price, date.
 */

defined( 'ABSPATH' ) || exit;
?>
<?php /* Пункт выдачи в списке попапа (макет 18248:74613, выбранный — 18271:37842): радиокнопка, у выбранного — «Привезти сюда».
		 data-service — служба (фильтр), data-js-pickup-point — id, по нему же связана метка на карте */ ?>
<li class="pickup-point" data-service="<?php echo esc_html( $args['service'] ?? '' ); ?>" data-js-pickup-point="<?php echo esc_html( $args['id'] ?? '' ); ?>">
	<label class="pickup-point__label">
		<input class="pickup-point__input" type="radio" name="pickup_choice" value="<?php echo esc_html( $args['id'] ?? '' ); ?>" data-js-pickup-input>
		<span class="pickup-point__radio" aria-hidden="true"></span>
		<span class="pickup-point__body">
			<span class="pickup-point__title" data-js-pickup-point-title><?php echo esc_html( $args['title'] ?? '' ); ?></span>
			<span class="pickup-point__info">
				<span data-js-pickup-point-address><?php echo esc_html( $args['address'] ?? '' ); ?></span>
				<span class="pickup-point__price">стоимость <span aria-hidden="true">—</span> <?php echo esc_html( $args['price'] ?? '' ); ?></span>
				<span>дата доставки&nbsp;— <?php echo esc_html( $args['date'] ?? '' ); ?></span>
			</span>
		</span>
	</label>
	<button class="btn btn--accent pickup-point__btn" type="button" data-js-pickup-choose data-js-swipe>Привезти сюда</button>
</li>
