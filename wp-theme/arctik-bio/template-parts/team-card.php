<?php
/**
 * Карточка сотрудника (src/partials/team-card.html).
 *
 * $args: photo — ID картинки, name, job, exp.
 */

defined( 'ABSPATH' ) || exit;
?>
<li class="team-card">
	<?php echo arctik_image( (int) $args['photo'], 'full', [ 'class' => 'team-card__img', 'alt' => $args['name'] ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<div class="team-card__info">
		<p class="team-card__job"><?php echo arctik_text( $args['job'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		<div>
			<p class="team-card__name"><?php echo arctik_text( $args['name'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<p class="team-card__exp"><?php echo arctik_text( $args['exp'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		</div>
	</div>
</li>
