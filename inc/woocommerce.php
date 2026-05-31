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
 * Check whether the current page is a WooCommerce product search.
 *
 * @return bool
 */
function jasanika_is_product_search() {
	return is_search() && 'product' === get_query_var( 'post_type' );
}

/**
 * Load the custom product search template when the query is a product search.
 *
 * @param string $template Current template path.
 * @return string
 */
function jasanika_product_search_template( $template ) {
	if ( jasanika_is_product_search() ) {
		$custom = locate_template( 'woocommerce/product-search.php' );
		if ( $custom ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'jasanika_product_search_template' );

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
	if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() && ! jasanika_is_product_search() ) {
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

	// Product categories stylesheet – product category pages only.
	if ( is_product_category() ) {
		wp_enqueue_style(
			'jasanika-product-categories',
			get_template_directory_uri() . '/assets/css/components/product-categories.css',
			array( 'jasanika-product-archive' ),
			$ver
		);
	}

	// Single product stylesheet – product pages only.
	if ( is_product() ) {
		wp_enqueue_style(
			'jasanika-single-product',
			get_template_directory_uri() . '/assets/css/components/single-product.css',
			array( 'jasanika-woocommerce' ),
			$ver
		);
	}

	// Cart stylesheet – cart page only.
	if ( is_cart() ) {
		wp_enqueue_style(
			'jasanika-cart',
			get_template_directory_uri() . '/assets/css/components/cart.css',
			array( 'jasanika-woocommerce', 'jasanika-buttons' ),
			$ver
		);
	}

	// Checkout stylesheet – checkout page only.
	if ( is_checkout() ) {
		wp_enqueue_style(
			'jasanika-checkout',
			get_template_directory_uri() . '/assets/css/components/checkout.css',
			array( 'jasanika-woocommerce', 'jasanika-buttons' ),
			$ver
		);
	}

	// My Account stylesheet – account pages only.
	if ( is_account_page() ) {
		wp_enqueue_style(
			'jasanika-my-account',
			get_template_directory_uri() . '/assets/css/components/my-account.css',
			array( 'jasanika-woocommerce' ),
			$ver
		);
	}

	// Product search stylesheet – product search pages only.
	if ( jasanika_is_product_search() ) {
		wp_enqueue_style(
			'jasanika-product-search',
			get_template_directory_uri() . '/assets/css/components/product-search.css',
			array( 'jasanika-woocommerce', 'jasanika-product-archive' ),
			$ver
		);
	}
}
add_action( 'wp_enqueue_scripts', 'jasanika_enqueue_woocommerce_styles' );
