<?php

/**
 * WooCommerce Integration
 *
 * Integrates WooCommerce with the Jasanika theme.
 * Handles theme wrappers, stylesheet enqueueing and compatibility.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'WC' ) ) {
	return;
}

/**
 * Remove default WooCommerce content wrappers.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

/**
 * Output opening Jasanika wrapper before WooCommerce main content.
 */
function jasanika_woocommerce_wrapper_before() {
	?>
	<main id="primary" class="site-main">
		<div class="container jasanika-woocommerce-wrap">
	<?php
}
add_action( 'woocommerce_before_main_content', 'jasanika_woocommerce_wrapper_before', 10 );

/**
 * Output closing Jasanika wrapper after WooCommerce main content.
 */
function jasanika_woocommerce_wrapper_after() {
	?>
		</div><!-- .container.jasanika-woocommerce-wrap -->
	</main><!-- #primary -->
	<?php
}
add_action( 'woocommerce_after_main_content', 'jasanika_woocommerce_wrapper_after', 10 );

/**
 * Enqueue WooCommerce stylesheet only on WooCommerce pages.
 */
function jasanika_enqueue_woocommerce_styles() {
	if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
		return;
	}

	$ver = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'jasanika-woocommerce',
		get_template_directory_uri() . '/assets/css/components/woocommerce.css',
		array( 'jasanika-variables', 'jasanika-buttons' ),
		$ver
	);

	// Product archive stylesheet – shop and product category/tag pages only.
	if ( is_shop() || is_product_category() || is_product_tag() ) {
		wp_enqueue_style(
			'jasanika-product-archive',
			get_template_directory_uri() . '/assets/css/components/product-archive.css',
			array( 'jasanika-woocommerce' ),
			$ver
		);
	}
}
add_action( 'wp_enqueue_scripts', 'jasanika_enqueue_woocommerce_styles' );
