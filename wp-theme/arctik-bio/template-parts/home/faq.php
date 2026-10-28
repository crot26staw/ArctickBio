<?php
/**
 * «Частые вопросы» (src/partials/home/faq.html). Заголовок, текст, кнопка, подпись — главная, вкладка «Частые вопросы»;
 * там же вопросы — свой список (решение пользователя, этап Кт2); кнопка ведёт на «Покупателям»; соцсети — «Настройки сайта».
 * Открыт первый вопрос, как на «Покупателям».
 */

defined( 'ABSPATH' ) || exit;

$arctik_page      = (int) get_option( 'page_on_front' );
$arctik_questions = array_values( array_filter( (array) arctik_field( 'arctik_home_faq_items', $arctik_page ), fn( $item ) => '' !== trim( (string) ( $item['question'] ?? '' ) ) ) );
if ( ! $arctik_questions ) {
	return;
}

$arctik_buyers = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/buyers.php', 'number' => 1 ] );
$arctik_buyers = $arctik_buyers ? $arctik_buyers[0]->ID : 0;

$arctik_text   = (string) arctik_field( 'arctik_home_faq_text', $arctik_page );
$arctik_button = (string) arctik_field( 'arctik_home_faq_button', $arctik_page );
$arctik_aside  = (string) arctik_field( 'arctik_home_faq_aside', $arctik_page );
?>
<section class="faq" data-header-theme="light" data-js-reveal-sequence data-reveal-sequence-play>
	<div class="faq__inner container">
		<div class="faq__main">
			<div class="faq__heading">
				<h2 class="faq__title" data-js-reveal-sequence-item data-reveal-sequence-split><?php echo arctik_text( arctik_field( 'arctik_home_faq_title', $arctik_page ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php if ( $arctik_text ) : ?>
					<p class="faq__text" data-js-reveal-sequence-item><?php echo arctik_lines( $arctik_text ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
				<?php if ( $arctik_button && $arctik_buyers ) : ?>
					<a class="faq__btn btn btn--accent" href="<?php echo esc_url( get_permalink( $arctik_buyers ) ); ?>" data-js-swipe data-js-reveal-sequence-item><?php echo arctik_text( $arctik_button ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<?php endif; ?>
			</div>

			<div class="faq__list" data-js-accordion-group data-js-reveal-sequence-item data-reveal-sequence-children>
				<?php
				foreach ( $arctik_questions as $arctik_i => $arctik_item ) {
					get_template_part( 'template-parts/accordion-item', null, [
						'id'       => 'faq-' . ( $arctik_i + 1 ),
						'open'     => 0 === $arctik_i,
						'question' => $arctik_item['question'],
						'answer'   => $arctik_item['answer'] ?? '',
					] );
				}
				?>
			</div>
		</div>

		<div class="faq__aside" data-js-reveal-sequence-item data-reveal-sequence-gap="1.5">
			<?php if ( $arctik_aside ) : ?>
				<p><?php echo arctik_lines( $arctik_aside ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
			<?php get_template_part( 'template-parts/socials', null, [ 'mod' => 'socials--large', 'size' => 32 ] ); ?>
		</div>
	</div>
</section>
