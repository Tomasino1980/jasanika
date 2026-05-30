<?php

/**
 * Theme Support
 *
 * Registers WordPress theme support features.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jasanika_theme_support() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		)
	);
}
add_action( 'after_setup_theme', 'jasanika_theme_support' );
