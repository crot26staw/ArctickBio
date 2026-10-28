<?php
/**
 * «Мои заказы» (src/partials/account/orders.html): вкладки «Активные» / «Завершенные» (account.js), заказы — arctik_account_orders().
 * Открыта «Активные», а если активных нет — «Завершенные»; пустая вкладка — строка «… заказов нет» (решение пользователя).
 * Заказов нет совсем — orders-empty.php.
 */

defined( 'ABSPATH' ) || exit;

$arctik_orders = arctik_account_orders();

if ( ! $arctik_orders['active'] && ! $arctik_orders['done'] ) {
	get_template_part( 'template-parts/account/orders-empty' );
	return;
}

$arctik_tabs     = [
	'active' => [ 'Активные', "Активных заказов нет" ],
	'done'   => [ 'Завершенные', "Завершенных заказов нет" ],
];
$arctik_selected = $arctik_orders['active'] ? 'active' : 'done';
?>
<?php /* «Мои заказы» (макет 16692:130971, планшет 18277:44075, мобилка 18277:44613) */ ?>
<div class="account__head">
	<h1 class="account__title">Мои заказы</h1>
</div>
<div class="account-tabs" role="tablist" aria-label="Заказы" data-js-account-tabs>
	<?php foreach ( $arctik_tabs as $arctik_key => [ $arctik_label ] ) : ?>
		<button class="account-tabs__tab" type="button" role="tab" id="orders-tab-<?php echo esc_attr( $arctik_key ); ?>" aria-selected="<?php echo $arctik_key === $arctik_selected ? 'true' : 'false'; ?>" aria-controls="orders-<?php echo esc_attr( $arctik_key ); ?>" data-js-account-tab><?php echo esc_html( $arctik_label ); ?></button>
	<?php endforeach; ?>
</div>
<div class="account__body">
	<?php foreach ( $arctik_tabs as $arctik_key => [ , $arctik_none ] ) : ?>
		<ul class="account__orders woocommerce-orders-table" id="orders-<?php echo esc_attr( $arctik_key ); ?>" role="tabpanel" aria-labelledby="orders-tab-<?php echo esc_attr( $arctik_key ); ?>"<?php echo $arctik_key === $arctik_selected ? '' : ' hidden'; ?>>
			<?php foreach ( $arctik_orders[ $arctik_key ] as $arctik_order ) : ?>
				<?php get_template_part( 'template-parts/account/order-card', null, [ 'order' => $arctik_order ] ); ?>
			<?php endforeach; ?>
			<?php if ( ! $arctik_orders[ $arctik_key ] ) : ?>
				<li class="account__orders-none"><?php echo esc_html( $arctik_none ); ?></li>
			<?php endif; ?>
		</ul>
	<?php endforeach; ?>
</div>
