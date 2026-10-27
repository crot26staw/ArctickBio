<?php
/**
 * Ингредиент карточки товара (src/partials/ingredient-card.html).
 *
 * $args: item — элемент complex «Ингредиенты» (image, title, text).
 */

defined( 'ABSPATH' ) || exit;

$arctik_item = $args['item'] ?? [];
?>
<li class="ingredient-card">
	<?php echo arctik_image( (int) ( $arctik_item['image'] ?? 0 ), 'full', [ 'class' => 'ingredient-card__img', 'alt' => '', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<h3 class="ingredient-card__title"><?php echo arctik_text( $arctik_item['title'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
	<p class="ingredient-card__text"><?php echo arctik_text( $arctik_item['text'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
</li>
