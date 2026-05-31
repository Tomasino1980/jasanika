<?php

/**
 * Single
 *
 * Template for single blog posts.
 */

get_header();
?>

<main id="main" class="site-main">

	<section class="single-post-page">
		<div class="single-post-page__container">

			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'template-parts/content/content-single' ); ?>
				<?php endwhile; ?>

				<nav class="single-post-navigation" aria-label="<?php esc_attr_e( 'Navigace mezi články', 'jasanika' ); ?>">
					<?php
					the_post_navigation(
						array(
							'prev_text' => '<span class="nav-subtitle">' . esc_html__( '← Předchozí', 'jasanika' ) . '</span><span class="nav-title">%title</span>',
							'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Následující →', 'jasanika' ) . '</span><span class="nav-title">%title</span>',
						)
					);
					?>
				</nav>

				<?php if ( comments_open() || get_comments_number() ) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>

			<?php else : ?>

				<p class="single-post-empty"><?php esc_html_e( 'Článek nebyl nalezen.', 'jasanika' ); ?></p>

			<?php endif; ?>

		</div>
	</section>

</main>

<?php
get_footer();
