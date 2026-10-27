<?php
/**
 * «Сделано людьми Севера» (src/partials/about/intro.html): поля страницы, вкладка «Сделано людьми Севера».
 * Фото появляются из-под синей плашки (js/modules/image-reveal.js).
 */

defined( 'ABSPATH' ) || exit;

$arctik_page   = (int) get_the_ID();
$arctik_title  = (string) arctik_field( 'arctik_about_intro_title', $arctik_page );
$arctik_lead   = (string) arctik_field( 'arctik_about_intro_lead', $arctik_page );
$arctik_text   = (string) arctik_field( 'arctik_about_intro_text', $arctik_page );
$arctik_small  = (int) arctik_field( 'arctik_about_intro_photo_small', $arctik_page );
$arctik_photo  = (int) arctik_field( 'arctik_about_intro_photo', $arctik_page );
$arctik_tablet = wp_get_attachment_image_src( (int) arctik_field( 'arctik_about_intro_photo_tablet', $arctik_page ), 'full' );
?>
<section class="company-intro container" data-header-theme="light">
	<div class="company-intro__content">
		<div class="company-intro__header">
			<?php if ( $arctik_title ) : ?>
				<h2 class="company-intro__title" data-js-title-reveal><?php echo arctik_text( $arctik_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php endif; ?>
			<?php if ( $arctik_lead ) : ?>
				<p class="company-intro__lead" data-js-title-reveal><?php echo arctik_lines( $arctik_lead ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
		<div class="company-intro__bottom">
			<?php if ( $arctik_small ) : ?>
				<div class="company-intro__img-small image-reveal" data-js-image-reveal>
					<span class="image-reveal__bg" aria-hidden="true"></span>
					<?php echo arctik_image( $arctik_small, 'full', [ 'class' => 'company-intro__img image-reveal__media' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			<?php endif; ?>
			<?php if ( $arctik_text ) : ?>
				<p class="company-intro__text" data-js-title-reveal><?php echo arctik_lines( $arctik_text ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( $arctik_photo ) : ?>
		<div class="company-intro__picture image-reveal" data-js-image-reveal>
			<span class="image-reveal__bg" aria-hidden="true"></span>
			<picture class="image-reveal__media">
				<?php if ( $arctik_tablet ) : ?>
					<source media="(max-width: 1199px)" srcset="<?php echo esc_url( $arctik_tablet[0] ); ?>" width="<?php echo esc_attr( $arctik_tablet[1] ); ?>" height="<?php echo esc_attr( $arctik_tablet[2] ); ?>">
				<?php endif; ?>
				<?php echo arctik_image( $arctik_photo, 'full', [ 'class' => 'company-intro__img' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</picture>
		</div>
	<?php endif; ?>
</section>
