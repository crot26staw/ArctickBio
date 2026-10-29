<?php
/**
 * Способы оплаты оформления заказа: «СБП» (arctik_sbp) и «Оплатить картой» (arctik_card).
 * Пока заглушки до подключения платёжного шлюза (этап Оф2): заказ создаётся со статусом «На удержании» (остаток списывается),
 * корзина очищается, переход на страницу «Заказ получен». Включаются в WooCommerce → Настройки → Платежи.
 * Иконка способа в форме — по ID, arctik_payment_icon().
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WC_Payment_Gateway' ) ) {
	abstract class Arctik_Payment_Gateway extends WC_Payment_Gateway {
		public function __construct() {
			$this->has_fields         = false;
			$this->method_description = 'Заглушка до подключения платёжного шлюза: заказ создаётся со статусом «На удержании», оплата не проводится.';
			$this->form_fields        = [
				'enabled' => [
					'title'   => 'Включить',
					'type'    => 'checkbox',
					'label'   => $this->method_title,
					'default' => 'yes',
				],
				'title'   => [
					'title'   => 'Название',
					'type'    => 'text',
					'default' => $this->method_title,
				],
			];
			$this->init_settings();
			$this->title = $this->get_option( 'title' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			$order->update_status( 'on-hold', 'Ожидает оплаты: платёжный шлюз ещё не подключён.' );
			WC()->cart->empty_cart();

			return [
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			];
		}
	}

	final class Arctik_Payment_Sbp extends Arctik_Payment_Gateway {
		public function __construct() {
			$this->id           = 'arctik_sbp';
			$this->method_title = 'СБП';
			parent::__construct();
		}
	}

	final class Arctik_Payment_Card extends Arctik_Payment_Gateway {
		public function __construct() {
			$this->id           = 'arctik_card';
			$this->method_title = 'Оплатить картой';
			parent::__construct();
		}
	}

	add_filter( 'woocommerce_payment_gateways', function ( array $gateways ): array {
		$gateways[] = Arctik_Payment_Sbp::class;
		$gateways[] = Arctik_Payment_Card::class;

		return $gateways;
	} );
}

/**
 * Иконка способа оплаты в форме (как в статике): [ файл, ширина, высота, модификатор ]. Чужой способ — карта.
 */
function arctik_payment_icon( string $gateway_id ): array {
	return 'arctik_sbp' === $gateway_id
		? [ 'icons/icn-sbp.svg', '19.4', '24', 'checkout-pay__icon--sbp' ]
		: [ 'icons/icn-credit.svg', '32', '32', '' ];
}
