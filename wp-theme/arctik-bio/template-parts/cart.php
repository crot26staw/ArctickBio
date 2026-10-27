<?php
/**
 * Корзина-попап (src/partials/cart.html). Пока только пустое состояние; товары, итоги и AJAX — этап «Кр».
 */

defined( 'ABSPATH' ) || exit;
?>
<?php /*
	Корзина-попап (макет «Корзина поп-ап» в секции 18126:59053 копии Pky7FDIkoyGiepPQCMknnV):
	пустая — 18266:30792, с товарами — 18269:32227, «Разобрали» — 18269:34349; планшет 18269:32867, мобилка 18256:85810.
	Открывает кнопка корзины в шапке [data-js-cart-open], закрывают [data-js-cart-close] и Escape — js/modules/cart.js.
	В WordPress содержимое [data-js-cart-body] — фрагмент мини-корзины WooCommerce (обновляется AJAX-ом).
*/ ?>
<div class="mini-cart" id="cart" data-js-cart inert>
	<div class="mini-cart__overlay" data-js-cart-close></div>

	<div class="mini-cart__dialog" role="dialog" aria-modal="true" aria-labelledby="cart-title" data-js-cart-dialog>
		<div class="mini-cart__head">
			<h2 class="mini-cart__title" id="cart-title">Корзина<span class="mini-cart__count" data-js-cart-title-count></span></h2>
			<button class="mini-cart__close" type="button" aria-label="Закрыть корзину" data-js-cart-close>
				<svg width="44" height="44" viewBox="0 0 44 44" fill="currentColor" aria-hidden="true">
					<path d="M23.414 22L30 15.414L28.586 14L22 20.586L15.414 14L14 15.414L20.586 22L14 28.586L15.414 30L22 23.414L28.586 30L30 28.586L23.414 22Z"/>
				</svg>
			</button>
		</div>

		<div class="mini-cart__body widget_shopping_cart_content" data-js-cart-body>
			<?php /* Пустая корзина */ ?>
			<div class="mini-cart__empty" data-js-cart-empty>
				<img class="mini-cart__empty-img" src="<?php echo esc_url( arctik_asset( 'images/cart-empty.webp' ) ); ?>" alt="" width="72" height="72">
				<div class="mini-cart__empty-text">
					<p class="mini-cart__empty-title woocommerce-mini-cart__empty-message">Ваша корзина пуста</p>
					<p class="mini-cart__empty-desc">В&nbsp;вашей корзине пока нет товаров, вы&nbsp;можете зайти в&nbsp;магазин и&nbsp;купить товары</p>
				</div>
				<a class="btn btn--accent btn--small" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" data-js-swipe>Продолжить покупки</a>
			</div>

			<?php /* С товарами */ ?>
			<div class="mini-cart__content" hidden data-js-cart-content>
				<div class="mini-cart__scroll" data-lenis-prevent>
					<ul class="mini-cart__list woocommerce-mini-cart cart_list product_list_widget" data-js-cart-list>
						<?php // Товары корзины (template-parts/cart-item.php) — этап «Кр». ?>
					</ul>

					<div class="mini-cart__more">
						<p class="mini-cart__more-title">Попробуйте также</p>
						<div class="mini-cart__more-viewport" data-js-drag-slider data-drag-slider-align="start">
							<div class="mini-cart__more-track" data-js-drag-slider-track>
								<?php
								foreach ( arctik_products() as $arctik_product ) {
									get_template_part( 'template-parts/mini-card', null, [
										'product' => $arctik_product,
										'mod'     => 'mini-card--light',
									] );
								}
								?>
							</div>
						</div>
					</div>
				</div>

				<div class="mini-cart__summary">
					<dl class="mini-cart__totals" data-js-cart-totals>
						<div class="mini-cart__row mini-cart__row--total">
							<dt>Итого</dt>
							<dd class="mini-cart__total" data-js-cart-total></dd>
						</div>
						<div class="mini-cart__row">
							<dt>Скидка</dt>
							<dd class="mini-cart__discount" data-js-cart-discount data-value="0"></dd>
						</div>
						<div class="mini-cart__row">
							<dt>Доставка</dt>
							<dd class="mini-cart__free">Бесплатно</dd>
						</div>
					</dl>

					<?php
					get_template_part( 'template-parts/promo', null, [
						'id'  => 'cart-coupon',
						'mod' => 'promo--outline promo--compact mini-cart__promo',
					] );
					?>

					<a class="btn btn--accent mini-cart__checkout checkout wc-forward" href="<?php echo esc_url( wc_get_checkout_url() ); ?>" data-js-cart-checkout data-js-swipe>Перейти к&nbsp;оформлению</a>
				</div>
			</div>
		</div>
	</div>
</div>
