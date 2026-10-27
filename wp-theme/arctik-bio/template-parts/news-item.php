<?php
/**
 * Слайд новости (src/partials/news-item.html).
 *
 * $args: post — запись arctik_news (поля — inc/fields/news.php).
 */

defined( 'ABSPATH' ) || exit;

$arctik_id     = (int) $args['post']->ID;
$arctik_link   = (string) arctik_field( 'arctik_news_link', $arctik_id );
$arctik_author = (string) arctik_field( 'arctik_news_author', $arctik_id );
$arctik_role   = (string) arctik_field( 'arctik_news_author_role', $arctik_id );
$arctik_avatar = (int) arctik_field( 'arctik_news_author_photo', $arctik_id );
$arctik_photo  = (int) arctik_field( 'arctik_news_image', $arctik_id );
$arctik_mobile = wp_get_attachment_image_src( (int) arctik_field( 'arctik_news_image_mobile', $arctik_id ), 'full' );
$arctik_title  = arctik_typograph( esc_html( get_the_title( $arctik_id ) ) );
?>
<li class="news-item swiper-slide">
	<div class="news-item__text">
		<div class="news-item__header">
			<h3 class="news-item__title">
				<?php if ( $arctik_link ) : ?>
					<a href="<?php echo esc_url( $arctik_link ); ?>" target="_blank" rel="noopener"><?php echo $arctik_title; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<?php else : ?>
					<?php echo $arctik_title; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endif; ?>
			</h3>
			<?php if ( has_excerpt( $arctik_id ) ) : ?>
				<p class="news-item__excerpt"><?php echo arctik_text( get_post_field( 'post_excerpt', $arctik_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
		<div class="news-item__meta">
			<?php if ( $arctik_author ) : ?>
				<div class="news-item__author">
					<?php echo arctik_image( $arctik_avatar, 'full', [ 'class' => 'news-item__author-img', 'alt' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<div>
						<p class="news-item__author-name"><?php echo arctik_text( $arctik_author ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						<?php if ( $arctik_role ) : ?>
							<p class="news-item__author-role"><?php echo arctik_text( $arctik_role ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			<time class="news-item__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $arctik_id ) ); ?>"><?php echo esc_html( get_the_date( 'j F, Y', $arctik_id ) ); ?></time>
		</div>
	</div>
	<?php if ( $arctik_photo ) : ?>
		<picture class="news-item__picture">
			<?php if ( $arctik_mobile ) : ?>
				<source media="(max-width: 767px)" srcset="<?php echo esc_url( $arctik_mobile[0] ); ?>" width="<?php echo esc_attr( $arctik_mobile[1] ); ?>" height="<?php echo esc_attr( $arctik_mobile[2] ); ?>">
			<?php endif; ?>
			<?php echo arctik_image( $arctik_photo, 'full', [ 'class' => 'news-item__img', 'alt' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</picture>
	<?php endif; ?>
</li>
