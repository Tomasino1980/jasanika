<?php

/**
 * Archive
 *
 * Template for category, tag and date archive pages.
 */

get_header();
?>

<main id="main" class="site-main">

	<section class="archive-page">
		<div class="archive-page__container">

			<header class="archive-header">
				<h1 class="archive-header__title">
					<?php the_archive_title(); ?>
				</h1>
				<?php
				$archive_description = get_the_archive_description();
				if ( $archive_description ) :
				?>
					<div class="archive-header__description">
						<?php echo wp_kses_post( $archive_description ); ?>
					</div>
				<?php endif; ?>
			</header>

			<?php if ( have_posts() ) : ?>

				<div class="archive-grid">
					<?php while ( have_posts() ) : the_post(); ?>
						<?php get_template_part( 'template-parts/content/content-card' ); ?>
					<?php endwhile; ?>
				</div>

				<nav class="archive-pagination" aria-label="<?php esc_attr_e( 'Stránkování', 'jasanika' ); ?>">
					<?php
					the_posts_pagination(
						array(
							'mid_size'  => 2,
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
						)
					);
					?>
				</nav>

			<?php else : ?>

				<p class="archive-empty"><?php esc_html_e( 'Nebyly nalezeny žádné články.', 'jasanika' ); ?></p>

			<?php endif; ?>

		</div>
	</section>

</main>

<?php
get_footer();
