<?php
/**
 * Документы (src/partials/documents/main.html): разметка «Покупателям» (.help), вместо вопросов — текст документа.
 * Заголовок — название страницы, подпись, фон, подпись под меню и документы — поля страницы. Документ без текста не выводится.
 */

defined( 'ABSPATH' ) || exit;

$arctik_page = (int) get_the_ID();
$arctik_lead = (string) arctik_field( 'arctik_documents_lead', $arctik_page );
$arctik_bg   = (int) arctik_field( 'arctik_documents_bg', $arctik_page );
$arctik_note = (string) arctik_field( 'arctik_documents_note', $arctik_page );

// Подпись — одна строка: из редактора оставляем только строчные теги.
$arctik_note = trim( wp_kses( $arctik_note, [ 'a' => [ 'href' => [], 'target' => [], 'rel' => [] ], 'strong' => [], 'em' => [], 'br' => [] ] ) );

$arctik_docs = [];
foreach ( (array) arctik_field( 'arctik_documents', $arctik_page ) as $arctik_index => $arctik_doc ) {
	if ( '' === trim( (string) $arctik_doc['title'] ) || '' === trim( wp_strip_all_tags( (string) $arctik_doc['text'] ) ) ) {
		continue;
	}

	$arctik_docs[] = [
		'id'    => sanitize_html_class( (string) $arctik_doc['anchor'] ) ?: 'document-' . ( $arctik_index + 1 ),
		'title' => (string) $arctik_doc['title'],
		'menu'  => trim( (string) $arctik_doc['menu'] ) ?: (string) $arctik_doc['title'],
		'text'  => (string) $arctik_doc['text'],
	];
}
?>
<section class="help" data-header-theme="dark">
	<?php if ( $arctik_bg ) : ?>
		<div class="help__bg" aria-hidden="true">
			<?php echo arctik_image( $arctik_bg, 'full', [ 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	<?php endif; ?>

	<div class="help__intro">
		<?php if ( $arctik_lead ) : ?>
			<p class="help__lead" data-js-title-reveal data-title-reveal-start="top bottom"><?php echo arctik_lines( $arctik_lead ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		<?php endif; ?>
		<h1 class="help__title" data-js-title-reveal data-title-reveal-start="top bottom"><?php the_title(); ?></h1>
	</div>

	<?php if ( $arctik_docs ) : ?>
		<?php // Панель документов: меню прилипает при скролле, активный документ — js/modules/anchor-nav.js ?>
		<div class="help__panel" data-header-theme="light">
			<aside class="help__aside">
				<nav aria-label="Документы">
					<ul class="help__menu" data-js-anchor-nav>
						<?php foreach ( $arctik_docs as $arctik_index => $arctik_doc ) : ?>
							<li><a class="help__link<?php echo 0 === $arctik_index ? ' is-active' : ''; ?>" href="#<?php echo esc_attr( $arctik_doc['id'] ); ?>"<?php echo 0 === $arctik_index ? ' aria-current="true"' : ''; ?> data-js-roll><?php echo arctik_text( $arctik_doc['menu'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
				<?php if ( $arctik_note ) : ?>
					<p class="help__note"><?php echo arctik_typograph( $arctik_note ); // phpcs:ignore WordPress.Security.EscapeOutput -- wp_kses выше ?></p>
				<?php endif; ?>
			</aside>

			<div class="help__content">
				<?php foreach ( $arctik_docs as $arctik_doc ) : ?>
					<section class="help__group" id="<?php echo esc_attr( $arctik_doc['id'] ); ?>" aria-labelledby="<?php echo esc_attr( $arctik_doc['id'] ); ?>-title">
						<h2 class="help__group-title" id="<?php echo esc_attr( $arctik_doc['id'] ); ?>-title" data-js-title-reveal><?php echo arctik_text( $arctik_doc['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
						<div class="help__doc">
							<?php echo arctik_rich( $arctik_doc['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
