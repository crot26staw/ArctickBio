<?php
/**
 * Подвал сайта (src/partials/footer.html) и конец документа.
 */

defined( 'ABSPATH' ) || exit;

$arctik_phone      = (string) arctik_option( 'arctik_phone' );
$arctik_email      = (string) arctik_option( 'arctik_email' );
$arctik_address    = (string) arctik_option( 'arctik_address' );
$arctik_copyright  = (string) arctik_option( 'arctik_copyright' );
$arctik_requisites = (string) arctik_option( 'arctik_requisites' );
$arctik_disclaimer = (string) arctik_option( 'arctik_disclaimer' );
$arctik_developer  = (string) arctik_option( 'arctik_developer_link' );
$arctik_q_title    = (string) arctik_option( 'arctik_footer_question_title' );
$arctik_q_text     = (string) arctik_option( 'arctik_footer_question_text' );
$arctik_products   = arctik_products();
?>
<footer class="footer" data-header-theme="dark">
	<div class="footer__top container">
		<div class="footer__brand">
			<a class="footer__logo logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="АрктикБиоМарин — на главную">
				<span class="logo__img"></span>
			</a>
			<p class="footer__copyright"><?php echo arctik_text( '© ' . wp_date( 'Y' ) . ' ' . $arctik_copyright ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php if ( $arctik_requisites ) : ?>
				<p class="footer__requisites"><?php echo arctik_lines( $arctik_requisites ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
			<a class="footer__dev" href="<?php echo esc_url( $arctik_developer ?: '#' ); ?>" target="_blank" rel="noopener" aria-label="Разработано в Alta">
				<img src="<?php echo esc_url( arctik_asset( 'icons/logo-alta.svg' ) ); ?>" alt="" width="120" height="16" loading="lazy">
			</a>
		</div>

		<div class="footer__nav">
			<?php if ( $arctik_products ) : ?>
				<nav class="footer__col footer__col--catalog" aria-labelledby="footer-catalog">
					<p class="footer__col-title" id="footer-catalog">Каталог продукции</p>
					<ul class="footer__menu menu" data-js-roll-group>
						<?php foreach ( $arctik_products as $arctik_product ) : ?>
							<li class="menu-item"><a href="<?php echo esc_url( $arctik_product->get_permalink() ); ?>"><?php echo arctik_text( $arctik_product->get_name() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<?php foreach ( [ 'footer_about' => 'about', 'footer_buyers' => 'buyers' ] as $arctik_location => $arctik_mod ) : ?>
				<?php if ( has_nav_menu( $arctik_location ) ) : ?>
					<nav class="footer__col footer__col--<?php echo esc_attr( $arctik_mod ); ?>" aria-labelledby="footer-<?php echo esc_attr( $arctik_mod ); ?>">
						<p class="footer__col-title" id="footer-<?php echo esc_attr( $arctik_mod ); ?>"><?php echo arctik_text( arctik_menu_name( $arctik_location ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						<?php arctik_menu( $arctik_location, 'footer__menu menu', 'data-js-roll-group' ); ?>
					</nav>
				<?php endif; ?>
			<?php endforeach; ?>

			<div class="footer__col footer__col--contacts">
				<?php if ( $arctik_q_title ) : ?>
					<p class="footer__col-title"><?php echo arctik_text( $arctik_q_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
				<?php if ( $arctik_q_text ) : ?>
					<p class="footer__col-title"><?php echo arctik_text( $arctik_q_text ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
				<address class="footer__menu footer__menu--contacts" data-js-roll-group>
					<?php if ( $arctik_phone ) : ?>
						<a href="<?php echo esc_attr( arctik_phone_href( $arctik_phone ) ); ?>"><?php echo arctik_text( $arctik_phone ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<?php endif; ?>
					<?php if ( $arctik_email ) : ?>
						<a href="mailto:<?php echo esc_attr( $arctik_email ); ?>"><?php echo esc_html( $arctik_email ); ?></a>
					<?php endif; ?>
					<?php if ( $arctik_address ) : ?>
						<p><?php echo arctik_text( $arctik_address ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<?php endif; ?>
				</address>
			</div>

			<div class="footer__bottom-row">
				<?php if ( arctik_socials() ) : ?>
					<div class="footer__col footer__col--media">
						<p class="footer__col-title">Медиа</p>
						<?php get_template_part( 'template-parts/socials' ); ?>
					</div>
				<?php endif; ?>

				<?php if ( has_nav_menu( 'footer_docs' ) ) : ?>
					<nav class="footer__col footer__col--docs" aria-labelledby="footer-docs">
						<p class="footer__col-title" id="footer-docs"><?php echo arctik_text( arctik_menu_name( 'footer_docs' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						<?php arctik_menu( 'footer_docs', 'footer__menu footer__menu--docs menu', 'data-js-roll-group' ); ?>
					</nav>
				<?php endif; ?>

				<?php get_template_part( 'template-parts/pay-logos', null, [ 'mod' => 'footer__pay' ] ); ?>
			</div>
		</div>
	</div>

	<?php if ( $arctik_disclaimer ) : ?>
		<p class="footer__disclaimer container"><?php echo arctik_text( $arctik_disclaimer ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	<?php endif; ?>

	<?php if ( $arctik_products ) : ?>
		<?php // Лента товаров листается зажатием/свайпом, если шире экрана (js/modules/drag-slider.js), в покое — по центру. ?>
		<div class="footer__products" data-js-drag-slider>
			<div class="footer__products-track" data-js-drag-slider-track>
				<?php
				foreach ( $arctik_products as $arctik_product ) {
					get_template_part( 'template-parts/mini-card', null, [
						'product' => $arctik_product,
						'mod'     => 'mini-card--solid mini-card--no-dots',
					] );
				}
				?>
			</div>
		</div>
	<?php endif; ?>
</footer>

<?php wp_footer(); ?>
</body>
</html>
