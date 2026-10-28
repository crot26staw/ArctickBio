<?php
/**
 * Баннер cookie и окно настроек (src/partials/cookies.html, этап Ку). Тексты — «Настройки сайта → Cookie» (inc/fields/cookies.php).
 * Полоса не выводится, если выбор уже сделан (cookie arctik_cookie_consent; cookies.js проверяет то же — на случай кеша страниц),
 * окно настроек выводится всегда — его открывает любая кнопка [data-js-modal-open="cookies"].
 */

defined( 'ABSPATH' ) || exit;

// Текст из rich text — только строчные теги (как подпись в «Покупателям»)
$arctik_inline = static function ( string $name ): string {
	$html = wp_kses( (string) arctik_option( $name ), [ 'a' => [ 'href' => [], 'target' => [], 'rel' => [] ], 'strong' => [], 'em' => [], 'br' => [] ] );

	return arctik_typograph( trim( $html ) );
};

$arctik_banner_text = $arctik_inline( 'arctik_cookie_banner_text' );
$arctik_text        = $arctik_inline( 'arctik_cookie_text' );
$arctik_title       = (string) arctik_option( 'arctik_cookie_title' ) ?: 'Мы используем файлы cookie';

$arctik_categories = [
	'necessary' => [ 'name' => 'Необходимые', 'label' => 'Необходимые cookie — всегда включены' ],
	'analytics' => [ 'name' => 'Аналитика', 'label' => 'Разрешить cookie аналитики' ],
	'marketing' => [ 'name' => 'Маркетинг и реклама', 'label' => 'Разрешить рекламные cookie' ],
];
?>
<?php /* Баннер cookie (макет 18129:38720 в файле XXwoaWCLuNuh8GMCHy1GcG) и окно настроек (18543:95242) */ ?>
<?php if ( ! isset( $_COOKIE['arctik_cookie_consent'] ) ) : ?>
	<section class="cookies" aria-label="Файлы cookie" hidden data-js-cookies>
		<div class="cookies__inner container">
			<p class="cookies__text"><?php echo $arctik_banner_text; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_kses выше ?></p>
			<div class="cookies__actions">
				<button class="btn btn--light cookies__btn" type="button" data-js-modal-open="cookies" data-js-swipe>Настроить</button>
				<button class="btn btn--dark cookies__btn" type="button" data-js-cookies-accept data-js-swipe>Хорошо</button>
			</div>
		</div>
	</section>
<?php endif; ?>

<div class="cookie-settings" data-js-modal="cookies" inert>
	<div class="cookie-settings__overlay" data-js-modal-close></div>

	<div class="cookie-settings__dialog" role="dialog" aria-modal="true" aria-labelledby="cookie-settings-title" data-js-modal-dialog>
		<form class="cookie-settings__form" action="#" method="post" data-js-cookies-form>
			<div class="cookie-settings__body" data-lenis-prevent>
				<div class="cookie-settings__head">
					<h2 class="cookie-settings__title" id="cookie-settings-title"><?php echo arctik_text( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<?php if ( $arctik_text ) : ?>
						<p class="cookie-settings__text"><?php echo $arctik_text; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_kses выше ?></p>
					<?php endif; ?>
				</div>

				<div class="cookie-settings__groups" role="group" aria-labelledby="cookie-settings-legend">
					<p class="cookie-settings__legend" id="cookie-settings-legend">Вы разрешаете:</p>
					<?php
					foreach ( $arctik_categories as $arctik_key => $arctik_category ) {
						$arctik_required = 'necessary' === $arctik_key;

						get_template_part( 'template-parts/cookie-group', null, [
							'category' => $arctik_key,
							'name'     => (string) arctik_option( "arctik_cookie_{$arctik_key}_name" ) ?: $arctik_category['name'],
							'text'     => (string) arctik_option( "arctik_cookie_{$arctik_key}_text" ),
							'label'    => $arctik_category['label'],
							'open'     => $arctik_required,
							'required' => $arctik_required,
							'badge'    => $arctik_required ? 'Обязательно' : '',
						] );
					}
					?>
				</div>
			</div>

			<button class="cookie-settings__close" type="button" aria-label="Закрыть" data-js-modal-close>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
					<path d="M13.06 12L18 7.06L16.94 6L12 10.94L7.06 6L6 7.06L10.94 12L6 16.94L7.06 18L12 13.06L16.94 18L18 16.94L13.06 12Z"/>
				</svg>
			</button>

			<button class="btn btn--dark cookie-settings__submit" type="submit" data-js-swipe>Принять cookie</button>
		</form>
	</div>
</div>
