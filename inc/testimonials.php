<?php

/**
 * Testimonials Helper Functions
 *
 * Provides functions to retrieve testimonials stored in WordPress options.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns all testimonials sorted by sort_order ascending.
 *
 * @return array
 */
function jasanika_get_testimonials(): array {
	$testimonials = get_option( 'jasanika_testimonials', array() );

	if ( ! is_array( $testimonials ) ) {
		return array();
	}

	usort(
		$testimonials,
		function ( array $a, array $b ): int {
			return (int) ( $a['sort_order'] ?? 0 ) - (int) ( $b['sort_order'] ?? 0 );
		}
	);

	return $testimonials;
}

/**
 * Returns only active testimonials sorted by sort_order ascending.
 *
 * @return array
 */
function jasanika_get_active_testimonials(): array {
	return array_values(
		array_filter(
			jasanika_get_testimonials(),
			function ( array $testimonial ): bool {
				return ! empty( $testimonial['active'] );
			}
		)
	);
}
