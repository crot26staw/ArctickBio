<?php
/**
 * Меню личного кабинета (src/partials/account/nav.html): разметка WooCommerce My Account navigation.
 * $args: mod — модификатор (account-nav--mobile — только мобильные плашки, страница статуса заказа),
 * active — активный пункт: orders / edit-account.
 */

defined( 'ABSPATH' ) || exit;

$arctik_active = $args['active'] ?? '';
$arctik_items  = [
	'orders'       => [ 'Мои заказы', '<svg class="account-nav__icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11 19.425V12.575L5 9.1V15.95L11 19.425ZM13 19.425L19 15.95V9.1L13 12.575V19.425ZM11 21.725L4 17.7C3.68333 17.5167 3.4375 17.275 3.2625 16.975C3.0875 16.675 3 16.3417 3 15.975V8.025C3 7.65833 3.0875 7.325 3.2625 7.025C3.4375 6.725 3.68333 6.48333 4 6.3L11 2.275C11.3167 2.09167 11.65 2 12 2C12.35 2 12.6833 2.09167 13 2.275L20 6.3C20.3167 6.48333 20.5625 6.725 20.7375 7.025C20.9125 7.325 21 7.65833 21 8.025V15.975C21 16.3417 20.9125 16.675 20.7375 16.975C20.5625 17.275 20.3167 17.5167 20 17.7L13 21.725C12.6833 21.9083 12.35 22 12 22C11.65 22 11.3167 21.9083 11 21.725ZM16 8.525L17.925 7.425L12 4L10.05 5.125L16 8.525ZM12 10.85L13.95 9.725L8.025 6.3L6.075 7.425L12 10.85Z"/></svg>' ],
	'edit-account' => [ 'Аккаунт', '<svg class="account-nav__icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9.175 10.825C8.39167 10.0417 8 9.1 8 8C8 6.9 8.39167 5.95833 9.175 5.175C9.95833 4.39167 10.9 4 12 4C13.1 4 14.0417 4.39167 14.825 5.175C15.6083 5.95833 16 6.9 16 8C16 9.1 15.6083 10.0417 14.825 10.825C14.0417 11.6083 13.1 12 12 12C10.9 12 9.95833 11.6083 9.175 10.825ZM4 20V17.2C4 16.6333 4.14583 16.1125 4.4375 15.6375C4.72917 15.1625 5.11667 14.8 5.6 14.55C6.63333 14.0333 7.68333 13.6458 8.75 13.3875C9.81667 13.1292 10.9 13 12 13C13.1 13 14.1833 13.1292 15.25 13.3875C16.3167 13.6458 17.3667 14.0333 18.4 14.55C18.8833 14.8 19.2708 15.1625 19.5625 15.6375C19.8542 16.1125 20 16.6333 20 17.2V20H4ZM6 18H18V17.2C18 17.0167 17.9542 16.85 17.8625 16.7C17.7708 16.55 17.65 16.4333 17.5 16.35C16.6 15.9 15.6917 15.5625 14.775 15.3375C13.8583 15.1125 12.9333 15 12 15C11.0667 15 10.1417 15.1125 9.225 15.3375C8.30833 15.5625 7.4 15.9 6.5 16.35C6.35 16.4333 6.22917 16.55 6.1375 16.7C6.04583 16.85 6 17.0167 6 17.2V18ZM13.4125 9.4125C13.8042 9.02083 14 8.55 14 8C14 7.45 13.8042 6.97917 13.4125 6.5875C13.0208 6.19583 12.55 6 12 6C11.45 6 10.9792 6.19583 10.5875 6.5875C10.1958 6.97917 10 7.45 10 8C10 8.55 10.1958 9.02083 10.5875 9.4125C10.9792 9.80417 11.45 10 12 10C12.55 10 13.0208 9.80417 13.4125 9.4125Z"/></svg>' ],
];
?>
<?php /* Меню кабинета (макет sidebar 18252:79583) */ ?>
<nav class="account-nav woocommerce-MyAccount-navigation <?php echo esc_attr( $args['mod'] ?? '' ); ?>" aria-label="Личный кабинет">
	<ul class="account-nav__list">
		<?php foreach ( $arctik_items as $arctik_endpoint => [ $arctik_label, $arctik_icon ] ) : ?>
			<?php $arctik_is_active = $arctik_endpoint === $arctik_active; ?>
			<li class="account-nav__item woocommerce-MyAccount-navigation-link woocommerce-MyAccount-navigation-link--<?php echo esc_attr( $arctik_endpoint ); ?><?php echo $arctik_is_active ? ' is-active' : ''; ?>">
				<a class="account-nav__link" href="<?php echo esc_url( wc_get_account_endpoint_url( $arctik_endpoint ) ); ?>"<?php echo $arctik_is_active ? ' aria-current="page"' : ''; ?> data-js-swipe>
					<?php echo $arctik_icon; // phpcs:ignore WordPress.Security.EscapeOutput -- SVG из шаблона ?>
					<span class="account-nav__label"><?php echo esc_html( $arctik_label ); ?></span>
					<svg class="account-nav__chevron" width="24" height="24" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.5 10L6.66667 6.16667L7.83333 5L12.8333 10L7.83333 15L6.66667 13.8333L10.5 10Z"/></svg>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php get_template_part( 'template-parts/account/logout', null, [ 'mod' => 'account-logout--nav' ] ); ?>
</nav>
