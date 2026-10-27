<?php
/**
 * Документ в «Исследованиях» (src/partials/research-doc.html). $args: title (перенос строки — <br>), file — ID вложения.
 * Без файла — плитка без ссылки и размера.
 */

defined( 'ABSPATH' ) || exit;

$arctik_id   = (int) ( $args['file'] ?? 0 );
$arctik_url  = arctik_file_url( $arctik_id );
$arctik_path = $arctik_url ? get_attached_file( $arctik_id ) : '';
$arctik_size = $arctik_path && file_exists( $arctik_path ) ? number_format( filesize( $arctik_path ) / MB_IN_BYTES, 1, '.', '' ) . ' MB' : '';
?>
<li class="research-doc">
	<?php if ( $arctik_url ) : ?>
		<a class="research-doc__link" href="<?php echo esc_url( $arctik_url ); ?>" download>
	<?php else : ?>
		<span class="research-doc__link">
	<?php endif; ?>
		<span class="research-doc__meta"><span>PDF.</span><?php if ( $arctik_size ) : ?><span>- <?php echo arctik_text( $arctik_size ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php endif; ?></span>
		<span class="research-doc__title"><?php echo arctik_lines( $args['title'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<img class="research-doc__icon" src="<?php echo esc_url( arctik_asset( 'icons/icn-download.svg' ) ); ?>" alt="" width="16" height="16">
	<?php echo $arctik_url ? '</a>' : '</span>'; ?>
</li>
