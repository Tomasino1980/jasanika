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

	// WooCommerce support.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'jasanika_theme_support' );

/**
 * Register theme widget areas.
 */
function jasanika_register_sidebars(): void {
	register_sidebar(
		array(
			'name'          => __( 'Footer Widget Area', 'jasanika' ),
			'id'            => 'footer-widgets',
			'description'   => __( 'Widgets displayed in the footer area.', 'jasanika' ),
			'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="footer-widget__title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'jasanika_register_sidebars' );
