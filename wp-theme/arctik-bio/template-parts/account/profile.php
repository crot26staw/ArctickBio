<?php
/**
 * «Аккаунт» (src/partials/account/profile.html): имя, смена данных и пароля.
 * Поля — как в WooCommerce edit-account; отправка — AJAX (account.js → inc/ajax/account.php).
 * «Имя» — имя и фамилия через пробел (first_name / last_name), кнопки неактивны, пока нечего сохранять.
 */

defined( 'ABSPATH' ) || exit;

$arctik_user = wp_get_current_user();
$arctik_name = arctik_account_name( $arctik_user );
?>
<?php /* «Аккаунт» (макет 16692:130199, планшет 18275:42634, мобилка 18263:89334) */ ?>
<div class="account__head">
	<h1 class="account__title">Аккаунт</h1>
</div>
<?php get_template_part( 'template-parts/account/tabs', null, [ 'active' => 'edit-account' ] ); ?>
<div class="account__body account__body--profile">
	<div class="account-user">
		<span class="account-user__avatar" aria-hidden="true">
			<svg width="34" height="34" viewBox="0 0 34.2222 34.2222" fill="currentColor"><path d="M13.0829 15.4356C11.9659 14.3187 11.4074 12.9759 11.4074 11.4074C11.4074 9.83889 11.9659 8.49614 13.0829 7.37917C14.1998 6.26219 15.5426 5.7037 17.1111 5.7037C18.6796 5.7037 20.0224 6.26219 21.1394 7.37917C22.2563 8.49614 22.8148 9.83889 22.8148 11.4074C22.8148 12.9759 22.2563 14.3187 21.1394 15.4356C20.0224 16.5526 18.6796 17.1111 17.1111 17.1111C15.5426 17.1111 14.1998 16.5526 13.0829 15.4356ZM5.7037 28.5185V24.5259C5.7037 23.7179 5.91165 22.9752 6.32755 22.2979C6.74344 21.6206 7.29599 21.1037 7.98519 20.7472C9.45864 20.0105 10.9559 19.4579 12.4769 19.0896C13.9978 18.7212 15.5426 18.537 17.1111 18.537C18.6796 18.537 20.2244 18.7212 21.7454 19.0896C23.2664 19.4579 24.7636 20.0105 26.237 20.7472C26.9262 21.1037 27.4788 21.6206 27.8947 22.2979C28.3106 22.9752 28.5185 23.7179 28.5185 24.5259V28.5185H5.7037ZM8.55556 25.6667H25.6667V24.5259C25.6667 24.2645 25.6013 24.0269 25.4706 23.813C25.3399 23.5991 25.1676 23.4327 24.9537 23.3139C23.6704 22.6722 22.3752 22.191 21.0681 21.8701C19.761 21.5493 18.442 21.3889 17.1111 21.3889C15.7802 21.3889 14.4613 21.5493 13.1542 21.8701C11.8471 22.191 10.5519 22.6722 9.26852 23.3139C9.05463 23.4327 8.88233 23.5991 8.75162 23.813C8.62091 24.0269 8.55556 24.2645 8.55556 24.5259V25.6667ZM19.1252 13.4215C19.6837 12.863 19.963 12.1917 19.963 11.4074C19.963 10.6231 19.6837 9.95178 19.1252 9.39329C18.5667 8.8348 17.8954 8.55556 17.1111 8.55556C16.3269 8.55556 15.6555 8.8348 15.097 9.39329C14.5385 9.95178 14.2593 10.6231 14.2593 11.4074C14.2593 12.1917 14.5385 12.863 15.097 13.4215C15.6555 13.98 16.3269 14.2593 17.1111 14.2593C17.8954 14.2593 18.5667 13.98 19.1252 13.4215Z"/></svg>
		</span>
		<p class="account-user__text">
			<span class="account-user__label">Имя</span>
			<span class="account-user__name" data-js-account-name><?php echo esc_html( $arctik_name ); ?></span>
		</p>
	</div>

	<form class="account-form woocommerce-EditAccountForm edit-account" action="#" method="post" novalidate data-js-account-form="details">
		<fieldset class="account-form__group">
			<legend class="account-form__title">Изменить информацию об&nbsp;аккаунте</legend>
			<div class="account-form__fields">
				<?php
				get_template_part( 'template-parts/form-field', null, [
					'id'           => 'account_display_name',
					'name'         => 'account_display_name',
					'label'        => 'Имя',
					'autocomplete' => 'name',
					'mod'          => 'field--light',
					'value'        => $arctik_name,
					'attrs'        => 'data-validate="required" data-message="Обязательное поле"',
				] );
				get_template_part( 'template-parts/form-field', null, [
					'id'           => 'account_email',
					'type'         => 'email',
					'name'         => 'account_email',
					'label'        => 'Email',
					'autocomplete' => 'email',
					'mod'          => 'field--light',
					'value'        => $arctik_user->user_email,
					'attrs'        => 'data-validate="email" data-message="Обязательное поле"',
				] );
				?>
			</div>
		</fieldset>
		<button class="btn account-form__btn" type="submit" name="save_account_details" disabled data-js-account-submit data-js-swipe>Сохранить</button>
	</form>

	<form class="account-form" action="#" method="post" novalidate data-js-account-form="password">
		<fieldset class="account-form__group">
			<legend class="account-form__title">Изменить пароль</legend>
			<div class="account-form__fields">
				<?php
				foreach ( [
					[ 'password_current', 'Текущий пароль*', 'current-password', 'data-validate="required" data-message="Обязательное поле"' ],
					[ 'password_1', 'Новый пароль*', 'new-password', 'data-validate="required" data-message="Обязательное поле"' ],
					[ 'password_2', 'Подтвердите пароль*', 'new-password', 'data-validate="repeat" data-repeat="password_1"' ],
				] as [ $arctik_field, $arctik_label, $arctik_autocomplete, $arctik_attrs ] ) {
					get_template_part( 'template-parts/form-field-password', null, [
						'id'           => $arctik_field,
						'name'         => $arctik_field,
						'label'        => $arctik_label,
						'autocomplete' => $arctik_autocomplete,
						'mod'          => 'field--light',
						'attrs'        => $arctik_attrs,
					] );
				}
				?>
			</div>
		</fieldset>
		<button class="btn account-form__btn" type="submit" name="save_password" disabled data-js-account-submit data-js-swipe>Обновить</button>
	</form>
</div>
<?php get_template_part( 'template-parts/account/logout', null, [ 'mod' => 'account-logout--page' ] ); ?>
