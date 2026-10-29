<?php
/**
 * Документы (src/partials/documents/main.html): разметка «Покупателям» (.help), вместо вопросов — текст документа.
 * Текущая страница — документ (или сами «Документы», пока документов нет). Подпись, фон, подпись под меню и заголовок
 * над панелью — со страницы «Документы»; в меню — все документы, у текущего is-active.
 */

defined( 'ABSPATH' ) || exit;

$arctik_current = (int) get_the_ID();
$arctik_page    = arctik_documents_page_id() ?: $arctik_current;
$arctik_lead    = (string) arctik_field( 'arctik_documents_lead', $arctik_page );
$arctik_bg      = (int) arctik_field( 'arctik_documents_bg', $arctik_page );
$arctik_note    = (string) arctik_field( 'arctik_documents_note', $arctik_page );
$arctik_docs    = arctik_documents();
$arctik_text    = $arctik_current !== $arctik_page ? (string) arctik_field( 'arctik_document_text', $arctik_current ) : '';

// Подпись — одна строка: из редактора оставляем только строчные теги.
$arctik_note = trim( wp_kses( $arctik_note, [ 'a' => [ 'href' => [], 'target' => [], 'rel' => [] ], 'strong' => [], 'em' => [], 'br' => [] ] ) );
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
		<?php // h1 — заголовок документа; пока документов нет — «Документы» ?>
		<<?php echo $arctik_docs ? 'p' : 'h1'; ?> class="help__title" data-js-title-reveal data-title-reveal-start="top bottom"><?php echo arctik_text( get_post_field( 'post_title', $arctik_page ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></<?php echo $arctik_docs ? 'p' : 'h1'; ?>>
	</div>

	<?php if ( $arctik_docs ) : ?>
		<?php // Панель документов: в меню — ссылки на страницы документов; вкладки планшета/мобилки подкручиваются к текущей — js/modules/anchor-nav.js ?>
		<div class="help__panel" data-header-theme="light">
			<aside class="help__aside">
				<nav aria-label="Документы">
					<ul class="help__menu" data-js-anchor-nav>
						<?php foreach ( $arctik_docs as $arctik_doc ) : ?>
							<?php
							$arctik_is_current = $arctik_doc->ID === $arctik_current;
							$arctik_menu       = trim( (string) arctik_field( 'arctik_document_menu', $arctik_doc->ID ) ) ?: $arctik_doc->post_title;
							?>
							<li><a class="help__link<?php echo $arctik_is_current ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_permalink( $arctik_doc ) ); ?>"<?php echo $arctik_is_current ? ' aria-current="page"' : ''; ?> data-js-roll><?php echo arctik_text( $arctik_menu ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
				<?php if ( $arctik_note ) : ?>
					<p class="help__note"><?php echo arctik_typograph( $arctik_note ); // phpcs:ignore WordPress.Security.EscapeOutput -- wp_kses выше ?></p>
				<?php endif; ?>
			</aside>

			<div class="help__content">
				<section class="help__group" aria-labelledby="document-title">
					<h1 class="help__group-title" id="document-title" data-js-title-reveal><?php the_title(); ?></h1>
					<?php if ( '' !== trim( wp_strip_all_tags( $arctik_text ) ) ) : ?>
						<div class="help__doc">
							<?php echo arctik_rich( $arctik_text ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					<?php endif; ?>
				</section>
			</div>
		</div>
	<?php endif; ?>
</section>
