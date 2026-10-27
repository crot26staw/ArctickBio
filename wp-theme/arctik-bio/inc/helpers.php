<?php
/**
 * Хелперы вывода для шаблонов.
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL статичного файла темы: картинки, иконки, видео (копируются из src/assets сборкой).
 * arctik_asset( 'icons/icn-cart.svg' ) → …/themes/arctik-bio/assets/icons/icn-cart.svg
 */
function arctik_asset( string $path ): string {
	return get_theme_file_uri( 'assets/' . ltrim( $path, '/' ) );
}

/**
 * Обычный текст из поля: экранирование + типограф.
 */
function arctik_text( ?string $text ): string {
	return arctik_typograph( esc_html( (string) $text ) );
}

/**
 * Текст с разметкой из поля (rich text): разрешённые теги + типограф.
 */
function arctik_rich( ?string $html ): string {
	return arctik_typograph( wp_kses_post( (string) $html ) );
}

/**
 * <img> из медиатеки: width/height, srcset, alt из медиатеки; ниже первого экрана — loading="lazy" (по умолчанию WP),
 * на первом экране передавать [ 'loading' => 'eager', 'fetchpriority' => 'high' ].
 */
function arctik_image( int $attachment_id, string $size = 'full', array $attr = [] ): string {
	if ( ! $attachment_id ) {
		return '';
	}

	return wp_get_attachment_image( $attachment_id, $size, false, $attr );
}

/**
 * Значение поля Carbon Fields у записи (по умолчанию — текущей). Имя — полное, с префиксом: 'arctik_hero_title'.
 */
function arctik_field( string $name, ?int $post_id = null ): mixed {
	if ( ! function_exists( 'carbon_get_post_meta' ) ) {
		return null;
	}

	return carbon_get_post_meta( $post_id ?? (int) get_the_ID(), $name );
}

/**
 * Значение из настроек темы (Theme Options Carbon Fields).
 */
function arctik_option( string $name ): mixed {
	if ( ! function_exists( 'carbon_get_theme_option' ) ) {
		return null;
	}

	return carbon_get_theme_option( $name );
}
