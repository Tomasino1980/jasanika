<?php

/**
 * Product Card
 *
 * Reusable product card template part for the WooCommerce archive.
 *
 * @global WC_Product $product Current WooCommerce product object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}
?>

<li <?php wc_product_class( 'product-card', $product ); ?>>

	<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="product-card__link" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">

		<div class="product-card__image">
			<?php
			if ( $product->get_image_id() ) {
				echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'product-card__img' ) ) );
			} else {
				echo wc_placeholder_img( 'woocommerce_thumbnail', array( 'class' => 'product-card__img' ) );
			}
			?>
		</div><!-- .product-card__image -->

		<div class="product-card__body">

			<h2 class="product-card__title woocommerce-loop-product__title">
				<?php echo esc_html( $product->get_name() ); ?>
			</h2>

			<div class="product-card__price">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div><!-- .product-card__price -->

		</div><!-- .product-card__body -->

	</a>

	<div class="product-card__footer">
		<?php woocommerce_template_loop_add_to_cart(); ?>
	</div><!-- .product-card__footer -->

</li>
