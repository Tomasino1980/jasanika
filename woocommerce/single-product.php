<?php

/**
 * WooCommerce Single Product
 *
 * Custom single product template for the Jasanika theme.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

?>

<?php do_action( 'woocommerce_before_main_content' ); ?>

<?php
while ( have_posts() ) :
	the_post();

	if ( post_password_required() ) {
		echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		get_footer();
		return;
	}

	do_action( 'woocommerce_before_single_product' );

	global $product;
?>

<div class="single-product__breadcrumb">
	<?php woocommerce_breadcrumb(); ?>
</div><!-- .single-product__breadcrumb -->

<article id="product-<?php the_ID(); ?>" <?php wc_product_class( 'single-product', $product ); ?>>

	<div class="single-product__columns">

		<div class="single-product__gallery">
			<?php do_action( 'woocommerce_before_single_product_summary' ); ?>
		</div><!-- .single-product__gallery -->

		<div class="single-product__info">
			<?php do_action( 'woocommerce_single_product_summary' ); ?>
		</div><!-- .single-product__info -->

	</div><!-- .single-product__columns -->

	<div class="single-product__after-summary">
		<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
	</div><!-- .single-product__after-summary -->

</article><!-- #product-<?php the_ID(); ?> -->

<?php
	do_action( 'woocommerce_after_single_product' );

endwhile;
?>

<?php do_action( 'woocommerce_after_main_content' ); ?>

<?php get_footer(); ?>
