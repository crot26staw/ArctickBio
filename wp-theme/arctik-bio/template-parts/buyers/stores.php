<?php
/**
 * «Где нас купить?» (src/partials/buyers/stores.html): города с адресами — поля страницы (вкладка «Где нас купить?»).
 * Нет городов — секция не выводится; открыт первый город, выбран его первый адрес.
 * У каждого адреса своя Яндекс Карта (код из конструктора, arctik_yandex_map_url()), нет — карта по умолчанию;
 * переключение — js/modules/stores.js.
 */

defined( 'ABSPATH' ) || exit;

$arctik_page    = (int) get_the_ID();
$arctik_title   = (string) arctik_field( 'arctik_stores_title', $arctik_page );
$arctik_default = arctik_yandex_map_url( (string) arctik_field( 'arctik_stores_map_embed', $arctik_page ) );

$arctik_cities = [];
foreach ( (array) arctik_field( 'arctik_stores_cities', $arctik_page ) as $arctik_city ) {
	if ( '' === trim( (string) $arctik_city['name'] ) ) {
		continue;
	}

	$arctik_points = [];
	foreach ( (array) ( $arctik_city['points'] ?? [] ) as $arctik_point ) {
		if ( '' !== trim( (string) $arctik_point['address'] ) ) {
			$arctik_points[] = [
				'address' => trim( (string) $arctik_point['address'] ),
				'map'     => arctik_yandex_map_url( (string) $arctik_point['map'] ),
			];
		}
	}

	$arctik_cities[] = [ 'name' => (string) $arctik_city['name'], 'points' => $arctik_points ];
}

if ( ! $arctik_cities ) {
	return;
}

// Сначала на карте — первый адрес первого города
$arctik_first = $arctik_cities[0]['points'][0] ?? null;
$arctik_map   = ( $arctik_first['map'] ?? '' ) ?: $arctik_default;
$arctik_maps  = $arctik_default || array_filter( array_merge( ...array_map( fn( $city ) => array_column( $city['points'], 'map' ), $arctik_cities ) ) );
?>
<section class="stores" id="where-to-buy" data-header-theme="light" data-js-stores>
	<?php if ( $arctik_title ) : ?>
		<h2 class="stores__title" data-js-title-reveal><?php echo arctik_text( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
	<?php endif; ?>

	<div class="stores__body" data-js-fade-up-group>
		<?php // Города: аккордеоны (js/modules/accordion.js), открыт один; список прокручивается внутри карточки. Адреса — кнопки выбора карты ?>
		<div class="stores__list" data-js-accordion-group data-lenis-prevent>
			<?php foreach ( $arctik_cities as $arctik_index => $arctik_city ) : ?>
				<?php
				$arctik_open = 0 === $arctik_index;
				$arctik_id   = 'store-city-' . ( $arctik_index + 1 );
				?>
				<div class="store-city<?php echo $arctik_open ? ' is-open' : ''; ?>" data-js-accordion>
					<button class="store-city__header" type="button" aria-expanded="<?php echo $arctik_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $arctik_id ); ?>" data-js-accordion-toggle>
						<span class="store-city__name"><?php echo arctik_text( $arctik_city['name'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<span class="store-city__icon icon-btn icon-btn--24" aria-hidden="true">
							<img src="<?php echo esc_url( arctik_asset( 'icons/icn-plus.svg' ) ); ?>" alt="" width="18" height="18">
						</span>
					</button>
					<div class="store-city__panel" id="<?php echo esc_attr( $arctik_id ); ?>" data-js-accordion-panel>
						<?php if ( $arctik_city['points'] ) : ?>
							<ul class="store-city__addresses">
								<?php foreach ( $arctik_city['points'] as $arctik_point_index => $arctik_point ) : ?>
									<?php $arctik_active = $arctik_open && 0 === $arctik_point_index; ?>
									<li><button class="store-city__address<?php echo $arctik_active ? ' is-active' : ''; ?>" type="button" aria-pressed="<?php echo $arctik_active ? 'true' : 'false'; ?>" data-js-stores-address<?php echo $arctik_point['map'] ? ' data-map="' . esc_url( $arctik_point['map'] ) . '"' : ''; ?> data-js-roll><?php echo arctik_text( $arctik_point['address'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $arctik_maps ) : ?>
			<div class="stores__map">
				<iframe class="stores__map-frame"<?php echo $arctik_map ? ' src="' . esc_url( $arctik_map ) . '"' : ''; ?><?php echo $arctik_default ? ' data-default-map="' . esc_url( $arctik_default ) . '"' : ''; ?> data-js-stores-map title="Карта магазинов" loading="lazy" allowfullscreen></iframe>
			</div>
		<?php endif; ?>
	</div>
</section>
