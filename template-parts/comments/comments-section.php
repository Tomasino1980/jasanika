<?php

/**
 * Comments Section
 *
 * Reusable component for rendering the comments area.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$comment_count = get_comments_number();
?>

<section class="comments-section">
	<div class="comments-section__inner">

		<?php if ( have_comments() ) : ?>

			<h2 class="comments-section__title">
				<?php
				printf(
					/* translators: %s: comment count */
					esc_html( _n( '%s komentář', '%s komentáře', $comment_count, 'jasanika' ) ),
					esc_html( number_format_i18n( $comment_count ) )
				);
				?>
			</h2>

			<ol class="comments-list">
				<?php
				wp_list_comments(
					array(
						'callback'    => 'jasanika_comment',
						'style'       => 'ol',
						'avatar_size' => 48,
					)
				);
				?>
			</ol>

			<?php
			the_comments_pagination(
				array(
					'prev_text' => esc_html__( '← Starší', 'jasanika' ),
					'next_text' => esc_html__( 'Novější →', 'jasanika' ),
				)
			);
			?>

		<?php elseif ( comments_open() ) : ?>

			<p class="comments-section__empty">
				<?php esc_html_e( 'Buďte první, kdo přidá komentář.', 'jasanika' ); ?>
			</p>

		<?php endif; ?>

		<?php if ( comments_open() ) : ?>
			<?php comment_form(); ?>
		<?php endif; ?>

	</div>
</section>
