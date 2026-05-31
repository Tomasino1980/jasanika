<?php

/**
 * Tag Archive
 *
 * Template for tag archive pages.
 */

get_header();

$tag        = get_queried_object();
$post_count = isset( $tag->count ) ? (int) $tag->count : 0;
?>

<main id="main" class="site-main">

	<section class="tag-page">
		<div class="tag-page__container">

			<header class="tag-header">
				<h1 class="tag-header__title">
					<?php single_tag_title(); ?>
				</h1>

				<?php $description = tag_description(); ?>
				<?php if ( $description ) : ?>
					<div class="tag-header__description">
						<?php echo wp_kses_post( $description ); ?>
					</div>
				<?php endif; ?>

				<div class="tag-stats">
					<span class="tag-stats__count">
						<?php
						printf(
							esc_html(
								/* translators: %s: post count */
								_n( '%s článek', '%s článků', $post_count, 'jasanika' )
							),
							number_format_i18n( $post_count )
						);
						?>
					</span>
				</div>
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

				<div class="tag-empty">
					<p class="tag-empty__message">
						<?php esc_html_e( 'Tento štítek zatím neobsahuje žádné články.', 'jasanika' ); ?>
					</p>
					<a href="<?php echo esc_url( home_url( '/blog' ) ); ?>" class="btn btn-primary">
						<?php esc_html_e( 'Zpět na blog', 'jasanika' ); ?>
					</a>
				</div>

			<?php endif; ?>

		</div>
	</section>

</main>

<?php
get_footer();
