<?php

/**
 * Enqueue
 *
 * Registers and enqueues theme scripts and styles.
 * Assets will be implemented in M2 - Layout Foundation.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jasanika_enqueue_assets() {
	// Assets will be enqueued in M2 - Layout Foundation.
}
add_action( 'wp_enqueue_scripts', 'jasanika_enqueue_assets' );
