<?php

/**
 * WooCommerce Product Category
 *
 * Template for WooCommerce product category (taxonomy-product_cat) pages.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term = get_queried_object();

?>

<?php do_action( 'woocommerce_before_main_content' ); ?>

	<header class="product-cat__header">

		<h1 class="product-cat__title">
			<?php echo esc_html( single_term_title( '', false ) ); ?>
		</h1>

		<?php if ( $term instanceof WP_Term && $term->description ) : ?>
			<div class="product-cat__description">
				<?php echo wp_kses_post( wc_format_content( $term->description ) ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $term instanceof WP_Term ) : ?>
			<p class="product-cat__count">
				<?php
				printf(
					/* translators: %s: product count */
					wp_kses(
						_n( '%s produkt', '%s produktů', $term->count, 'jasanika' ),
						array( 'span' => array( 'class' => array() ) )
					),
					'<span class="product-cat__count-number">' . esc_html( number_format_i18n( $term->count ) ) . '</span>'
				);
				?>
			</p>
		<?php endif; ?>

	</header><!-- .product-cat__header -->

	<?php
	// Display child categories when available.
	$child_categories = array();
	if ( $term instanceof WP_Term ) {
		$child_categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => $term->term_id,
				'hide_empty' => false,
			)
		);
	}

	if ( ! empty( $child_categories ) && ! is_wp_error( $child_categories ) ) :
	?>
		<nav class="product-cat__children" aria-label="<?php esc_attr_e( 'Podkategorie', 'jasanika' ); ?>">
			<ul class="product-cat__children-grid">
				<?php foreach ( $child_categories as $child ) : ?>
					<li class="product-cat__child-card">
						<a href="<?php echo esc_url( get_term_link( $child ) ); ?>" class="product-cat__child-link">
							<span class="product-cat__child-name"><?php echo esc_html( $child->name ); ?></span>
							<span class="product-cat__child-count">
								<?php
								printf(
									/* translators: %s: product count */
									esc_html( _n( '%s produkt', '%s produktů', $child->count, 'jasanika' ) ),
									esc_html( number_format_i18n( $child->count ) )
								);
								?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav><!-- .product-cat__children -->
	<?php endif; ?>

	<div class="product-archive__layout">

		<aside class="product-archive__sidebar" aria-label="<?php esc_attr_e( 'Filtry produktů', 'jasanika' ); ?>">
			<?php get_template_part( 'template-parts/woocommerce/product-filters' ); ?>
		</aside><!-- .product-archive__sidebar -->

		<div class="product-archive__main">

			<?php do_action( 'woocommerce_before_shop_loop' ); ?>

			<?php if ( woocommerce_product_loop() ) : ?>

				<ul class="product-archive__grid products">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/woocommerce/product-card' );
					endwhile;
					?>
				</ul><!-- .product-archive__grid -->

				<?php do_action( 'woocommerce_after_shop_loop' ); ?>

				<div class="product-archive__pagination">
					<?php woocommerce_pagination(); ?>
				</div><!-- .product-archive__pagination -->

			<?php else : ?>

				<div class="product-cat__empty">
					<p class="product-cat__empty-text">
						<?php esc_html_e( 'Tato kategorie zatím neobsahuje žádné produkty.', 'jasanika' ); ?>
					</p>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="product-cat__empty-btn">
						<?php esc_html_e( 'Zpět do obchodu', 'jasanika' ); ?>
					</a>
				</div><!-- .product-cat__empty -->

			<?php endif; ?>

		</div><!-- .product-archive__main -->

	</div><!-- .product-archive__layout -->

<?php do_action( 'woocommerce_after_main_content' ); ?>

<?php
get_footer();
