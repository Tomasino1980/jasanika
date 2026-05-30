<?php

/**
 * Menus
 *
 * Registers navigation menus.
 * Navigation will be implemented in M4 - Menu System.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jasanika_register_menus() {
	// Navigation menus will be implemented in M4 - Menu System.
}
add_action( 'after_setup_theme', 'jasanika_register_menus' );
