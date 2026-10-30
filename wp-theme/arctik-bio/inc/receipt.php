<?php
/**
 * Чеки 54-ФЗ — облачная касса LIFE PAY (API cloud-print/create-receipt, apidoc.life-pay.ru/cloud-print/index).
 * Эквайринг (Альфа-Банк) чеки не пробивает: заказ оплачен — тема отправляет чек прихода, переведён в «Возвращён» — чек возврата.
 * Настройки — «Настройки сайта → Чеки» (inc/fields/receipt.php): логин и API-ключ, тестовый режим, СНО, НДС.
 *
 * Отправка — фоном (Action Scheduler WC), не в запросе callback банка. Ошибка связи / 500 — повтор через 1, 3, 5, затем каждые 10 минут
 * с тем же ext_id (LIFE PAY второй чек не создаёт). Ошибка данных — заметка в заказе, без повтора.
 * Итог — заметка в заказе и мета _arctik_receipt_<тип> (queued / uuid); вручную — «Действия с заказом» → «Отправить чек…».
 * Частичный возврат — только заметка: чек возврата пробивается в кабинете LIFE PAY.
 */

defined( 'ABSPATH' ) || exit;

const ARCTIK_RECEIPT_URL = 'https://sapi.life-pay.ru/cloud-print/create-receipt';

// Повторы при ошибке связи, секунды (документация LIFE PAY: 1, 3, 5 минут, затем каждые 10)
const ARCTIK_RECEIPT_RETRY = [ 60, 180, 300, 600, 600, 600, 600, 600, 600, 600 ];

// Ошибки данных и доступа: повтор не поможет
const ARCTIK_RECEIPT_FATAL = [ 400, 6000, 6009, 6010, 6012, 6080, 6095 ]; // 6012 «Access denied» — неверный ключ (в документации нет)

const ARCTIK_RECEIPT_TYPES = [
	'payment' => 'прихода',
	'refund'  => 'возврата',
];

function arctik_receipt_enabled(): bool {
	return (bool) arctik_option( 'arctik_receipt_enabled' );
}

function arctik_receipt_is_test(): bool {
	return (bool) arctik_option( 'arctik_receipt_test' );
}

function arctik_receipt_log( string $message ): void {
	wc_get_logger()->warning( $message, [ 'source' => 'arctik-receipt' ] );
}

/**
 * Телефон в формате LIFE PAY: 7XXXXXXXXXX (логин) — из любого вида; не похоже на российский номер — ''.
 */
function arctik_receipt_phone( string $phone ): string {
	$digits = preg_replace( '/\D+/', '', $phone );

	if ( 10 === strlen( $digits ) ) {
		$digits = '7' . $digits;
	} elseif ( 11 === strlen( $digits ) && '8' === $digits[0] ) {
		$digits = '7' . substr( $digits, 1 );
	}

	return 11 === strlen( $digits ) && '7' === $digits[0] ? $digits : '';
}

/**
 * Позиции чека: товары по цене после скидки, доставка — услугой. Цена за единицу не делится на копейки —
 * позиция разбивается на две (qty − 1 по цене и одна с остатком), чтобы сумма чека совпала с оплатой.
 */
function arctik_receipt_products( WC_Order $order ): array {
	$tax      = (string) ( arctik_option( 'arctik_receipt_tax' ) ?: 'vat5' );
	$products = [];

	$add = static function ( string $name, int $cents, int $qty, int $item_type ) use ( &$products, $tax ) {
		if ( $cents <= 0 || $qty <= 0 ) {
			return;
		}

		$unit = intdiv( $cents, $qty );
		$rest = $cents - $unit * $qty;
		$name = mb_substr( wp_strip_all_tags( $name ), 0, 128 );
		$row  = [
			'name'      => $name,
			'tax'       => $tax,
			'unit'      => 'piece',
			'item_type' => $item_type,
		];

		if ( $rest ) {
			if ( $qty > 1 ) {
				$products[] = $row + [ 'price' => $unit / 100, 'quantity' => $qty - 1 ];
			}
			$products[] = $row + [ 'price' => ( $unit + $rest ) / 100, 'quantity' => 1 ];
		} else {
			$products[] = $row + [ 'price' => $unit / 100, 'quantity' => $qty ];
		}
	};

	foreach ( $order->get_items() as $item ) {
		$add( $item->get_name(), (int) round( (float) $order->get_line_total( $item, true, false ) * 100 ), (int) $item->get_quantity(), 1 ); // 1 — товар
	}

	$add( 'Доставка', (int) round( ( (float) $order->get_shipping_total() + (float) $order->get_shipping_tax() ) * 100 ), 1, 4 ); // 4 — услуга

	return $products;
}

/**
 * Запрос к LIFE PAY. Возвращает [ 'ok' => bool, 'retry' => bool, 'uuid' => string, 'message' => string ].
 */
function arctik_receipt_request( WC_Order $order, string $type ): array {
	$login  = arctik_receipt_phone( (string) arctik_option( 'arctik_receipt_login' ) );
	$apikey = trim( (string) arctik_option( 'arctik_receipt_apikey' ) );

	if ( ! $login || ! $apikey ) {
		return [ 'ok' => false, 'retry' => false, 'uuid' => '', 'message' => 'не заданы логин или API-ключ (Настройки сайта → Чеки)' ];
	}

	$test = arctik_receipt_is_test();
	$data = [
		'apikey'     => $apikey,
		'login'      => $login,
		'ext_id'     => ( $test ? 'test-' : '' ) . $type . '-' . $order->get_id(),
		'type'       => $type,
		'mode'       => 'email',
		'tax_system' => (string) ( arctik_option( 'arctik_receipt_tax_system' ) ?: 'usn6' ),
		'purchase'   => [ 'products' => arctik_receipt_products( $order ) ],
		// Сумма оплаты — итог заказа: не совпала с позициями — LIFE PAY вернёт ошибку данных, а не пробьёт чек на другую сумму
		'card_amount' => round( (float) $order->get_total(), 2 ),
	];

	$email = (string) $order->get_billing_email();
	$phone = arctik_receipt_phone( (string) $order->get_billing_phone() );

	if ( $email ) {
		$data['customer_email'] = $email;
	} elseif ( $phone ) {
		$data['customer_phone'] = '+' . $phone;
	}

	if ( $test ) {
		$data['test'] = 1;
	}

	$response = wp_remote_post( ARCTIK_RECEIPT_URL, [
		'timeout' => 20,
		'headers' => [ 'Content-Type' => 'application/json' ],
		'body'    => wp_json_encode( $data ),
	] );

	$status = (int) wp_remote_retrieve_response_code( $response );
	$body   = is_wp_error( $response ) ? null : json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 === $status && is_array( $body ) && 0 === (int) ( $body['code'] ?? -1 ) ) {
		return [ 'ok' => true, 'retry' => false, 'uuid' => (string) ( $body['data']['uuid'] ?? '' ), 'message' => '' ];
	}

	$code    = is_array( $body ) ? (int) ( $body['code'] ?? 0 ) : 0;
	$message = is_wp_error( $response )
		? $response->get_error_message()
		: trim( ( is_array( $body ) ? ( $body['message'] ?? '' ) . ( empty( $body['data'] ) ? '' : ' ' . wp_json_encode( $body['data'], JSON_UNESCAPED_UNICODE ) ) : $status . ' ' . wp_remote_retrieve_body( $response ) ) );

	arctik_receipt_log( sprintf( 'Заказ %d, чек %s: %s', $order->get_id(), $type, $message ) );

	return [ 'ok' => false, 'retry' => ! in_array( $code, ARCTIK_RECEIPT_FATAL, true ), 'uuid' => '', 'message' => $message ];
}

/**
 * Поставить чек в очередь (один раз на заказ и тип).
 */
function arctik_receipt_queue( WC_Order $order, string $type ): void {
	if ( ! arctik_receipt_enabled() || $order->get_meta( '_arctik_receipt_' . $type ) || ARCTIK_GATEWAY !== $order->get_payment_method() ) {
		return;
	}

	$order->update_meta_data( '_arctik_receipt_' . $type, 'queued' );
	$order->save();

	as_enqueue_async_action( 'arctik_receipt_send', [ $order->get_id(), $type, 0 ], 'arctik' );
}

add_action( 'arctik_receipt_send', function ( int $order_id, string $type, int $attempt ) {
	$order = wc_get_order( $order_id );

	if ( ! $order || ! isset( ARCTIK_RECEIPT_TYPES[ $type ] ) ) {
		return;
	}

	$result = arctik_receipt_request( $order, $type );
	$label  = 'LIFE PAY' . ( arctik_receipt_is_test() ? ' (тест)' : '' ) . ': чек ' . ARCTIK_RECEIPT_TYPES[ $type ];

	if ( $result['ok'] ) {
		$order->update_meta_data( '_arctik_receipt_' . $type, $result['uuid'] ?: 'test' );
		$order->add_order_note( $label . ( $result['uuid'] ? ' отправлен, uuid ' . $result['uuid'] : "\u{00A0}— данные приняты, в налоговую не отправлен" ) );
		$order->save();
		return;
	}

	if ( $result['retry'] && isset( ARCTIK_RECEIPT_RETRY[ $attempt ] ) ) {
		as_schedule_single_action( time() + ARCTIK_RECEIPT_RETRY[ $attempt ], 'arctik_receipt_send', [ $order_id, $type, $attempt + 1 ], 'arctik' );
		return;
	}

	$order->update_meta_data( '_arctik_receipt_' . $type, 'error' );
	$order->add_order_note( $label . ' не отправлен: ' . $result['message'] . '. Повторить — «Действия с заказом» → «Отправить чек…»' );
	$order->save();
}, 10, 3 );

// Оплачен — чек прихода (плагин банка ставит статус и через payment_complete, и через update_status — ловим смену статуса)
add_action( 'woocommerce_order_status_changed', function ( int $order_id, string $from, string $to, WC_Order $order ) {
	if ( in_array( $to, wc_get_is_paid_statuses(), true ) ) {
		arctik_receipt_queue( $order, 'payment' );
	}

	// Возврат — только если приход был пробит
	if ( 'refunded' === $to && ! in_array( $order->get_meta( '_arctik_receipt_payment' ), [ '', 'error' ], true ) ) {
		arctik_receipt_queue( $order, 'refund' );
	}
}, 10, 4 );

add_action( 'woocommerce_order_partially_refunded', function ( int $order_id ) {
	$order = wc_get_order( $order_id );

	if ( $order && arctik_receipt_enabled() && ! in_array( $order->get_meta( '_arctik_receipt_payment' ), [ '', 'error' ], true ) ) {
		$order->add_order_note( "LIFE PAY: частичный возврат\u{00A0}— чек возврата пробейте в\u{00A0}кабинете LIFE PAY" );
	}
} );

// Вручную: «Действия с заказом» (тот же ext_id — LIFE PAY второй чек не создаст)
add_filter( 'woocommerce_order_actions', function ( array $actions, $order ): array {
	if ( arctik_receipt_enabled() && $order instanceof WC_Order && ARCTIK_GATEWAY === $order->get_payment_method() && $order->is_paid() ) {
		$actions['arctik_receipt_payment'] = 'Отправить чек прихода в LIFE PAY';
	}

	if ( arctik_receipt_enabled() && $order instanceof WC_Order && $order->has_status( 'refunded' ) ) {
		$actions['arctik_receipt_refund'] = 'Отправить чек возврата в LIFE PAY';
	}

	return $actions;
}, 10, 2 );

foreach ( array_keys( ARCTIK_RECEIPT_TYPES ) as $arctik_type ) {
	add_action( 'woocommerce_order_action_arctik_receipt_' . $arctik_type, function ( WC_Order $order ) use ( $arctik_type ) {
		$order->update_meta_data( '_arctik_receipt_' . $arctik_type, 'queued' );
		$order->save();
		as_enqueue_async_action( 'arctik_receipt_send', [ $order->get_id(), $arctik_type, 0 ], 'arctik' );
	} );
}
