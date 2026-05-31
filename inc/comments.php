<?php

/**
 * Comments
 *
 * Comment callback and comment-related functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders a single comment item.
 *
 * @param WP_Comment $comment The comment object.
 * @param array      $args    Comment display arguments.
 * @param int        $depth   Nesting depth.
 */
function jasanika_comment( $comment, $args, $depth ) {
	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'comment-item', $comment ); ?>>
		<article class="comment-item__body">

			<header class="comment-item__header">
				<span class="comment-item__author"><?php echo get_comment_author_link( $comment ); ?></span>
				<time class="comment-item__date" datetime="<?php echo esc_attr( get_comment_date( 'c', $comment ) ); ?>">
					<?php
					printf(
						/* translators: 1: comment date, 2: comment time */
						esc_html__( '%1$s v %2$s', 'jasanika' ),
						esc_html( get_comment_date( '', $comment ) ),
						esc_html( get_comment_time( '', false, true, $comment ) )
					);
					?>
				</time>
			</header>

			<div class="comment-item__content">
				<?php comment_text( $comment ); ?>
			</div>

			<?php if ( (int) $args['max_depth'] !== (int) $depth ) : ?>
				<div class="comment-item__reply">
					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'add_below' => 'comment',
								'depth'     => $depth,
								'max_depth' => $args['max_depth'],
								'before'    => '',
								'after'     => '',
							)
						),
						$comment
					);
					?>
				</div>
			<?php endif; ?>

		</article>
	<?php
	// Note: closing </li> is output by Walker_Comment::end_el().
}
