<?php

/**
 * Menus
 *
 * Registers navigation menus.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jasanika_register_menus() {
	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'jasanika' ),
			'footer'  => esc_html__( 'Footer Menu', 'jasanika' ),
		)
	);
}
add_action( 'after_setup_theme', 'jasanika_register_menus' );
