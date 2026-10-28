<?php
/**
 * Баннер cookie (этап Ку): тексты «Настройки сайта → Cookie» — из статики (src/partials/cookies.html).
 * Ссылки — на раздел cookie страницы «Документы» (/seed/documents.php запустить раньше).
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/cookies.php
 */

$documents = get_page_by_path( 'documents' );
$cookie_url = $documents ? get_permalink( $documents ) . '#cookie' : '#';

$options = [
	'arctik_cookie_banner_text'    => 'Мы используем файлы куки для корректной и быстрой работы сайта. Ознакомиться с политикой конфиденциальности. <a href="' . esc_url( $cookie_url ) . '">Подробнее</a>',
	'arctik_cookie_title'          => 'Мы используем файлы cookie',
	'arctik_cookie_text'           => 'Мы и наши партнёры используем файлы cookie и другие технологии, чтобы персонализировать ваш опыт, показывать рекламу и анализировать посещаемость сайта. Подробнее — в нашей <a href="' . esc_url( $cookie_url ) . '">Политике использования cookie</a>.',
	'arctik_cookie_note'           => 'Чтобы закрыть окно, сохраните настройки',
	'arctik_cookie_necessary_name' => 'Необходимые',
	'arctik_cookie_necessary_text' => 'Необходимые файлы cookie обеспечивают работу основных функций сайта, включая безопасный вход в аккаунт и сохранение ваших настроек конфиденциальности.',
	'arctik_cookie_analytics_name' => 'Аналитика',
	'arctik_cookie_analytics_text' => 'Помогают понять, как посетители пользуются сайтом: какие страницы открывают и где возникают сложности. Данные обезличены и нужны, чтобы делать сайт удобнее.',
	'arctik_cookie_marketing_name' => 'Маркетинг и реклама',
	'arctik_cookie_marketing_text' => 'Нужны, чтобы показывать вам подходящие предложения ArcticBerries на других сайтах и оценивать эффективность рекламы. Если их отключить, реклама станет менее персональной.',
];
foreach ( $options as $name => $value ) {
	carbon_set_theme_option( $name, $value );
}

WP_CLI::success( 'Тексты баннера cookie заполнены' );
