<?php

/**
 * WooCommerce Product Search
 *
 * Template for WooCommerce product search results.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

?>

<?php do_action( 'woocommerce_before_main_content' ); ?>

	<header class="product-search__header">
		<h1 class="product-search__title">
			<?php esc_html_e( 'Výsledky hledání produktů', 'jasanika' ); ?>
		</h1>
		<p class="product-search__query">
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Hledaný výraz: "%s"', 'jasanika' ),
				esc_html( get_search_query() )
			);
			?>
		</p>
	</header><!-- .product-search__header -->

	<div class="product-search__form-wrap">
		<?php get_product_search_form(); ?>
	</div><!-- .product-search__form-wrap -->

	<?php if ( have_posts() ) : ?>

		<ul class="product-archive__grid products">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/woocommerce/product-search-card' );
			endwhile;
			?>
		</ul><!-- .product-archive__grid -->

		<div class="product-archive__pagination">
			<?php woocommerce_pagination(); ?>
		</div><!-- .product-archive__pagination -->

	<?php else : ?>

		<div class="product-search__empty">
			<p class="product-search__empty-message">
				<?php esc_html_e( 'Nebyly nalezeny žádné produkty.', 'jasanika' ); ?>
			</p>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary">
				<?php esc_html_e( 'Zpět do obchodu', 'jasanika' ); ?>
			</a>
		</div><!-- .product-search__empty -->

	<?php endif; ?>

<?php do_action( 'woocommerce_after_main_content' ); ?>

<?php
get_footer();
