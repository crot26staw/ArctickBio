<?php
/**
 * Наполнение «Контактов» (этап «Кн»): шаблон страницы и поля — тексты и фото из статики.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/contacts.php
 */

require_once __DIR__ . '/_lib.php';

$page = get_page_by_path( 'contacts' );
if ( ! $page ) {
	WP_CLI::error( 'Нет страницы «Контакты» — сначала /seed/base.php' );
}

update_post_meta( $page->ID, '_wp_page_template', 'page-templates/contacts.php' );

carbon_set_post_meta( $page->ID, 'arctik_contacts_lead', 'Остались вопросы?' );
carbon_set_post_meta( $page->ID, 'arctik_contacts_text', "Напишите нам — поможем\nс выбором продукта или заказом" );
carbon_set_post_meta( $page->ID, 'arctik_contacts_photo', arctik_seed_file( 'contacts-bg.webp' ) );
carbon_set_post_meta( $page->ID, 'arctik_contacts_photo_tablet', arctik_seed_file( 'contacts-bg-tablet.webp' ) );
carbon_set_post_meta( $page->ID, 'arctik_contacts_photo_mobile', arctik_seed_file( 'contacts-bg-mobile.webp' ) );

WP_CLI::success( 'Контакты заполнены' );
