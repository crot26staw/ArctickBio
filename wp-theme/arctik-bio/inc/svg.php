<?php
/**
 * SVG в медиатеке. WP не пускает SVG: в нём может быть скрипт. Поэтому файл при загрузке чистится —
 * убираются скрипты, обработчики on*, ссылки javascript: и т.п., сущности XML — отказ; не разобрался как XML — отказ.
 * Размеры (для width/height у <img> и превью в медиатеке) — из width/height или viewBox файла.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'upload_mimes', function ( array $mimes ): array {
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
} );

// finfo определяет SVG то как image/svg+xml, то как text/xml / text/plain — WP тогда отказывает.
add_filter( 'wp_check_filetype_and_ext', function ( array $data, string $file, string $filename ): array {
	if ( 'svg' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}

	return $data;
}, 10, 3 );

$arctik_svg_prefilter = function ( array $file ): array {
	if ( 'svg' !== strtolower( pathinfo( (string) $file['name'], PATHINFO_EXTENSION ) ) ) {
		return $file;
	}

	$svg = arctik_svg_sanitize( (string) file_get_contents( $file['tmp_name'] ) );

	if ( null === $svg ) {
		$file['error'] = 'Не удалось прочитать SVG — сохраните файл заново (например, «Экспорт → SVG» в Figma).';
	} else {
		file_put_contents( $file['tmp_name'], $svg );
	}

	return $file;
};
add_filter( 'wp_handle_upload_prefilter', $arctik_svg_prefilter );
add_filter( 'wp_handle_sideload_prefilter', $arctik_svg_prefilter );

/**
 * Чистый SVG или null, если это не SVG / в нём сущности XML.
 */
function arctik_svg_sanitize( string $svg ): ?string {
	// Обычный DOCTYPE (Illustrator) убирается, с внутренними объявлениями/сущностями — отказ.
	$svg = (string) preg_replace( '/<!DOCTYPE[^>\[]*>/i', '', $svg );

	if ( '' === trim( $svg ) || preg_match( '/<!DOCTYPE|<!ENTITY/i', $svg ) ) {
		return null;
	}

	$doc      = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$loaded   = $doc->loadXML( $svg, LIBXML_NONET );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded || ! $doc->documentElement || 'svg' !== strtolower( $doc->documentElement->localName ) ) {
		return null;
	}

	$banned = [ 'script', 'foreignobject', 'iframe', 'object', 'embed', 'audio', 'video', 'canvas', 'handler', 'listener' ];
	$remove = [];

	foreach ( $doc->getElementsByTagName( '*' ) as $el ) {
		$name = strtolower( $el->localName );

		// <animate attributeName="href" to="javascript:…"> подменяет ссылку уже в браузере.
		if ( in_array( $name, $banned, true ) || ( in_array( $name, [ 'animate', 'set' ], true ) && str_contains( strtolower( $el->getAttribute( 'attributeName' ) ), 'href' ) ) ) {
			$remove[] = $el;
			continue;
		}

		foreach ( iterator_to_array( $el->attributes ) as $attr ) {
			$attr_name = strtolower( $attr->localName );
			$value     = strtolower( preg_replace( '/[\s\x00-\x1f]+/', '', $attr->value ) );

			if ( str_starts_with( $attr_name, 'on' )
				|| ( 'href' === $attr_name && ! preg_match( '~^(#|https?://|data:image/(png|jpe?g|gif|webp);)~', $value ) )
				|| str_contains( $value, 'javascript:' ) ) {
				$el->removeAttributeNode( $attr );
			}
		}
	}

	foreach ( $remove as $el ) {
		$el->parentNode?->removeChild( $el );
	}

	return $doc->saveXML( $doc->documentElement );
}

/**
 * [ ширина, высота ] SVG по width/height (без процентов), иначе по viewBox.
 */
function arctik_svg_size( string $path ): array {
	$svg = is_readable( $path ) ? (string) file_get_contents( $path ) : '';

	if ( ! preg_match( '/<svg\b[^>]*>/i', $svg, $tag ) ) {
		return [ 0, 0 ];
	}

	$attr = fn ( string $name ): string => preg_match( '/\s' . $name . '\s*=\s*["\']([^"\']*)["\']/i', $tag[0], $m ) ? $m[1] : '';

	$width  = str_contains( $attr( 'width' ), '%' ) ? 0 : (float) $attr( 'width' );
	$height = str_contains( $attr( 'height' ), '%' ) ? 0 : (float) $attr( 'height' );

	if ( ( ! $width || ! $height ) && $box = preg_split( '/[\s,]+/', trim( $attr( 'viewBox' ) ) ) ) {
		if ( 4 === count( $box ) ) {
			$width  = (float) $box[2];
			$height = (float) $box[3];
		}
	}

	return [ (int) round( $width ), (int) round( $height ) ];
}

add_filter( 'wp_generate_attachment_metadata', function ( array $meta, int $attachment_id ): array {
	if ( 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
		return $meta;
	}

	[ $meta['width'], $meta['height'] ] = arctik_svg_size( (string) get_attached_file( $attachment_id ) );
	$meta['file'] = _wp_relative_upload_path( (string) get_attached_file( $attachment_id ) );

	return $meta;
}, 10, 2 );

// Превью SVG в медиатеке и в полях Carbon Fields (иначе — значок файла).
add_filter( 'wp_prepare_attachment_for_js', function ( array $response, WP_Post $attachment ): array {
	if ( 'image/svg+xml' !== $attachment->post_mime_type ) {
		return $response;
	}

	[ $width, $height ] = arctik_svg_size( (string) get_attached_file( $attachment->ID ) );
	$full               = [
		'url'         => $response['url'],
		'width'       => $width,
		'height'      => $height,
		'orientation' => $height > $width ? 'portrait' : 'landscape',
	];

	$response['sizes'] = array_merge( [ 'full' => $full, 'medium' => $full, 'thumbnail' => $full ], (array) ( $response['sizes'] ?? [] ) );
	$response['image'] = [ 'src' => $response['url'], 'width' => $width, 'height' => $height ];

	return $response;
}, 10, 2 );
