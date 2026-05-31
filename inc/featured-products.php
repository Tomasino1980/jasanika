<?php

/**
 * Jasanika – Featured Products Helper Functions
 *
 * Query helpers for the Featured Products homepage section.
 * Requires WooCommerce to be active.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Theme Options – Featured Products
// ---------------------------------------------------------------------------

function jasanika_get_featured_products_title(): string {
	return (string) jasanika_get_option( 'featured_products_title', __( 'Featured Products', 'jasanika' ) );
}

function jasanika_get_featured_products_description(): string {
	return (string) jasanika_get_option( 'featured_products_description', __( 'Explore our latest handcrafted creations.', 'jasanika' ) );
}

function jasanika_get_featured_products_count(): int {
	$count = (int) jasanika_get_option( 'featured_products_count', 4 );
	return min( max( $count, 1 ), 12 );
}

// ---------------------------------------------------------------------------
// Product Query
// ---------------------------------------------------------------------------

/**
 * Returns an array of WC product objects for the Featured Products section.
 *
 * Featured products (marked via product_visibility taxonomy) are returned
 * first. When there are fewer featured products than requested, the remainder
 * is filled with the latest published products, avoiding duplicates.
 *
 * @return WC_Product[]
 */
function jasanika_get_featured_products(): array {
	if ( ! function_exists( 'WC' ) ) {
		return array();
	}

	$count    = jasanika_get_featured_products_count();
	$products = array();
	$used_ids = array();

	// Step 1 – Featured products via product_visibility taxonomy.
	$featured_query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $count,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'featured',
				),
			),
		)
	);

	foreach ( $featured_query->posts as $id ) {
		$product = wc_get_product( (int) $id );
		if ( $product instanceof WC_Product ) {
			$products[] = $product;
			$used_ids[] = (int) $id;
		}
	}

	// Step 2 – Fill remaining slots with latest published products.
	$remaining = $count - count( $products );

	if ( $remaining > 0 ) {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $remaining,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( ! empty( $used_ids ) ) {
			$args['post__not_in'] = $used_ids;
		}

		$fallback_query = new WP_Query( $args );

		foreach ( $fallback_query->posts as $id ) {
			$product = wc_get_product( (int) $id );
			if ( $product instanceof WC_Product ) {
				$products[] = $product;
			}
		}
	}

	return $products;
}

/**
 * Returns true when WooCommerce is active and at least one product exists.
 *
 * @return bool
 */
function jasanika_has_featured_products(): bool {
	if ( ! function_exists( 'WC' ) ) {
		return false;
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
		)
	);

	return $query->found_posts > 0;
}
