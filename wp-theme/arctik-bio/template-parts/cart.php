<?php
/**
 * Корзина-попап (src/partials/cart.html). Товары, итоги и счётчики — части inc/cart.php (arctik_cart_part()),
 * они же — фрагменты WC: после запросов cart-api.js заменяются целиком.
 */

defined( 'ABSPATH' ) || exit;

$arctik_empty = WC()->cart->is_empty();
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
			<h2 class="mini-cart__title" id="cart-title">Корзина<?php echo arctik_cart_part( 'title-count' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<button class="mini-cart__close" type="button" aria-label="Закрыть корзину" data-js-cart-close>
				<svg width="44" height="44" viewBox="0 0 44 44" fill="currentColor" aria-hidden="true">
					<path d="M23.414 22L30 15.414L28.586 14L22 20.586L15.414 14L14 15.414L20.586 22L14 28.586L15.414 30L22 23.414L28.586 30L30 28.586L23.414 22Z"/>
				</svg>
			</button>
		</div>

		<div class="mini-cart__body widget_shopping_cart_content" data-js-cart-body>
			<?php /* Пустая корзина */ ?>
			<div class="mini-cart__empty" <?php echo $arctik_empty ? '' : 'hidden'; ?> data-js-cart-empty>
				<img class="mini-cart__empty-img" src="<?php echo esc_url( arctik_asset( 'images/cart-empty.webp' ) ); ?>" alt="" width="72" height="72" loading="lazy">
				<div class="mini-cart__empty-text">
					<p class="mini-cart__empty-title woocommerce-mini-cart__empty-message">Ваша корзина пуста</p>
					<p class="mini-cart__empty-desc">В&nbsp;вашей корзине пока нет товаров, вы&nbsp;можете зайти в&nbsp;магазин и&nbsp;купить товары</p>
				</div>
				<a class="btn btn--accent btn--small" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" data-js-swipe>Продолжить покупки</a>
			</div>

			<?php /* С товарами */ ?>
			<?php /* На мобилке прокручивается весь блок, итог — последняя карточка в прокрутке (макет 18920:36300) */ ?>
			<div class="mini-cart__content" <?php echo $arctik_empty ? 'hidden' : ''; ?> data-js-cart-content data-lenis-prevent>
				<div class="mini-cart__scroll" data-lenis-prevent>
					<?php echo arctik_cart_part( 'list' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

					<?php echo arctik_cart_part( 'more' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

				<div class="mini-cart__summary" data-js-cart-summary>
					<?php echo arctik_cart_part( 'totals' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

					<?php
					get_template_part( 'template-parts/promo', null, [
						'id'    => 'cart-coupon',
						'mod'   => 'promo--outline promo--compact mini-cart__promo',
						'value' => arctik_cart_coupon(),
					] );
					?>

					<p class="mini-cart__error" hidden data-js-cart-error>Удалите из&nbsp;корзины товары, которых нет в&nbsp;наличии</p>
					<a class="btn btn--accent mini-cart__checkout checkout wc-forward" href="<?php echo esc_url( wc_get_checkout_url() ); ?>" data-js-cart-checkout data-js-swipe>Перейти к&nbsp;оформлению</a>
				</div>
			</div>

			<?php /* Мобилка: пока итог виден не целиком — кнопка с суммой внизу (макет 18920:36300), сумма — копия «Итого» (js/modules/cart.js) */ ?>
			<div class="mini-cart__bar" data-js-cart-bar inert>
				<a class="btn btn--accent mini-cart__bar-btn" href="<?php echo esc_url( wc_get_checkout_url() ); ?>" data-js-cart-checkout data-js-swipe>К&nbsp;оформлению<span class="mini-cart__bar-total" data-js-cart-bar-total></span></a>
			</div>
		</div>
	</div>
</div>
