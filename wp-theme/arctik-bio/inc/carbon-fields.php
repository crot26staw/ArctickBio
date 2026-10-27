<?php
/**
 * Carbon Fields: загрузка из vendor/ (composer install) и регистрация полей из inc/fields/*.php.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	$autoload = get_theme_file_path( 'vendor/autoload.php' );

	if ( ! is_readable( $autoload ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>АрктикБио: не установлен Carbon Fields — выполните <code>composer install</code> в папке темы.</p></div>';
		} );

		return;
	}

	require_once $autoload;
	\Carbon_Fields\Carbon_Fields::boot();
} );

add_action( 'carbon_fields_register_fields', function () {
	// Сначала «Настройки сайта» — к ним крепятся подстраницы (set_page_parent) из остальных файлов.
	$files = glob( get_theme_file_path( 'inc/fields/*.php' ) ) ?: [];
	usort( $files, fn( $a, $b ) => ( 'theme-options.php' !== basename( $a ) ) <=> ( 'theme-options.php' !== basename( $b ) ) ?: strcmp( $a, $b ) );

	foreach ( $files as $file ) {
		require_once $file;
	}
} );

/**
 * Русский интерфейс полей в админке.
 * Carbon Fields собирает переводы для JS по ключам $translations->entries, а с WP 6.5 там список с числовыми ключами —
 * интерфейс оставался английским. Пересобираем словарь «оригинал → перевод» и добавляем строки, которых нет в ru_RU.
 */
add_filter( 'carbon_fields_config', function ( array $config ): array {
	$translations = get_translations_for_domain( 'carbon-fields-ui' );
	$locale       = [ '' => $config['config']['locale'][''] ?? [ 'domain' => 'carbon-fields-ui' ] ];

	foreach ( $translations->entries as $entry ) {
		$key            = $entry->context ? $entry->context . "\u{0004}" . $entry->singular : $entry->singular;
		$locale[ $key ] = $entry->translations;
	}

	$missing = [
		'Add'                       => 'Добавить',
		'Clear'                     => 'Очистить',
		'Click to edit'             => 'Нажмите, чтобы изменить',
		'Close'                     => 'Закрыть',
		'Edit'                      => 'Изменить',
		'No results found.'         => 'Ничего не найдено.',
		'Show All Options'          => 'Показать все варианты',
		'Show Less Options'         => 'Показать меньше вариантов',
		'There are no entries yet.' => 'Пока ничего не добавлено.',
		'Thumbnail'                 => 'Миниатюра',
	];
	foreach ( $missing as $original => $translation ) {
		if ( empty( $locale[ $original ][0] ) ) {
			$locale[ $original ] = [ $translation ];
		}
	}

	$config['config']['locale'] = $locale;

	return $config;
} );
