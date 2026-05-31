<?php

/**
 * Latest Posts
 *
 * Displays the latest published blog posts on the homepage.
 * Section title and post count are managed from Jasanika → Theme Settings → Homepage Content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$latest_posts_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => jasanika_get_latest_posts_count(),
		'ignore_sticky_posts' => true,
	)
);
?>

<section class="latest-posts">
	<div class="latest-posts__container">

		<h2 class="latest-posts__heading"><?php echo esc_html( jasanika_get_latest_posts_section_title() ); ?></h2>

		<?php if ( $latest_posts_query->have_posts() ) : ?>

			<div class="latest-posts__grid">

				<?php while ( $latest_posts_query->have_posts() ) : $latest_posts_query->the_post(); ?>

					<article class="latest-posts__card">

						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="latest-posts__image-link" tabindex="-1" aria-hidden="true">
								<?php the_post_thumbnail( 'medium', array( 'class' => 'latest-posts__image' ) ); ?>
							</a>
						<?php endif; ?>

						<div class="latest-posts__body">

							<time class="latest-posts__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
								<?php echo esc_html( get_the_date() ); ?>
							</time>

							<h3 class="latest-posts__title">
								<a href="<?php the_permalink(); ?>" class="latest-posts__title-link">
									<?php the_title(); ?>
								</a>
							</h3>

							<p class="latest-posts__excerpt">
								<?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?>
							</p>

							<a href="<?php the_permalink(); ?>" class="btn btn-outline latest-posts__read-more">
								<?php esc_html_e( 'Read More', 'jasanika' ); ?>
							</a>

						</div>

					</article>

				<?php endwhile; ?>

			</div>

		<?php else : ?>

			<p class="latest-posts__empty"><?php esc_html_e( 'No articles available yet.', 'jasanika' ); ?></p>

		<?php endif; ?>

		<?php wp_reset_postdata(); ?>

	</div>
</section>
