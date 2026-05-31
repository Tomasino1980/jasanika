<?php

/**
 * Author Archive
 *
 * Template for author archive pages.
 */

get_header();

$author     = get_queried_object();
$author_id  = isset( $author->ID ) ? (int) $author->ID : 0;
$post_count = count_user_posts( $author_id, 'post', true );
?>

<main id="main" class="site-main">

	<section class="author-page">
		<div class="author-page__container">

			<header class="author-header">
				<div class="author-profile">

					<div class="author-profile__avatar">
						<?php
						echo get_avatar(
							$author_id,
							120,
							'',
							esc_attr( $author->display_name ),
							array( 'class' => 'author-profile__avatar-img' )
						);
						?>
					</div>

					<div class="author-profile__info">

						<h1 class="author-profile__name">
							<?php echo esc_html( $author->display_name ); ?>
						</h1>

						<?php $bio = get_the_author_meta( 'description', $author_id ); ?>
						<?php if ( $bio ) : ?>
							<p class="author-profile__bio">
								<?php echo esc_html( $bio ); ?>
							</p>
						<?php endif; ?>

						<div class="author-stats">
							<span class="author-stats__count">
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

					</div>
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

				<div class="author-empty">
					<p class="author-empty__message">
						<?php esc_html_e( 'Tento autor zatím nepublikoval žádné články.', 'jasanika' ); ?>
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
