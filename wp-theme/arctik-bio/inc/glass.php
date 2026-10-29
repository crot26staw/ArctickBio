<?php
/**
 * Фон «стеклянных» блоков из медиатеки (benefits, отзывы, соцсети «О компании»).
 * Карточкам-стеклу нужна заранее размытая копия фона (js/modules/fake-glass.js, --glass-image) — тема делает её сама:
 * как в статике, кадр под ширину показа в макете, уменьшенный вдвое, размытие — blur из CSS, тоже вдвое.
 * Копия лежит рядом с оригиналом в uploads (-glass<blur>-<ширина>.webp), имя — в мете вложения _arctik_glass; удаляется вместе с ним.
 * Делается при сохранении полей (arctik_glass_warm), на случай сбоя — при первом выводе.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Поля фонов: [ имя поля, где хранится (option / home / about), blur в CSS, ширина показа в макете ].
 */
function arctik_glass_fields(): array {
	return [
		[ 'arctik_benefits_bg', 'option', 14, 1440 ],
		[ 'arctik_home_reviews_bg', 'home', 16, 1440 ],
		[ 'arctik_media_bg', 'about', 16, 1440 ],
		[ 'arctik_media_bg_tablet', 'about', 16, 768 ],
	];
}

/**
 * Фон из поля: [ 'id' => вложение, 'glass' => URL размытой копии ]. Поле пустое или копию сделать не удалось — null
 * (тогда шаблон выводит картинку темы: стекло без своей копии съехало бы).
 */
function arctik_glass_bg( int $attachment_id, int $blur, int $width ): ?array {
	if ( ! $attachment_id ) {
		return null;
	}

	$glass = arctik_glass_url( $attachment_id, $blur, $width );

	return $glass ? [ 'id' => $attachment_id, 'glass' => $glass ] : null;
}

/**
 * URL размытой копии вложения; нет — создаёт. Не вышло — пустая строка.
 */
function arctik_glass_url( int $attachment_id, int $blur, int $width ): string {
	$file = get_attached_file( $attachment_id );
	if ( ! $file || ! is_file( $file ) ) {
		return '';
	}

	$key   = "{$blur}-{$width}";
	$saved = (array) get_post_meta( $attachment_id, '_arctik_glass', true );
	$name  = $saved[ $key ] ?? '';
	$dir   = dirname( $file );

	if ( ! $name || ! is_file( "{$dir}/{$name}" ) ) {
		$name = arctik_glass_make( $file, $blur, $width );
		if ( ! $name ) {
			return '';
		}

		$saved[ $key ] = $name;
		update_post_meta( $attachment_id, '_arctik_glass', array_filter( $saved ) );
	}

	return dirname( wp_get_attachment_url( $attachment_id ) ) . '/' . rawurlencode( $name );
}

/**
 * Размытая копия файла рядом с ним. Imagick, без него — GD. Возвращает имя файла или ''.
 */
function arctik_glass_make( string $file, int $blur, int $width ): string {
	$width = (int) round( $width / 2 );
	$sigma = $blur / 2;
	$base  = pathinfo( $file, PATHINFO_FILENAME ) . "-glass{$blur}-{$width}";
	$dir   = dirname( $file );

	if ( extension_loaded( 'imagick' ) ) {
		try {
			$image = new Imagick( $file );
			$image->setIteratorIndex( 0 );
			$image->autoOrient();
			$image->thumbnailImage( $width, 0 );
			// Края кадра не темнеют: за краем — те же крайние пиксели
			$image->setImageVirtualPixelMethod( Imagick::VIRTUALPIXELMETHOD_EDGE );
			$image->blurImage( 0, $sigma );
			$image->stripImage();

			$ext = in_array( 'WEBP', Imagick::queryFormats( 'WEBP' ), true ) ? 'webp' : 'jpg';
			$image->setImageFormat( $ext );
			$image->setImageCompressionQuality( 80 );
			$image->writeImage( "{$dir}/{$base}.{$ext}" );
			$image->clear();

			return "{$base}.{$ext}";
		} catch ( Exception $e ) {
			// Не вышло — пробуем GD
		}
	}

	return arctik_glass_make_gd( $file, $dir, $base, $width, $sigma );
}

/**
 * GD: свой гаусс у GD — 3×3 (σ ≈ 0.7), поэтому кадр уменьшается так, чтобы размытие стало 2 px (8 проходов), и растягивается обратно.
 */
function arctik_glass_make_gd( string $file, string $dir, string $base, int $width, float $sigma ): string {
	if ( ! function_exists( 'imagecreatefromstring' ) ) {
		return '';
	}

	$source = @imagecreatefromstring( (string) file_get_contents( $file ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
	if ( ! $source ) {
		return '';
	}

	$height = (int) round( imagesy( $source ) * $width / imagesx( $source ) );
	$factor = max( 1, $sigma / 2 );
	$small  = imagecreatetruecolor( max( 1, (int) round( $width / $factor ) ), max( 1, (int) round( $height / $factor ) ) );
	imagecopyresampled( $small, $source, 0, 0, 0, 0, imagesx( $small ), imagesy( $small ), imagesx( $source ), imagesy( $source ) );
	imagedestroy( $source );

	for ( $i = 0; $i < 8; $i++ ) {
		imagefilter( $small, IMG_FILTER_GAUSSIAN_BLUR );
	}

	$result = imagecreatetruecolor( $width, $height );
	imagecopyresampled( $result, $small, 0, 0, 0, 0, $width, $height, imagesx( $small ), imagesy( $small ) );
	imagedestroy( $small );

	$ext   = function_exists( 'imagewebp' ) ? 'webp' : 'jpg';
	$saved = 'webp' === $ext ? imagewebp( $result, "{$dir}/{$base}.webp", 80 ) : imagejpeg( $result, "{$dir}/{$base}.jpg", 80 );
	imagedestroy( $result );

	return $saved ? "{$base}.{$ext}" : '';
}

/**
 * Копии для всех заполненных полей — после сохранения настроек или страницы, чтобы первый посетитель не ждал.
 */
function arctik_glass_warm(): void {
	$pages = [
		'home'  => (int) get_option( 'page_on_front' ),
		'about' => (int) ( get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/about.php', 'number' => 1 ] )[0]->ID ?? 0 ),
	];

	foreach ( arctik_glass_fields() as [ $name, $where, $blur, $width ] ) {
		$id = 'option' === $where ? arctik_option( $name ) : ( $pages[ $where ] ? arctik_field( $name, $pages[ $where ] ) : 0 );

		if ( $id ) {
			arctik_glass_url( (int) $id, $blur, $width );
		}
	}
}
add_action( 'carbon_fields_theme_options_container_saved', 'arctik_glass_warm' );
add_action( 'carbon_fields_post_meta_container_saved', 'arctik_glass_warm' );

// Вложение удалено — копии тоже
add_action( 'delete_attachment', function ( int $attachment_id ): void {
	$file = get_attached_file( $attachment_id );
	if ( ! $file ) {
		return;
	}

	foreach ( (array) get_post_meta( $attachment_id, '_arctik_glass', true ) as $name ) {
		if ( $name ) {
			wp_delete_file( dirname( $file ) . '/' . $name );
		}
	}
} );
