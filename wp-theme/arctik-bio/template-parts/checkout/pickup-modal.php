<?php
/**
 * Попап выбора пункта выдачи (src/partials/checkout/pickup-modal.html). Карта — Яндекс Карты, город и пункты — API СДЭК
 * (pickup.js запрашивает их у inc/ajax/delivery.php при открытии; window.arctik.pickup — inc/checkout.php).
 * Службы — ARCTIK_PICKUP_ACTIVE (пока только СДЭК; Озон — когда будет его API). Пункт списка — <template> из pickup-point.php.
 */

defined( 'ABSPATH' ) || exit;

$arctik_city     = arctik_city();
$arctik_services = array_intersect_key( ARCTIK_PICKUP_SERVICES, array_flip( ARCTIK_PICKUP_ACTIVE ) );
?>
<?php /*
	Попап выбора пункта выдачи (макет 18270:36436, список служб — 18271:38011; планшет 18270:37670; мобилка — 18263:87991 «На карте» и 18263:88439 «Списком»).
	Открывает [data-js-modal-open="pickup"] (js/modules/modal.js), выбор — js/modules/pickup.js.
*/ ?>
<div class="map-modal" id="pickup" data-js-modal="pickup" data-js-pickup inert>
	<div class="map-modal__overlay" data-js-modal-close></div>

	<div class="map-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="pickup-title" data-view="map" data-js-modal-dialog data-js-pickup-dialog>
		<?php /* Мобилка: «Назад» и вкладки */ ?>
		<div class="map-modal__bar">
			<button class="map-modal__back" type="button" aria-label="Назад" data-js-modal-close>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10.7984 12.0039L15.3984 16.6039L13.9984 18.0039L7.99844 12.0039L13.9984 6.00391L15.3984 7.40391L10.7984 12.0039Z"/></svg>
			</button>
			<div class="map-modal__tabs" role="tablist" aria-label="Вид">
				<button class="map-modal__tab" type="button" role="tab" aria-selected="true" data-js-pickup-tab="map">На&nbsp;карте</button>
				<button class="map-modal__tab" type="button" role="tab" aria-selected="false" data-js-pickup-tab="list">Списком</button>
			</div>
		</div>

		<div class="map-modal__map" data-js-pickup-map>
			<div class="map-modal__ymap" data-js-pickup-ymap></div>
			<?php /* Яндекс Карты не загрузились (pickup.js) */ ?>
			<p class="map-modal__map-error" hidden data-js-pickup-map-error>Карта не&nbsp;загрузилась&nbsp;— выберите пункт выдачи из&nbsp;списка</p>

			<button class="map-modal__city" type="button" data-js-pickup-city-open>
				<span data-js-pickup-city-name><?php echo esc_html( $arctik_city['name'] ); ?></span>
				<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.74935 10.7507L13.5827 6.91732L14.7493 8.08398L9.74935 13.084L4.74935 8.08398L5.91602 6.91732L9.74935 10.7507Z"/></svg>
			</button>

			<?php /* Фильтр служб на карте */ ?>
			<div class="map-modal__services">
				<?php foreach ( $arctik_services as $arctik_id => $arctik_service ) : ?>
					<button class="service-chip" type="button" aria-pressed="true" data-js-pickup-chip="<?php echo esc_attr( $arctik_id ); ?>" data-pickup-marker="<?php echo esc_url( arctik_asset( 'icons/map-marker-' . $arctik_id . '.svg' ) ); ?>">
						<img class="service-chip__logo" src="<?php echo esc_url( arctik_asset( $arctik_service['logo'] ) ); ?>" alt="" width="24" height="24">
						<span><?php echo esc_html( $arctik_service['name'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="map-modal__panel" data-lenis-prevent>
			<div class="map-modal__head">
				<h2 class="map-modal__title" id="pickup-title">Пункт выдачи</h2>
				<button class="map-modal__close" type="button" aria-label="Закрыть" data-js-modal-close>
					<svg width="44" height="44" viewBox="0 0 44 44" fill="currentColor" aria-hidden="true">
						<path d="M23.414 22L30 15.414L28.586 14L22 20.586L15.414 14L14 15.414L20.586 22L14 28.586L15.414 30L22 23.414L28.586 30L30 28.586L23.414 22Z"/>
					</svg>
				</button>
			</div>

			<div class="map-modal__controls">
				<button class="map-modal__city-edit" type="button" data-js-pickup-city-open>
					<span data-js-pickup-city-label><?php echo esc_html( $arctik_city['label'] ); ?></span>
					<img src="<?php echo esc_url( arctik_asset( 'icons/icn-edit.svg' ) ); ?>" alt="" width="16" height="16">
				</button>

				<?php /* Выбор города (макета нет): поле с подсказками СДЭК вместо строки города */ ?>
				<div class="map-modal__suggest" hidden data-js-pickup-city>
					<?php get_template_part( 'template-parts/form-field', null, [ 'id' => 'pickup-city', 'type' => 'text', 'name' => 'pickup_city', 'label' => 'Город', 'autocomplete' => 'off', 'mod' => 'field--light', 'attrs' => 'role="combobox" aria-expanded="false" aria-controls="pickup-city-suggest" aria-autocomplete="list" data-js-pickup-city-input' ] ); ?>
					<ul class="map-modal__suggest-list" id="pickup-city-suggest" role="listbox" aria-label="Подсказки города" hidden data-js-pickup-city-list></ul>
				</div>

				<div class="select" data-js-pickup-select>
					<button class="select__btn" type="button" aria-haspopup="listbox" aria-expanded="false" aria-controls="pickup-services" data-js-pickup-select-btn>
						<span class="select__value" data-js-pickup-select-value>Службы выдачи</span>
						<svg class="select__chevron" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.6992 12.9008L16.2992 8.30078L17.6992 9.70078L11.6992 15.7008L5.69922 9.70078L7.09922 8.30078L11.6992 12.9008Z"/></svg>
					</button>
					<ul class="select__list" id="pickup-services" role="listbox" aria-label="Службы выдачи" hidden data-js-pickup-select-list>
						<?php foreach ( $arctik_services as $arctik_id => $arctik_service ) : ?>
							<li class="select__option" role="option" tabindex="-1" aria-selected="false" data-value="<?php echo esc_attr( $arctik_id ); ?>"><?php echo esc_html( $arctik_service['name'] ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<?php /* Мобилка, вкладка «Списком» */ ?>
				<div class="map-modal__search">
					<?php get_template_part( 'template-parts/form-field', null, [ 'id' => 'pickup-search', 'type' => 'search', 'name' => 'pickup_search', 'label' => 'Найти адрес', 'autocomplete' => 'off', 'mod' => 'field--light', 'attrs' => 'data-js-pickup-search' ] ); ?>
				</div>
			</div>

			<?php /* Пункты — из API при открытии (pickup.js) */ ?>
			<ul class="map-modal__list" data-js-pickup-list></ul>
			<template data-js-pickup-point-template>
				<?php get_template_part( 'template-parts/checkout/pickup-point' ); ?>
			</template>
			<p class="map-modal__empty" hidden data-js-pickup-empty>В&nbsp;этом городе пунктов выдачи нет&nbsp;— выберите другой город</p>
		</div>
	</div>
</div>
