<?php
/**
 * Первый экран карточки товара (src/partials/product/main.html): галерея фото + панель покупки, макет 16581:44492.
 * Разметка панели под WooCommerce: .product_title, .price, form.cart (add-to-cart — ID товара, формат — радио variation_id,
 * .quantity, .single_add_to_cart_button). Без JS форма отправляется обычным POST (обработчик WC), с JS — AJAX из cart.js
 * (data-js-cart-form).
 *
 * Фото — «Галерея товара» (первое — большое), пусто — «Изображение товара». Форматы — вариации (arctik_product_formats()),
 * у простого товара (набор на 3 месяца) выбора формата нет. Нет в наличии ни одного — кнопка неактивна, «Нет в наличии».
 * Ниже формы — аккордеоны (arctik_product_details()), ингредиенты (у товара или общие) и «Другие вкусы» (arctik_products()).
 *
 * $args: product — WC_Product.
 */

defined( 'ABSPATH' ) || exit;

/** @var WC_Product|null $arctik_product */
$arctik_product = $args['product'] ?? null;
if ( ! $arctik_product instanceof WC_Product ) {
	return;
}

$arctik_photos = $arctik_product->get_gallery_image_ids() ?: array_filter( [ $arctik_product->get_image_id() ] );

$arctik_formats = arctik_product_formats( $arctik_product );
$arctik_checked = array_values( array_filter( $arctik_formats, fn( $format ) => $format['checked'] ) )[0] ?? null;

// Товар, который уйдёт в корзину сейчас: выбранная вариация или сам товар (простой).
if ( $arctik_product->is_type( 'variable' ) ) {
	$arctik_selected  = $arctik_checked['variation'] ?? null;
	$arctik_available = (bool) $arctik_selected;
} else {
	$arctik_selected  = $arctik_product;
	$arctik_available = $arctik_product->is_purchasable() && $arctik_product->is_in_stock();
}

// Ошибка добавления в корзину из уведомлений WC («+» у карточки, форма без JS; на сайте они нигде не выводятся — снимаем)
$arctik_error = arctik_cart_notice();

$arctik_price    = arctik_price_text( $arctik_selected ? wc_get_price_to_display( $arctik_selected ) : $arctik_product->get_price() );
$arctik_max      = $arctik_selected ? arctik_product_max_qty( $arctik_selected ) : 99;
$arctik_delivery = (string) arctik_option( 'arctik_products_delivery_note' );

$arctik_details = arctik_product_details( $arctik_product );

$arctik_ingredients = array_filter( (array) arctik_product_content( $arctik_product, 'ingredients' ), fn( $item ) => ! empty( $item['title'] ) );

// «Другие вкусы»: все товары-вкусы, кроме этого.
$arctik_related = array_filter( arctik_products(), fn( $item ) => $item->get_id() !== $arctik_product->get_id() );
?>
<section class="product-main product" data-header-theme="dark">
	<div class="product-main__bg" aria-hidden="true">
		<img src="<?php echo esc_url( arctik_asset( 'images/product-bg.webp' ) ); ?>" alt="" width="936" height="1024" fetchpriority="high">
	</div>

	<?php // На десктопе — сетка, на планшете/мобилке — лента, листается свайпом, если шире экрана (drag-slider.js). ?>
	<div class="product-main__gallery woocommerce-product-gallery" data-js-drag-slider data-drag-slider-align="start">
		<div class="product-main__photos" data-js-drag-slider-track>
			<?php
			foreach ( array_values( $arctik_photos ) as $arctik_index => $arctik_photo ) {
				$arctik_attr = [ 'class' => 'product-main__photo' . ( 0 === $arctik_index ? ' product-main__photo--main' : '' ) ];
				// Первые три фото — на первом экране.
				if ( $arctik_index < 3 ) {
					$arctik_attr['loading'] = 'eager';
				}
				if ( 0 === $arctik_index ) {
					$arctik_attr['fetchpriority'] = 'high';
				}
				echo arctik_image( (int) $arctik_photo, 'full', $arctik_attr ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</div>
	</div>

	<?php // Светлая панель: когда она уходит под шапку, шапка переключается на светлую тему (header.js берёт последний [data-header-theme]). ?>
	<div class="product-main__panel summary entry-summary" data-header-theme="light">
		<div class="product-main__heading">
			<h1 class="product-main__title product_title" data-js-title-reveal data-title-reveal-start="top bottom"><?php echo arctik_text( arctik_product_full_title( $arctik_product ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
			<p class="product-main__price price" data-js-product-price><?php echo esc_html( $arctik_price ); ?></p>
		</div>

		<form class="product-main__form cart" action="<?php echo esc_url( $arctik_product->get_permalink() ); ?>" method="post" data-js-sticky-buy-form data-js-cart-form>
			<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $arctik_product->get_id() ); ?>">
			<?php if ( $arctik_formats ) : ?>
				<fieldset class="product-main__formats">
					<legend class="product-main__subtitle">Выберите формат</legend>
					<div class="product-main__formats-list">
						<?php
						foreach ( $arctik_formats as $arctik_format ) {
							get_template_part( 'template-parts/product-format', null, [
								'format'  => $arctik_format,
								'product' => $arctik_product,
							] );
						}
						?>
					</div>
				</fieldset>
			<?php endif; ?>

			<div class="product-main__buy">
				<div class="product-main__actions" data-js-sticky-buy-source>
					<?php get_template_part( 'template-parts/quantity', null, [ 'name' => 'quantity', 'max' => $arctik_max, 'disabled' => ! $arctik_available ] ); ?>
					<button class="product-main__cart btn btn--accent single_add_to_cart_button button" type="submit" data-js-swipe<?php disabled( ! $arctik_available ); ?>>
						<?php if ( $arctik_available ) : ?>
							<span>В&nbsp;корзину</span>
							<span data-js-product-price><?php echo esc_html( $arctik_price ); ?></span>
						<?php else : ?>
							<span>Нет в&nbsp;наличии</span>
						<?php endif; ?>
					</button>
				</div>
				<?php /* Ошибка добавления в корзину: AJAX — cart.js; после «+» у карточки или отправки формы без JS — уведомление WC */ ?>
				<p class="product-main__error" role="alert"<?php echo $arctik_error ? '' : ' hidden'; ?> data-js-cart-form-error><?php echo esc_html( $arctik_error ); ?></p>
				<?php if ( $arctik_delivery ) : ?>
					<p class="product-main__delivery">
						<img src="<?php echo esc_url( arctik_asset( 'icons/icn-box.svg' ) ); ?>" alt="" width="24" height="24">
						<?php echo arctik_text( $arctik_delivery ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</p>
				<?php endif; ?>
			</div>
		</form>

		<?php if ( $arctik_details ) : ?>
			<?php // Описание товара: аккордеоны (accordion.js), открыт один пункт — первый. ?>
			<div class="product-main__details" data-js-accordion-group>
				<?php foreach ( $arctik_details as $arctik_index => $arctik_detail ) : ?>
					<?php $arctik_open = 0 === $arctik_index; ?>
					<div class="accordion accordion--compact<?php echo $arctik_open ? ' is-open' : ''; ?>" data-js-accordion>
						<button class="accordion__header" type="button" aria-expanded="<?php echo $arctik_open ? 'true' : 'false'; ?>" aria-controls="product-<?php echo esc_attr( $arctik_detail['id'] ); ?>" data-js-accordion-toggle>
							<span class="accordion__question"><?php echo arctik_text( $arctik_detail['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<span class="accordion__icon icon-btn icon-btn--24" aria-hidden="true">
								<img src="<?php echo esc_url( arctik_asset( 'icons/icn-plus.svg' ) ); ?>" alt="" width="18" height="18">
								<img src="<?php echo esc_url( arctik_asset( 'icons/icn-minus.svg' ) ); ?>" alt="" width="18" height="18">
							</span>
						</button>
						<div class="accordion__panel" id="product-<?php echo esc_attr( $arctik_detail['id'] ); ?>" data-js-accordion-panel>
							<?php echo $arctik_detail['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- собрано выше с экранированием ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php // Ряды шире панели листаются зажатием/свайпом (drag-slider.js). ?>
		<?php if ( $arctik_ingredients ) : ?>
			<div class="product-main__block">
				<h2 class="product-main__subtitle">Ингредиенты</h2>
				<div class="product-main__viewport" data-js-drag-slider data-drag-slider-align="start">
					<ul class="product-main__track product-main__track--ingredients" data-js-drag-slider-track>
						<?php
						foreach ( $arctik_ingredients as $arctik_item ) {
							get_template_part( 'template-parts/ingredient-card', null, [ 'item' => $arctik_item ] );
						}
						?>
					</ul>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $arctik_related ) : ?>
			<div class="product-main__block product-main__block--related">
				<h2 class="product-main__subtitle">Другие вкусы Arctic Berries</h2>
				<div class="product-main__viewport" data-js-drag-slider data-drag-slider-align="start">
					<div class="product-main__track" data-js-drag-slider-track>
						<?php
						foreach ( $arctik_related as $arctik_item ) {
							get_template_part( 'template-parts/mini-card', null, [ 'product' => $arctik_item, 'mod' => 'mini-card--light' ] );
						}
						?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<?php
	// Мобилка: нижняя панель покупки (макет 18160:50932) — видна, когда кнопка «В корзину» в панели ушла с экрана.
	// Количество синхронизировано с формой, кнопка отправляет её (sticky-buy.js).
	if ( $arctik_available ) :
		?>
		<div class="product-main__sticky-buy" data-js-sticky-buy aria-hidden="true" inert>
			<?php get_template_part( 'template-parts/quantity', null, [ 'max' => $arctik_max ] ); ?>
			<button class="product-main__cart btn btn--accent" type="button" data-js-sticky-buy-submit data-js-swipe>
				<span>В&nbsp;корзину</span>
				<span data-js-product-price><?php echo esc_html( $arctik_price ); ?></span>
			</button>
		</div>
	<?php endif; ?>
</section>
