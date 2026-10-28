<?php
/**
 * Личный кабинет (src/account-orders.html, account.html, account-addresses.html): обычная шапка, фон оформления, без подвала.
 * Подключается фильтром template_include (inc/account.php) для разделов orders / edit-account / edit-address.
 * Раздел — template-parts/account/{orders,profile,addresses}.php; «Аккаунт» и «Мои адреса» — один пункт меню.
 */

defined( 'ABSPATH' ) || exit;

$arctik_section = arctik_account_section();
$arctik_parts   = [
	'orders'       => 'orders',
	'edit-account' => 'profile',
	'edit-address' => 'addresses',
];

get_header();
?>
<img class="page__bg" src="<?php echo esc_url( arctik_asset( 'images/checkout-bg.webp' ) ); ?>" alt="" width="936" height="1024">

<main class="main main--flex">
	<section class="account" data-header-theme="dark">
		<div class="account__card">
			<?php get_template_part( 'template-parts/account/nav', null, [ 'active' => 'orders' === $arctik_section ? 'orders' : 'edit-account' ] ); ?>
			<div class="account__content woocommerce-MyAccount-content" data-header-theme="light">
				<?php get_template_part( 'template-parts/account/' . $arctik_parts[ $arctik_section ] ); ?>
			</div>
		</div>
	</section>
</main>

<?php
get_footer( 'checkout' );
