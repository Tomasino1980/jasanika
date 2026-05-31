<?php

/**
 * Slider Helper Functions
 *
 * Provides functions to retrieve slides stored in WordPress options.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns all slides sorted by sort_order ascending.
 *
 * @return array
 */
function jasanika_get_slides(): array {
	$slides = get_option( 'jasanika_slides', array() );

	if ( ! is_array( $slides ) ) {
		return array();
	}

	usort(
		$slides,
		function ( array $a, array $b ): int {
			return (int) ( $a['sort_order'] ?? 0 ) - (int) ( $b['sort_order'] ?? 0 );
		}
	);

	return $slides;
}

/**
 * Returns only active slides sorted by sort_order ascending.
 *
 * @return array
 */
function jasanika_get_active_slides(): array {
	return array_values(
		array_filter(
			jasanika_get_slides(),
			function ( array $slide ): bool {
				return ! empty( $slide['active'] );
			}
		)
	);
}
