<?php
/**
 * Соцсети из настроек сайта (src: ul.socials в подвале и мобильном меню).
 *
 * $args: mod — модификаторы списка (socials--large socials--glass), size — 24 или 32 (иконки icn-*-32.svg).
 */

defined( 'ABSPATH' ) || exit;

$arctik_socials = arctik_socials();
if ( ! $arctik_socials ) {
	return;
}

$arctik_size   = (int) ( $args['size'] ?? 24 );
$arctik_suffix = 32 === $arctik_size ? '-32' : '';
?>
<ul class="socials <?php echo esc_attr( $args['mod'] ?? '' ); ?>">
	<?php foreach ( $arctik_socials as $arctik_social ) : ?>
		<li><a class="socials__link" href="<?php echo esc_url( $arctik_social['url'] ); ?>" target="_blank" rel="noopener" data-js-swipe aria-label="<?php echo esc_attr( $arctik_social['label'] ); ?>"><img src="<?php echo esc_url( arctik_asset( "icons/icn-{$arctik_social['network']}{$arctik_suffix}.svg" ) ); ?>" alt="" width="<?php echo esc_attr( $arctik_size ); ?>" height="<?php echo esc_attr( $arctik_size ); ?>"></a></li>
	<?php endforeach; ?>
</ul>
