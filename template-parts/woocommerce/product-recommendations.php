<?php

/**
 * Product Recommendations
 *
 * Shared component for related products and cross-sell sections.
 * Reuses the existing product-card template part.
 *
 * @package Jasanika
 *
 * @var string $args['title']    Section heading.
 * @var int[]  $args['products'] Array of product IDs to display.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title       = isset( $args['title'] ) ? $args['title'] : '';
$product_ids = isset( $args['products'] ) ? array_map( 'absint', (array) $args['products'] ) : array();

if ( empty( $product_ids ) ) {
	return;
}

$products = array_filter( array_map( 'wc_get_product', $product_ids ) );

if ( empty( $products ) ) {
	return;
}

?>

<section class="product-recommendations">

	<?php if ( $title ) : ?>
		<h2 class="product-recommendations__title"><?php echo esc_html( $title ); ?></h2>
	<?php endif; ?>

	<ul class="product-recommendations__grid">

		<?php
		foreach ( $products as $product ) :
			// Set up the global product for the product-card template part.
			$GLOBALS['product'] = $product;
			get_template_part( 'template-parts/woocommerce/product-card' );
		endforeach;
		?>

	</ul><!-- .product-recommendations__grid -->

</section><!-- .product-recommendations -->
