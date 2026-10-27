<?php
/**
 * «Почему выбирают АрктикБиоМарин» (src/partials/home/comparison.html). Контент — «Настройки сайта → Общие блоки → Почему выбирают».
 * Появление по скроллу — js/modules/reveal-sequence.js.
 *
 * $args: mod — модификаторы секции (comparison--spaced).
 */

defined( 'ABSPATH' ) || exit;

$arctik_image_id = (int) arctik_option( 'arctik_comparison_image' );
?>
<section class="comparison container <?php echo esc_attr( $args['mod'] ?? '' ); ?>" data-header-theme="light" data-js-reveal-sequence>
	<h2 class="comparison__title section-title" data-js-reveal-sequence-item data-reveal-sequence-split><?php echo arctik_lines( arctik_option( 'arctik_comparison_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>

	<div class="comparison__content">
		<?php echo arctik_image( $arctik_image_id, 'full', [ 'class' => 'comparison__img', 'loading' => 'lazy', 'data-js-reveal-sequence-item' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<div class="comparison__table-wrap" data-js-reveal-sequence-item>
			<table class="comparison__table">
				<colgroup>
					<col class="comparison__col-feature">
					<col>
					<col>
				</colgroup>
				<thead>
					<tr>
						<th class="comparison__head comparison__head--feature" scope="col"><?php echo arctik_text( arctik_option( 'arctik_comparison_head_feature' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></th>
						<th class="comparison__head" scope="col">
							<?php echo arctik_image( (int) arctik_option( 'arctik_comparison_pack' ), 'medium', [ 'class' => 'comparison__head-img', 'alt' => 'АрктикБиоМарин', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</th>
						<th class="comparison__head" scope="col"><?php echo arctik_text( arctik_option( 'arctik_comparison_head_other' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( (array) arctik_option( 'arctik_comparison_rows' ) as $arctik_row ) : ?>
						<tr>
							<th class="comparison__feature" scope="row"><?php echo arctik_text( $arctik_row['feature'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></th>
							<td class="comparison__cell">
								<div class="comparison__cell-inner">
									<img src="<?php echo esc_url( arctik_asset( 'icons/icn-check.svg' ) ); ?>" alt="Да" width="32" height="24">
									<p class="comparison__cell-text"><?php echo arctik_lines( $arctik_row['ours'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
								</div>
							</td>
							<td class="comparison__cell">
								<div class="comparison__cell-inner">
									<img src="<?php echo esc_url( arctik_asset( 'icons/icn-circle.svg' ) ); ?>" alt="Нет" width="24" height="24">
									<p class="comparison__cell-text"><?php echo arctik_lines( $arctik_row['other'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>
