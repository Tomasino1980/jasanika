<?php

/**
 * Search Results
 *
 * Template for displaying search results pages.
 */

get_header();
?>

<main id="main" class="site-main">

	<section class="search-page">
		<div class="search-page__container">

			<header class="search-header">
				<h1 class="search-header__title">
					<?php esc_html_e( 'Výsledky hledání', 'jasanika' ); ?>
				</h1>
				<p class="search-header__query">
					<?php
					printf(
						/* translators: %s: search query */
						esc_html__( 'Výsledky pro: "%s"', 'jasanika' ),
						esc_html( get_search_query() )
					);
					?>
				</p>
			</header>

			<?php if ( have_posts() ) : ?>

				<div class="search-grid">
					<?php while ( have_posts() ) : the_post(); ?>
						<?php get_template_part( 'template-parts/content/content-search' ); ?>
					<?php endwhile; ?>
				</div>

				<nav class="search-pagination" aria-label="<?php esc_attr_e( 'Stránkování', 'jasanika' ); ?>">
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

				<div class="search-empty">
					<p class="search-empty__message">
						<?php esc_html_e( 'Nebyly nalezeny žádné výsledky.', 'jasanika' ); ?>
					</p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-outline">
						<?php esc_html_e( 'Zpět na hlavní stránku', 'jasanika' ); ?>
					</a>
				</div>

			<?php endif; ?>

		</div>
	</section>

</main>

<?php
get_footer();
