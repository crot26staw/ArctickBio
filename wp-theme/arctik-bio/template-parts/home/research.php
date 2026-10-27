<?php
/**
 * «Исследования» (src/partials/home/research.html): фото с заголовком и список пунктов — раскрыт один, сменяются
 * по скроллу (js/modules/research.js), последний — документы. Поля — главная, вкладка «Исследования».
 */

defined( 'ABSPATH' ) || exit;

$arctik_page  = (int) get_option( 'page_on_front' );
$arctik_items = (array) arctik_field( 'arctik_home_research_items', $arctik_page );
$arctik_docs  = array_filter( (array) arctik_field( 'arctik_home_research_docs', $arctik_page ), fn( $doc ) => ! empty( $doc['title'] ) );
$arctik_num   = 0;
if ( ! $arctik_items && ! $arctik_docs ) {
	return;
}
?>
<section class="research" data-header-theme="light">
	<div class="research__media">
		<?php echo arctik_image( (int) arctik_field( 'arctik_home_research_image', $arctik_page ), 'full', [ 'class' => 'research__img', 'alt' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<h2 class="research__title" data-js-title-reveal><?php echo arctik_text( arctik_field( 'arctik_home_research_title', $arctik_page ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
	</div>

	<?php // Пункты сменяются по скроллу, секция закреплена (research.js, таймлайн со scrub): открыт один, по умолчанию первый. ?>
	<div class="research__content">
		<ul class="research-list" data-js-research-list>
			<?php foreach ( $arctik_items as $arctik_item ) : ?>
				<?php
				$arctik_prefix = trim( (string) ( $arctik_item['prefix'] ?? '' ) );
				$arctik_number = (int) ( $arctik_item['number'] ?? 0 );
				?>
				<li class="research-list__item<?php echo 0 === $arctik_num ? ' is-active' : ''; ?>" data-js-research-item>
					<div class="research-list__row">
						<span class="research-list__num"><?php echo esc_html( sprintf( '%02d', ++$arctik_num ) ); ?></span>
						<div class="research-list__body">
							<div class="research-list__collapse" data-js-research-collapse>
								<div class="research-list__inner">
									<p class="research-list__label"><?php echo arctik_text( $arctik_item['label'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
								</div>
							</div>
							<div class="research-list__expand" data-js-research-expand>
								<div class="research-list__inner">
									<div class="research-list__details" data-js-research-content>
										<p class="research-list__value"><?php echo $arctik_prefix ? esc_html( $arctik_prefix ) . ' ' : ''; ?><span class="research-list__number" data-js-number-ticker data-value="<?php echo esc_attr( $arctik_number ); ?>"><?php echo esc_html( $arctik_number ); ?></span><?php echo esc_html( (string) ( $arctik_item['suffix'] ?? '' ) ); ?></p>
										<p class="research-list__title"><?php echo arctik_text( $arctik_item['title'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
										<p class="research-list__text"><?php echo arctik_lines( $arctik_item['text'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="research-list__expand" data-js-research-expand>
						<div class="research-list__inner">
							<div class="research-list__media" data-js-research-content>
								<?php echo arctik_image( (int) ( $arctik_item['image'] ?? 0 ), 'large', [ 'class' => 'research-list__img', 'alt' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</div>
						</div>
					</div>
				</li>
			<?php endforeach; ?>

			<?php if ( $arctik_docs ) : ?>
				<li class="research-list__item<?php echo 0 === $arctik_num ? ' is-active' : ''; ?>" data-js-research-item>
					<div class="research-list__row">
						<span class="research-list__num"><?php echo esc_html( sprintf( '%02d', ++$arctik_num ) ); ?></span>
						<div class="research-list__body">
							<p class="research-list__label"><?php echo arctik_text( arctik_field( 'arctik_home_research_docs_label', $arctik_page ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						</div>
					</div>
					<div class="research-list__expand" data-js-research-expand>
						<div class="research-list__inner">
							<div class="research-list__media" data-js-research-content>
								<ul class="research-docs">
									<?php
									foreach ( $arctik_docs as $arctik_doc ) {
										get_template_part( 'template-parts/research-doc', null, $arctik_doc );
									}
									?>
								</ul>
							</div>
						</div>
					</div>
				</li>
			<?php endif; ?>
		</ul>
	</div>
</section>
