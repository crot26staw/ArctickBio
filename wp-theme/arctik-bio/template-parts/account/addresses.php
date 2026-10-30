<?php
/**
 * «Мои адреса» (src/partials/account/addresses.html): список адресов (inc/account.php, мета arctik_addresses) и форма.
 * «Добавить адрес» / «Изменить» открывают форму, «Назад» — обратно к списку; сохранение, удаление и выбор основного — AJAX,
 * ответ — новый список карточек. Основной адрес копируется в адрес доставки WC (им заполняется курьер на оформлении).
 */

defined( 'ABSPATH' ) || exit;

$arctik_fields = [
	[ 'shipping_name', 'text', 'shipping_full_name', "Имя и&nbsp;Фамилия", 'name', 'data-validate="required" data-message="Обязательное поле"' ],
	[ 'shipping_phone', 'tel', 'shipping_phone', 'Телефон', 'tel', 'data-validate="phone" data-optional data-js-phone-mask' ],
	[ 'shipping_address_1', 'text', 'shipping_address_1', 'Адрес*', 'address-line1', 'data-validate="required" data-message="Обязательное поле"' ],
	[ 'shipping_address_2', 'text', 'shipping_address_2', 'Квартира, номер', 'address-line2', '' ],
	[ 'shipping_country', 'text', 'shipping_country', 'Страна', 'country-name', '' ],
	[ 'shipping_city', 'text', 'shipping_city', 'Город', 'address-level2', '' ],
	[ 'shipping_postcode', 'text', 'shipping_postcode', 'Почтовый индекс', 'postal-code', '' ],
];
?>
<?php /*
	«Мои адреса» (макет: нет адресов — 16692:130470, форма — 16692:130572, с адресом — 16692:130673;
	планшет 18275:43279 / 18277:43532 / 18277:43628, мобилка 18275:43390 / 18277:43758 / 18277:43854)
*/ ?>
<div class="account__head">
	<h1 class="account__title">Аккаунт</h1>
</div>
<?php get_template_part( 'template-parts/account/tabs', null, [ 'active' => 'edit-address' ] ); ?>
<div class="account__body" data-js-addresses>
	<h2 class="account-form__title">Управление адресами</h2>

	<div class="account-addresses" data-js-addresses-list>
		<ul class="account-addresses__list" data-js-addresses-items>
			<?php arctik_address_cards(); ?>
		</ul>
		<button class="btn btn--dark account-addresses__add" type="button" data-js-addresses-add data-js-swipe>Добавить адрес</button>
	</div>

	<form class="account-form account-form--address" action="#" method="post" novalidate hidden data-js-addresses-form>
		<input type="hidden" name="address_id" value="">
		<div class="account-form__fields">
			<?php
			foreach ( $arctik_fields as [ $arctik_id, $arctik_type, $arctik_name, $arctik_label, $arctik_autocomplete, $arctik_attrs ] ) {
				get_template_part( 'template-parts/form-field', null, [
					'id'           => $arctik_id,
					'type'         => $arctik_type,
					'name'         => $arctik_name,
					'label'        => $arctik_label,
					'autocomplete' => $arctik_autocomplete,
					'mod'          => 'field--light',
					'attrs'        => $arctik_attrs,
				] );
			}
			?>
		</div>
		<div class="account-form__actions">
			<button class="btn btn--dark" type="submit" name="save_address" data-js-addresses-submit data-js-swipe>Добавить адрес</button>
			<button class="btn account-form__back" type="button" data-js-addresses-back data-js-swipe>Назад</button>
		</div>
	</form>
</div>
<?php get_template_part( 'template-parts/account/logout', null, [ 'mod' => 'account-logout--page' ] ); ?>
