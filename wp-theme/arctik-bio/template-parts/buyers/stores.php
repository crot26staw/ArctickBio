<?php
/**
 * «Где нас купить?» (src/partials/buyers/stores.html): города с адресами и карта — поля страницы
 * (вкладка «Где нас купить?»). Нет городов — секция не выводится; открыт первый город.
 * Карта — Яндекс Карта в iframe (код из конструктора или «Поделиться → Встроить», arctik_yandex_map_url()).
 */

defined( 'ABSPATH' ) || exit;

$arctik_page  = (int) get_the_ID();
$arctik_title = (string) arctik_field( 'arctik_stores_title', $arctik_page );
$arctik_map   = arctik_yandex_map_url( (string) arctik_field( 'arctik_stores_map_embed', $arctik_page ) );

$arctik_cities = [];
foreach ( (array) arctik_field( 'arctik_stores_cities', $arctik_page ) as $arctik_city ) {
	if ( '' === trim( (string) $arctik_city['name'] ) ) {
		continue;
	}

	$arctik_cities[] = [
		'name'      => (string) $arctik_city['name'],
		'addresses' => array_filter( array_map( 'trim', preg_split( '/\R/', (string) $arctik_city['addresses'] ) ) ),
	];
}

if ( ! $arctik_cities ) {
	return;
}
?>
<section class="stores" id="where-to-buy" data-header-theme="light">
	<?php if ( $arctik_title ) : ?>
		<h2 class="stores__title" data-js-title-reveal><?php echo arctik_text( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
	<?php endif; ?>

	<div class="stores__body" data-js-fade-up-group>
		<?php // Города: аккордеоны (js/modules/accordion.js), открыт один; список прокручивается внутри карточки ?>
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
						<?php if ( $arctik_city['addresses'] ) : ?>
							<ul class="store-city__addresses">
								<?php foreach ( $arctik_city['addresses'] as $arctik_address ) : ?>
									<li><?php echo arctik_text( $arctik_address ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $arctik_map ) : ?>
			<div class="stores__map">
				<iframe class="stores__map-frame" src="<?php echo esc_url( $arctik_map ); ?>" title="Карта магазинов" loading="lazy" allowfullscreen></iframe>
			</div>
		<?php endif; ?>
	</div>
</section>
