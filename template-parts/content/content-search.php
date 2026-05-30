<?php

/**
 * Content Search
 *
 * Reusable search result card component.
 * Used in search.php via get_template_part().
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'search-card' ); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="search-card__image-link" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'medium_large', array( 'class' => 'search-card__image' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="search-card__body">

		<time class="search-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
			<?php echo esc_html( get_the_date() ); ?>
		</time>

		<h2 class="search-card__title">
			<a href="<?php the_permalink(); ?>" class="search-card__title-link">
				<?php the_title(); ?>
			</a>
		</h2>

		<p class="search-card__excerpt">
			<?php echo esc_html( wp_trim_words( get_the_excerpt(), 25, '…' ) ); ?>
		</p>

		<a href="<?php the_permalink(); ?>" class="btn btn-outline search-card__read-more">
			<?php esc_html_e( 'Číst více', 'jasanika' ); ?>
		</a>

	</div>

</article>
