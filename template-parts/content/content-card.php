<?php

/**
 * Content Card
 *
 * Reusable post card component.
 * Used in archive.php via get_template_part().
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'archive-card' ); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="archive-card__image-link" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'medium_large', array( 'class' => 'archive-card__image' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="archive-card__body">

		<time class="archive-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
			<?php echo esc_html( get_the_date() ); ?>
		</time>

		<h2 class="archive-card__title">
			<a href="<?php the_permalink(); ?>" class="archive-card__title-link">
				<?php the_title(); ?>
			</a>
		</h2>

		<p class="archive-card__excerpt">
			<?php echo esc_html( wp_trim_words( get_the_excerpt(), 25, '…' ) ); ?>
		</p>

		<a href="<?php the_permalink(); ?>" class="btn btn-outline archive-card__read-more">
			<?php esc_html_e( 'Číst více', 'jasanika' ); ?>
		</a>

	</div>

</article>
