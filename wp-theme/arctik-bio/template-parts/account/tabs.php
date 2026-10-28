<?php
/**
 * Вкладки «Аккаунт» / «Мои адреса» (src/partials/account/tabs.html) — ссылки на эндпоинты edit-account / edit-address.
 * $args: active — edit-account / edit-address.
 */

defined( 'ABSPATH' ) || exit;

$arctik_active = $args['active'] ?? '';
?>
<?php /* Вкладки (макет tabs 18275:43246) */ ?>
<nav class="account-tabs" aria-label="Аккаунт">
	<?php foreach ( [ 'edit-account' => 'Аккаунт', 'edit-address' => 'Мои адреса' ] as $arctik_endpoint => $arctik_label ) : ?>
		<?php $arctik_is_active = $arctik_endpoint === $arctik_active; ?>
		<a class="account-tabs__tab<?php echo $arctik_is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( wc_get_account_endpoint_url( $arctik_endpoint ) ); ?>"<?php echo $arctik_is_active ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $arctik_label ); ?></a>
	<?php endforeach; ?>
</nav>
