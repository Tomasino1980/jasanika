<?php

/**
 * Cleanup
 *
 * Removes unnecessary WordPress default output.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jasanika_cleanup() {
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'after_setup_theme', 'jasanika_cleanup' );
