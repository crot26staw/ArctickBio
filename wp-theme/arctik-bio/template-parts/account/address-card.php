<?php
/**
 * Адрес в «Моих адресах» (src/partials/account/address-card.html): радиокнопка — основной адрес, «Изменить», удалить (account.js).
 * $args: id, address — [ name, phone, address_1, address_2, country, city, postcode ] (inc/account.php), checked — основной.
 * Поля адреса — в data-атрибутах карточки: из них «Изменить» заполняет форму.
 */

defined( 'ABSPATH' ) || exit;

$arctik_address = $args['address'] ?? [];
$arctik_data    = '';
foreach ( ARCTIK_ADDRESS_FIELDS as $arctik_key ) {
	$arctik_data .= sprintf( ' data-address-%s="%s"', str_replace( '_', '', $arctik_key ), esc_attr( $arctik_address[ $arctik_key ] ?? '' ) );
}
?>
<?php /* Адрес (макет 18275:43216) */ ?>
<li class="address-card" data-js-address-card<?php echo $arctik_data; // phpcs:ignore WordPress.Security.EscapeOutput -- экранировано выше ?>>
	<label class="address-card__main">
		<input class="address-card__input" type="radio" name="default_address" value="<?php echo esc_attr( $args['id'] ?? '' ); ?>"<?php checked( ! empty( $args['checked'] ) ); ?>>
		<span class="address-card__radio" aria-hidden="true"></span>
		<span class="address-card__text">
			<span class="address-card__name" data-js-address-card-name><?php echo esc_html( $arctik_address['name'] ?? '' ); ?></span>
			<span class="address-card__address" data-js-address-card-address><?php echo esc_html( arctik_address_line( $arctik_address ) ); ?></span>
		</span>
	</label>
	<div class="address-card__actions">
		<button class="btn btn--neutral btn--small" type="button" data-js-address-edit data-js-swipe>Изменить</button>
		<button class="address-card__remove" type="button" aria-label="Удалить адрес" data-js-address-remove data-js-swipe>
			<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 21C6.45 21 5.97917 20.8042 5.5875 20.4125C5.19583 20.0208 5 19.55 5 19V6H4V4H9V3H15V4H20V6H19V19C19 19.55 18.8042 20.0208 18.4125 20.4125C18.0208 20.8042 17.55 21 17 21H7ZM17 6H7V19H17V6Z"/></svg>
		</button>
	</div>
</li>
