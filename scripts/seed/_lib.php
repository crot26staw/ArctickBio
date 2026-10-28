<?php
/**
 * Общее для сидов: загрузка файлов темы (assets/images, assets/video) в медиатеку.
 */

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

/**
 * Файл из assets темы в медиатеку (один раз — повторно находится по метке _arctik_seed).
 * $file — путь от assets/: 'video/hero.mp4'; без папки — assets/images.
 */
function arctik_seed_file( string $file, string $alt = '' ): int {
	if ( ! str_contains( $file, '/' ) ) {
		$file = "images/{$file}";
	}
	$key = str_starts_with( $file, 'images/' ) ? substr( $file, 7 ) : $file;

	$found = get_posts( [ 'post_type' => 'attachment', 'post_status' => 'any', 'meta_key' => '_arctik_seed', 'meta_value' => $key, 'fields' => 'ids', 'numberposts' => 1 ] );
	if ( $found ) {
		return (int) $found[0];
	}

	$tmp = wp_tempnam( basename( $file ) );
	copy( get_theme_file_path( "assets/{$file}" ), $tmp );
	$id = media_handle_sideload( [ 'name' => basename( $file ), 'tmp_name' => $tmp ], 0 );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( "{$file}: " . $id->get_error_message() );
	}

	update_post_meta( $id, '_arctik_seed', $key );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );

	return (int) $id;
}

/**
 * Картинка из assets/images (совместимость со старыми сидами).
 */
function arctik_seed_image( string $file, string $alt = '' ): int {
	return arctik_seed_file( $file, $alt );
}

/**
 * Текст заказчика из scripts/seed/content/ (имя файла).
 */
function arctik_seed_content( string $file ): string {
	return (string) file_get_contents( __DIR__ . "/content/{$file}" );
}

/**
 * FAQ из документа заказчика: блоки через пустую строку; блок из одной строки ПРОПИСНЫМИ — раздел,
 * иначе первая строка — вопрос, остальные — ответ.
 *
 * @return array<int, array{title: string, questions: array<int, array{question: string, answer: string}>}>
 */
function arctik_seed_faq( string $file ): array {
	$groups = [];
	foreach ( preg_split( '/\R\s*\R/u', trim( arctik_seed_content( $file ) ) ) as $block ) {
		$lines = array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', $block ) ) ) );
		if ( ! $lines ) {
			continue;
		}
		if ( mb_strtoupper( $lines[0] ) === $lines[0] ) {
			$groups[] = [ 'title' => array_shift( $lines ), 'questions' => [] ];
		}
		if ( $lines && $groups ) {
			$groups[ array_key_last( $groups ) ]['questions'][] = [ 'question' => $lines[0], 'answer' => implode( "\n", array_slice( $lines, 1 ) ) ];
		}
	}

	return $groups;
}
