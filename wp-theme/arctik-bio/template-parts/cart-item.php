<?php
/**
 * Товар в корзине-попапе (src/partials/cart-item.html). $args: key — ключ позиции корзины, item — позиция WC()->cart.
 *
 * Название и подпись — как у мини-карточки товара («Морской коллаген» / «с дикой брусникой»), ниже — формат вариации («30 саше»),
 * картинка — пачка мини-карточки (макет 18920:36110).
 * Нет в наличии (или не хватает остатка) — cart-item--sold: «Разобрали» и «Удалить» вместо счётчика и цены.
 */

defined( 'ABSPATH' ) || exit;

$arctik_key  = (string) ( $args['key'] ?? '' );
$arctik_item = $args['item'] ?? [];

/** @var WC_Product|null $arctik_product */
$arctik_product = $arctik_item['data'] ?? null;
if ( ! $arctik_product instanceof WC_Product || ! $arctik_product->exists() ) {
	return;
}

$arctik_parent = wc_get_product( $arctik_product->get_parent_id() ?: $arctik_product->get_id() ) ?: $arctik_product;
$arctik_qty    = (int) $arctik_item['quantity'];
$arctik_link   = $arctik_parent->get_permalink();
$arctik_max    = $arctik_product->get_max_purchase_quantity();
$arctik_sold   = arctik_cart_item_is_sold( $arctik_item );

[ $arctik_title, $arctik_subtitle ] = arctik_product_mini_titles( $arctik_parent );

// Формат — значения атрибутов вариации («30 саше»), у простого товара (набор) строки нет
$arctik_format = $arctik_product->is_type( 'variation' ) ? wc_get_formatted_variation( $arctik_product, true, false, false ) : '';
?>
<?php /*
	Товар в корзине (макет 18182:42856, «Разобрали» — 18269:34323). Разметка — как в мини-корзине WooCommerce:
	li.woocommerce-mini-cart-item, ссылка удаления .remove_from_cart_button с data-product_id / data-cart_item_key.
	Нет в наличии — mod "cart-item--sold", вместо счётчика и цены — подпись «Разобрали».
*/ ?>
<li class="cart-item woocommerce-mini-cart-item mini_cart_item <?php echo $arctik_sold ? 'cart-item--sold' : ''; ?>" data-js-cart-item data-price="<?php echo esc_attr( wc_get_price_to_display( $arctik_product ) ); ?>" data-cart_item_key="<?php echo esc_attr( $arctik_key ); ?>">
	<a class="cart-item__media" href="<?php echo esc_url( $arctik_link ); ?>" tabindex="-1" aria-hidden="true">
		<?php echo arctik_image( arctik_product_image_id( $arctik_parent, 'mini' ), 'woocommerce_thumbnail', [ 'class' => 'cart-item__img', 'alt' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</a>
	<div class="cart-item__body">
		<div class="cart-item__text">
			<div class="cart-item__name">
				<a class="cart-item__title" href="<?php echo esc_url( $arctik_link ); ?>"><?php echo arctik_text( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<?php if ( $arctik_subtitle ) : ?>
					<p class="cart-item__subtitle"><?php echo arctik_text( $arctik_subtitle ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $arctik_format ) : ?>
				<p class="cart-item__format"><?php echo arctik_text( wp_strip_all_tags( $arctik_format ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
		<div class="cart-item__actions">
			<div class="cart-item__controls">
				<div class="cart-item__quantity quantity quantity--mini" data-js-quantity>
					<button class="quantity__btn" type="button" aria-label="Уменьшить количество" data-js-quantity-minus>
						<svg width="16" height="18" viewBox="0 0 16 18" fill="currentColor" aria-hidden="true"><path d="M7.55123 9.49756H4.97656V8.60156H11.0119V9.49756H7.55256H7.55123Z"/></svg>
					</button>
					<input class="quantity__input input-text qty text" type="number" name="cart[<?php echo esc_attr( $arctik_key ); ?>][qty]" value="<?php echo esc_attr( $arctik_qty ); ?>" min="1" max="<?php echo esc_attr( $arctik_max > 0 ? $arctik_max : 99 ); ?>" step="1" inputmode="numeric" aria-label="Количество" data-js-quantity-input>
					<button class="quantity__btn" type="button" aria-label="Увеличить количество" data-js-quantity-plus>
						<svg width="16" height="18" viewBox="0 0 16 18" fill="currentColor" aria-hidden="true"><path d="M7.65346 12.0624V9.49906H5.07812V8.60306H7.65413V6.03906H8.55013V8.6024H11.1135V9.4984H8.55013V12.0617H7.65413L7.65346 12.0624Z"/></svg>
					</button>
				</div>
				<a class="cart-item__remove remove remove_from_cart_button" href="<?php echo esc_url( wc_get_cart_remove_url( $arctik_key ) ); ?>" data-product_id="<?php echo esc_attr( $arctik_product->get_id() ); ?>" data-cart_item_key="<?php echo esc_attr( $arctik_key ); ?>" data-js-cart-remove data-js-roll>Удалить</a>
				<p class="cart-item__sold">Разобрали</p>
			</div>
			<span class="cart-item__price price" data-js-cart-item-price><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $arctik_product, $arctik_qty ) ); ?></span>
		</div>
	</div>
</li>
