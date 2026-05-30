<?php

/**
 * 404
 *
 * Template for 404 not found page.
 */

get_header();
?>

<main id="main" class="site-main">

	<section class="error-404">
		<div class="error-404__container">

			<div class="error-404__content">

				<p class="error-404__code" aria-hidden="true">404</p>

				<h1 class="error-404__heading">
					<?php esc_html_e( 'Stránka nebyla nalezena', 'jasanika' ); ?>
				</h1>

				<p class="error-404__description">
					<?php esc_html_e( 'Omlouváme se, ale požadovaná stránka neexistuje nebo byla přesunuta.', 'jasanika' ); ?>
				</p>

				<div class="error-404__actions">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-lg">
						<?php esc_html_e( 'Zpět na hlavní stránku', 'jasanika' ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '/blog' ) ); ?>" class="btn btn-outline btn-lg">
						<?php esc_html_e( 'Přejít na blog', 'jasanika' ); ?>
					</a>
				</div>

				<div class="error-404__search">
					<?php get_search_form(); ?>
				</div>

			</div>

		</div>
	</section>

</main>

<?php
get_footer();
