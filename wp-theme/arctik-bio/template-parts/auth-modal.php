<?php
/**
 * Попап входа (src/partials/auth-modal.html). Формы отправляет auth.js в обработчики inc/ajax/auth.php;
 * серверная ошибка поля (устаревшая ссылка сброса) — arctik_auth_field() из inc/auth.php.
 */

defined( 'ABSPATH' ) || exit;
?>
<?php /*
	Попап входа (макет 18126:59052 в копии Pky7FDIkoyGiepPQCMknnV): авторизация, регистрация, восстановление пароля.
	Экраны [data-js-auth-view] переключает js/modules/auth.js; открывают кнопки [data-js-auth-open="<экран>"]
	и адрес #auth-<экран> (ссылка из письма сброса пароля — #auth-reset).
	Имена полей — как в формах WooCommerce (username/password, email, user_login, password_1/password_2).
*/ ?>
<div class="auth" id="auth" data-js-auth inert>
	<div class="auth__overlay" data-js-auth-close></div>

	<div class="auth__dialog" role="dialog" aria-modal="true" aria-labelledby="auth-login-title" data-lenis-prevent data-js-auth-dialog>
		<button class="auth__icon-btn auth__back" type="button" aria-label="Назад" hidden data-js-auth-back data-js-swipe>
			<svg width="44" height="44" viewBox="0 0 44 44" fill="currentColor" aria-hidden="true">
				<path d="M20.3452 22.0072L25.9674 27.6294L24.2563 29.3405L16.923 22.0072L24.2563 14.6738L25.9674 16.3849L20.3452 22.0072Z"/>
			</svg>
		</button>
		<button class="auth__icon-btn auth__close" type="button" aria-label="Закрыть" data-js-auth-close>
			<svg width="44" height="44" viewBox="0 0 44 44" fill="currentColor" aria-hidden="true">
				<path d="M23.414 22L30 15.414L28.586 14L22 20.586L15.414 14L14 15.414L20.586 22L14 28.586L15.414 30L22 23.414L28.586 30L30 28.586L23.414 22Z"/>
			</svg>
		</button>

		<?php /* Авторизация */ ?>
		<section class="auth__view" data-js-auth-view="login" aria-labelledby="auth-login-title">
			<div class="auth__head">
				<h2 class="auth__title" id="auth-login-title">Авторизация</h2>
				<p class="auth__text">Войдите, чтобы отслеживать заказы</p>
			</div>
			<form class="auth__form" action="#" method="post" novalidate data-js-auth-form="login">
				<div class="auth__fields">
					<?php
					get_template_part( 'template-parts/form-field', null, [
						'id'           => 'auth-login-email',
						'type'         => 'email',
						'name'         => 'username',
						'label'        => 'Email',
						'autocomplete' => 'username',
						'attrs'        => 'data-validate="email"',
					] );
					?>
					<div class="auth__password">
						<?php
						get_template_part( 'template-parts/form-field-password', null, [
							'id'           => 'auth-login-password',
							'name'         => 'password',
							'label'        => 'Пароль',
							'autocomplete' => 'current-password',
							'attrs'        => 'data-validate="required" data-message="Введите пароль"',
						] );
						?>
						<button class="auth__link auth__link--muted" type="button" data-js-auth-go="forgot" data-js-roll>Забыли пароль?</button>
					</div>
				</div>
				<div class="auth__footer">
					<div class="auth__submit">
						<button class="btn btn--accent auth__btn" type="submit" name="login" data-js-swipe>Войти</button>
					</div>
					<p class="auth__switch">Нет аккаунта? <button class="auth__link" type="button" data-js-auth-go="register" data-js-roll>Зарегистрируйтесь</button></p>
				</div>
			</form>
		</section>

		<?php /* Регистрация */ ?>
		<section class="auth__view" data-js-auth-view="register" aria-labelledby="auth-register-title" hidden>
			<div class="auth__head">
				<h2 class="auth__title" id="auth-register-title">Регистрация</h2>
				<p class="auth__text">Войдите, чтобы отслеживать заказы</p>
			</div>
			<form class="auth__form" action="#" method="post" novalidate data-js-auth-form="register">
				<div class="auth__fields">
					<?php
					get_template_part( 'template-parts/form-field', null, [
						'id'           => 'auth-register-email',
						'type'         => 'email',
						'name'         => 'email',
						'label'        => 'Email',
						'autocomplete' => 'email',
						'attrs'        => 'data-validate="email"',
					] );
					?>
					<?php
					get_template_part( 'template-parts/form-field', null, [
						'id'           => 'auth-register-name',
						'type'         => 'text',
						'name'         => 'first_name',
						'label'        => 'Имя',
						'autocomplete' => 'given-name',
						'attrs'        => 'data-validate="required" data-message="Введите имя"',
					] );
					?>
					<?php
					get_template_part( 'template-parts/form-field-password', null, [
						'id'           => 'auth-register-password',
						'name'         => 'password',
						'label'        => 'Пароль',
						'autocomplete' => 'new-password',
						'attrs'        => 'data-validate="required" data-message="Введите пароль"',
					] );
					?>
					<?php
					get_template_part( 'template-parts/form-field-password', null, [
						'id'           => 'auth-register-password-repeat',
						'name'         => 'password_repeat',
						'label'        => 'Повторите пароль',
						'autocomplete' => 'new-password',
						'attrs'        => 'data-validate="repeat" data-repeat="auth-register-password"',
					] );
					?>
				</div>
				<div class="auth__footer">
					<div class="auth__submit">
						<label class="checkbox checkbox--inverse auth__checkbox" data-js-field>
							<input class="checkbox__input" type="checkbox" name="privacy_policy" data-js-field-input data-validate="checked">
							<span class="checkbox__box" aria-hidden="true"></span>
							<span class="checkbox__label">Я&nbsp;соглашаюсь с <a href="<?php echo esc_url( get_privacy_policy_url() ?: '#' ); ?>" target="_blank" rel="noopener" data-js-roll>политикой конфиденциальности</a><br> и&nbsp;<a href="#" target="_blank" rel="noopener" data-js-roll>пользовательским соглашением</a></span>
						</label>
						<button class="btn btn--accent auth__btn" type="submit" name="register" data-js-swipe>Зарегистрироваться</button>
					</div>
					<p class="auth__switch">Есть аккаунт? <button class="auth__link" type="button" data-js-auth-go="login" data-js-roll>Авторизируйтесь</button></p>
				</div>
			</form>
		</section>

		<?php /* Забыли пароль */ ?>
		<section class="auth__view" data-js-auth-view="forgot" aria-labelledby="auth-forgot-title" hidden>
			<div class="auth__head">
				<h2 class="auth__title" id="auth-forgot-title">Забыли пароль?</h2>
				<p class="auth__text auth__text--narrow">Введите email&nbsp;— отправим ссылку для&nbsp;сброса пароля.</p>
			</div>
			<form class="auth__form auth__form--compact" action="#" method="post" novalidate data-js-auth-form="forgot">
				<?php
				get_template_part( 'template-parts/form-field', null, arctik_auth_field( [
					'id'           => 'auth-forgot-email',
					'type'         => 'email',
					'name'         => 'user_login',
					'label'        => 'Email',
					'autocomplete' => 'email',
					'attrs'        => 'data-validate="email"',
				] ) );
				?>
				<button class="btn btn--accent auth__btn" type="submit" data-js-swipe>Отправить</button>
			</form>
		</section>

		<?php /* Письмо отправлено */ ?>
		<section class="auth__view" data-js-auth-view="sent" aria-labelledby="auth-sent-title" hidden>
			<div class="auth__head">
				<h2 class="auth__title" id="auth-sent-title">Письмо отправлено</h2>
				<p class="auth__text">Письмо отправлено на <span data-js-auth-sent-email>вашу почту</span>. Перейдите по&nbsp;ссылке в&nbsp;письме. Не&nbsp;пришло? Проверьте «Спам»</p>
			</div>
			<form class="auth__form auth__form--compact" action="#" method="post" novalidate data-js-auth-form="sent">
				<?php
				get_template_part( 'template-parts/form-field', null, [
					'id'           => 'auth-sent-email',
					'type'         => 'email',
					'name'         => 'user_login',
					'label'        => 'Email',
					'autocomplete' => 'email',
					'attrs'        => 'data-validate="email"',
				] );
				?>
				<button class="btn btn--accent auth__btn" type="submit" data-js-swipe>Отправить снова</button>
			</form>
		</section>

		<?php /* Новый пароль (по ссылке из письма) */ ?>
		<section class="auth__view" data-js-auth-view="reset" aria-labelledby="auth-reset-title" hidden>
			<div class="auth__head">
				<h2 class="auth__title" id="auth-reset-title">Создать новый пароль</h2>
			</div>
			<form class="auth__form auth__form--compact" action="#" method="post" novalidate data-js-auth-form="reset">
				<?php
				get_template_part( 'template-parts/form-field-password', null, [
					'id'           => 'auth-reset-password',
					'name'         => 'password_1',
					'label'        => 'Новый пароль',
					'autocomplete' => 'new-password',
					'attrs'        => 'data-validate="required" data-message="Введите пароль"',
				] );
				?>
				<?php
				get_template_part( 'template-parts/form-field-password', null, [
					'id'           => 'auth-reset-password-repeat',
					'name'         => 'password_2',
					'label'        => 'Повторить пароль',
					'autocomplete' => 'new-password',
					'attrs'        => 'data-validate="repeat" data-repeat="auth-reset-password"',
				] );
				?>
				<button class="btn btn--accent auth__btn" type="submit" data-js-swipe>Обновить пароль</button>
			</form>
		</section>
	</div>
</div>
