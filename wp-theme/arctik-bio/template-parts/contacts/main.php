<?php
/**
 * Экран контактов (src/partials/contacts/main.html). Заголовок — название страницы, подпись над ним, текст и фото —
 * поля страницы (вкладка «Контакты»); телефон, соцсети, адрес, почта — «Настройки сайта» (пустое не выводится).
 */

defined( 'ABSPATH' ) || exit;

$arctik_page    = (int) get_the_ID();
$arctik_lead    = (string) arctik_field( 'arctik_contacts_lead', $arctik_page );
$arctik_text    = (string) arctik_field( 'arctik_contacts_text', $arctik_page );
$arctik_photo   = (int) arctik_field( 'arctik_contacts_photo', $arctik_page );
$arctik_phone   = (string) arctik_option( 'arctik_phone' );
$arctik_email   = (string) arctik_option( 'arctik_email' );
$arctik_address = (string) arctik_option( 'arctik_address' );

// Свои кадры планшета и мобилки: <source> с размерами (пусто — фото десктопа).
$arctik_sources = [];
foreach ( [ 'mobile' => '(max-width: 767px)', 'tablet' => '(max-width: 1199px)' ] as $arctik_key => $arctik_media ) {
	$arctik_src = wp_get_attachment_image_src( (int) arctik_field( "arctik_contacts_photo_{$arctik_key}", $arctik_page ), 'full' );
	if ( $arctik_src ) {
		$arctik_sources[] = [ $arctik_media, $arctik_src ];
	}
}
?>
<section class="contacts" data-header-theme="dark">
	<?php // Фон: фото (фрагмент кадра, как в макете) + затемнение поверх. ?>
	<div class="contacts__bg" aria-hidden="true">
		<?php if ( $arctik_photo ) : ?>
			<picture>
				<?php foreach ( $arctik_sources as [ $arctik_media, $arctik_src ] ) : ?>
					<source media="<?php echo esc_attr( $arctik_media ); ?>" srcset="<?php echo esc_url( $arctik_src[0] ); ?>" width="<?php echo esc_attr( $arctik_src[1] ); ?>" height="<?php echo esc_attr( $arctik_src[2] ); ?>">
				<?php endforeach; ?>
				<?php echo arctik_image( $arctik_photo, 'full', [ 'class' => 'contacts__photo', 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</picture>
		<?php endif; ?>
		<img class="contacts__shade" src="<?php echo esc_url( arctik_asset( 'images/contacts-shade.webp' ) ); ?>" alt="" width="1080" height="612">
	</div>

	<div class="contacts__card">
		<div class="contacts__intro">
			<?php if ( $arctik_lead ) : ?>
				<p class="contacts__lead" data-js-title-reveal data-title-reveal-start="top bottom"><?php echo arctik_text( $arctik_lead ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
			<h1 class="contacts__title" data-js-title-reveal data-title-reveal-start="top bottom"><?php the_title(); ?></h1>
			<?php if ( $arctik_text ) : ?>
				<p class="contacts__text" data-js-title-reveal data-title-reveal-start="top bottom"><?php echo arctik_lines( $arctik_text ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>

		<dl class="contacts__list">
			<?php if ( $arctik_phone ) : ?>
				<div class="contacts__row">
					<dt class="contacts__label">Телефон</dt>
					<dd class="contacts__value"><a href="<?php echo esc_attr( arctik_phone_href( $arctik_phone ) ); ?>" data-js-roll><?php echo esc_html( $arctik_phone ); ?></a></dd>
				</div>
			<?php endif; ?>
			<?php if ( arctik_socials() ) : ?>
				<div class="contacts__row">
					<dt class="contacts__label">Задать вопрос</dt>
					<dd>
						<?php get_template_part( 'template-parts/socials', null, [ 'mod' => 'socials--large contacts__socials', 'size' => 32 ] ); ?>
					</dd>
				</div>
			<?php endif; ?>
			<?php if ( $arctik_address ) : ?>
				<div class="contacts__row contacts__row--top">
					<dt class="contacts__label">Адрес</dt>
					<dd><address class="contacts__address"><?php echo arctik_text( $arctik_address ); // phpcs:ignore WordPress.Security.EscapeOutput ?></address></dd>
				</div>
			<?php endif; ?>
			<?php if ( $arctik_email ) : ?>
				<div class="contacts__row">
					<dt class="contacts__label">Почта</dt>
					<dd class="contacts__value"><a href="mailto:<?php echo esc_attr( $arctik_email ); ?>" data-js-roll><?php echo esc_html( $arctik_email ); ?></a></dd>
				</div>
			<?php endif; ?>
		</dl>
	</div>
</section>
