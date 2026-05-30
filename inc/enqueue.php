<?php

/**
 * Enqueue
 *
 * Registers and enqueues theme scripts and styles.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jasanika_enqueue_assets() {

	$ver = wp_get_theme()->get( 'Version' );
	$uri = get_template_directory_uri();

	// Google Fonts – Playfair Display (headings) + Inter (body).
	wp_enqueue_style(
		'jasanika-fonts',
		'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap',
		array(),
		null
	);

	// Base layer.
	wp_enqueue_style(
		'jasanika-variables',
		$uri . '/assets/css/base/variables.css',
		array( 'jasanika-fonts' ),
		$ver
	);

	wp_enqueue_style(
		'jasanika-reset',
		$uri . '/assets/css/base/reset.css',
		array( 'jasanika-variables' ),
		$ver
	);

	wp_enqueue_style(
		'jasanika-typography',
		$uri . '/assets/css/base/typography.css',
		array( 'jasanika-reset' ),
		$ver
	);

	// Layout layer.
	wp_enqueue_style(
		'jasanika-containers',
		$uri . '/assets/css/layout/containers.css',
		array( 'jasanika-typography' ),
		$ver
	);

	wp_enqueue_style(
		'jasanika-grid',
		$uri . '/assets/css/layout/grid.css',
		array( 'jasanika-containers' ),
		$ver
	);

	wp_enqueue_style(
		'jasanika-header',
		$uri . '/assets/css/layout/header.css',
		array( 'jasanika-containers' ),
		$ver
	);

	wp_enqueue_style(
		'jasanika-footer',
		$uri . '/assets/css/layout/footer.css',
		array( 'jasanika-containers' ),
		$ver
	);

	// Component layer.
	wp_enqueue_style(
		'jasanika-buttons',
		$uri . '/assets/css/components/buttons.css',
		array( 'jasanika-variables' ),
		$ver
	);

	wp_enqueue_style(
		'jasanika-cards',
		$uri . '/assets/css/components/cards.css',
		array( 'jasanika-variables' ),
		$ver
	);

	wp_enqueue_style(
		'jasanika-navigation',
		$uri . '/assets/css/components/navigation.css',
		array( 'jasanika-variables' ),
		$ver
	);

	wp_enqueue_style(
		'jasanika-forms',
		$uri . '/assets/css/components/forms.css',
		array( 'jasanika-variables' ),
		$ver
	);

	// Pages layer.
	wp_enqueue_style(
		'jasanika-homepage',
		$uri . '/assets/css/pages/homepage.css',
		array( 'jasanika-containers' ),
		$ver
	);

	// Hero slider – front page only.
	if ( is_front_page() ) {
		wp_enqueue_style(
			'jasanika-hero-slider',
			$uri . '/assets/css/components/hero-slider.css',
			array( 'jasanika-variables', 'jasanika-buttons' ),
			$ver
		);

		wp_enqueue_style(
			'jasanika-feature-blocks',
			$uri . '/assets/css/components/feature-blocks.css',
			array( 'jasanika-variables', 'jasanika-buttons' ),
			$ver
		);

		wp_enqueue_style(
			'jasanika-latest-posts',
			$uri . '/assets/css/components/latest-posts.css',
			array( 'jasanika-variables', 'jasanika-buttons' ),
			$ver
		);

		wp_enqueue_style(
			'jasanika-categories',
			$uri . '/assets/css/components/categories.css',
			array( 'jasanika-variables' ),
			$ver
		);
	}
}
add_action( 'wp_enqueue_scripts', 'jasanika_enqueue_assets' );
