<?php

/**
 * Content Single
 *
 * Reusable single post article component.
 * Used in single.php via get_template_part().
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post' ); ?>>

	<header class="single-post__header">

		<h1 class="single-post__title"><?php the_title(); ?></h1>

		<div class="single-post__meta">

			<time class="single-post__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
				<?php echo esc_html( get_the_date() ); ?>
			</time>

			<span class="single-post__author">
				<?php
				printf(
					/* translators: %s: author display name */
					esc_html__( '%s', 'jasanika' ),
					esc_html( get_the_author() )
				);
				?>
			</span>

			<?php
			$categories = get_the_category();
			if ( $categories ) :
			?>
				<div class="single-post__categories">
					<?php foreach ( $categories as $category ) : ?>
						<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" class="single-post__category-link">
							<?php echo esc_html( $category->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>

	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="single-post__featured-image">
			<?php the_post_thumbnail( 'large', array( 'class' => 'single-post__image' ) ); ?>
		</div>
	<?php endif; ?>

	<div class="single-post__content">
		<?php the_content(); ?>
	</div>

	<footer class="single-post__footer">

		<?php if ( $categories ) : ?>
			<div class="single-post__footer-categories">
				<span class="single-post__footer-label"><?php esc_html_e( 'Kategorie:', 'jasanika' ); ?></span>
				<?php foreach ( $categories as $category ) : ?>
					<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" class="single-post__tag-link">
						<?php echo esc_html( $category->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php
		$tags = get_the_tags();
		if ( $tags ) :
		?>
			<div class="single-post__footer-tags">
				<span class="single-post__footer-label"><?php esc_html_e( 'Štítky:', 'jasanika' ); ?></span>
				<?php foreach ( $tags as $tag ) : ?>
					<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="single-post__tag-link">
						<?php echo esc_html( $tag->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</footer>

</article>
