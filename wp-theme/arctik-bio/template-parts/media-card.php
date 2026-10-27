<?php
/**
 * Карточка соцсети (src/partials/media-card.html).
 *
 * $args: соцсеть из arctik_socials() — network, url, label.
 */

defined( 'ABSPATH' ) || exit;
?>
<li class="media__item">
	<a class="media-card" href="<?php echo esc_url( $args['url'] ); ?>" target="_blank" rel="noopener" data-js-fake-glass-item data-swipe-trigger>
		<span class="media-card__header">
			<span class="media-card__title"><?php echo esc_html( $args['label'] ); ?></span>
			<img class="media-card__chevron" src="<?php echo esc_url( arctik_asset( 'icons/icn-chevron.svg' ) ); ?>" alt="" width="24" height="24">
		</span>
		<span class="media-card__icon-wrap">
			<span class="media-card__icon" data-js-swipe><img src="<?php echo esc_url( arctik_asset( "icons/icn-{$args['network']}-32.svg" ) ); ?>" alt="" width="56" height="56"></span>
		</span>
	</a>
</li>
