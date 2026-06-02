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

	wp_enqueue_style(
		'jasanika-footer-builder',
		$uri . '/assets/css/components/footer-builder.css',
		array( 'jasanika-footer' ),
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

	// Logo placement – always loaded.
	wp_enqueue_style(
		'jasanika-logo-placement',
		$uri . '/assets/css/components/logo-placement.css',
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

	// Single post CSS – single posts only.
	if ( is_single() ) {
		wp_enqueue_style(
			'jasanika-single-post',
			$uri . '/assets/css/components/single-post.css',
			array( 'jasanika-variables', 'jasanika-buttons' ),
			$ver
		);

		wp_enqueue_style(
			'jasanika-comments',
			$uri . '/assets/css/components/comments.css',
			array( 'jasanika-variables', 'jasanika-buttons' ),
			$ver
		);
	}

	// Search results CSS – search pages only.
	if ( is_search() ) {
		wp_enqueue_style(
			'jasanika-search-results',
			$uri . '/assets/css/components/search-results.css',
			array( 'jasanika-variables', 'jasanika-buttons' ),
			$ver
		);
	}

	// 404 CSS – 404 page only.
	if ( is_404() ) {
		wp_enqueue_style(
			'jasanika-404',
			$uri . '/assets/css/components/404.css',
			array( 'jasanika-variables', 'jasanika-buttons' ),
			$ver
		);
	}

	// Contact page CSS – contact page only.
	if ( is_page_template( 'page-contact.php' ) ) {
		wp_enqueue_style(
			'jasanika-contact-page',
			$uri . '/assets/css/components/contact-page.css',
			array( 'jasanika-variables', 'jasanika-buttons' ),
			$ver
		);
	}

	// Archive CSS – archive, category and tag pages only.
	if ( is_archive() || is_category() || is_tag() ) {
		wp_enqueue_style(
			'jasanika-archive',
			$uri . '/assets/css/components/archive.css',
			array( 'jasanika-variables', 'jasanika-buttons' ),
			$ver
		);
	}

	// Category archive CSS – category pages only.
	if ( is_category() ) {
		wp_enqueue_style(
			'jasanika-category-archive',
			$uri . '/assets/css/components/category-archive.css',
			array( 'jasanika-archive' ),
			$ver
		);
	}

	// Tag archive CSS – tag pages only.
	if ( is_tag() ) {
		wp_enqueue_style(
			'jasanika-tag-archive',
			$uri . '/assets/css/components/tag-archive.css',
			array( 'jasanika-archive' ),
			$ver
		);
	}

	// Author archive CSS – author pages only.
	if ( is_author() ) {
		wp_enqueue_style(
			'jasanika-author-archive',
			$uri . '/assets/css/components/author-archive.css',
			array( 'jasanika-archive', 'jasanika-buttons' ),
			$ver
		);
	}

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

		wp_enqueue_style(
			'jasanika-cta-section',
			$uri . '/assets/css/components/cta-section.css',
			array( 'jasanika-variables', 'jasanika-buttons' ),
			$ver
		);

		wp_enqueue_style(
			'jasanika-testimonials',
			$uri . '/assets/css/components/testimonials.css',
			array( 'jasanika-variables' ),
			$ver
		);

		wp_enqueue_style(
			'jasanika-featured-products',
			$uri . '/assets/css/components/featured-products.css',
			array( 'jasanika-variables', 'jasanika-buttons' ),
			$ver
		);

		// Newsletter – load only when the section is enabled.
		$sections = jasanika_get_homepage_sections();
		if ( ! empty( $sections['newsletter']['enabled'] ) ) {
			wp_enqueue_style(
				'jasanika-newsletter',
				$uri . '/assets/css/components/newsletter.css',
				array( 'jasanika-variables', 'jasanika-buttons' ),
				$ver
			);

			wp_enqueue_script(
				'jasanika-newsletter',
				$uri . '/assets/js/newsletter.js',
				array(),
				$ver,
				true
			);

			wp_localize_script(
				'jasanika-newsletter',
				'jasanikaNL',
				array(
					'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
					'messages' => array(
						'email_required'   => __( 'Please enter your email address.', 'jasanika' ),
						'consent_required' => __( 'Please accept the privacy policy to subscribe.', 'jasanika' ),
						'server_error'     => __( 'An error occurred. Please try again later.', 'jasanika' ),
					),
				)
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'jasanika_enqueue_assets' );

// ---------------------------------------------------------------------------
// Cookie Consent Assets
// ---------------------------------------------------------------------------

/**
 * Enqueue cookie consent banner CSS and JS when the banner is enabled.
 * Assets are loaded only when jasanika_cookie_consent_is_banner_enabled() is true,
 * keeping the front-end free from unnecessary scripts.
 */
function jasanika_enqueue_cookie_consent_assets(): void {
	if ( ! jasanika_cookie_consent_is_banner_enabled() ) {
		return;
	}

	$ver = wp_get_theme()->get( 'Version' );
	$uri = get_template_directory_uri();

	wp_enqueue_style(
		'jasanika-cookie-banner',
		$uri . '/assets/css/components/cookie-banner.css',
		array( 'jasanika-variables' ),
		$ver
	);

	wp_enqueue_script(
		'jasanika-cookie-consent',
		$uri . '/assets/js/cookie-consent.js',
		array(),
		$ver,
		true
	);

	$settings = jasanika_cookie_consent_get_settings();

	wp_localize_script(
		'jasanika-cookie-consent',
		'jasanikaCookieConsent',
		array(
			'expiration' => (int) $settings['consent_expiration'],
			'version'    => '1',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'jasanika_enqueue_cookie_consent_assets' );

// ---------------------------------------------------------------------------
// Brand CSS Variables
// ---------------------------------------------------------------------------

/**
 * Inject active preset CSS variables on the front-end.
 */
function jasanika_enqueue_brand_css_variables(): void {
	if ( ! function_exists( 'jasanika_theme_presets_get_css_variables' ) || ! function_exists( 'jasanika_theme_presets_build_root_css' ) ) {
		return;
	}

	$variables = jasanika_theme_presets_get_css_variables();
	$vars      = jasanika_theme_presets_build_root_css( $variables );

	if ( '' === $vars ) {
		return;
	}

	wp_add_inline_style( 'jasanika-variables', $vars );
}
add_action( 'wp_enqueue_scripts', 'jasanika_enqueue_brand_css_variables', 20 );

// ---------------------------------------------------------------------------
// Logo Placement CSS Variables
// ---------------------------------------------------------------------------

/**
 * Inject logo placement CSS custom properties on the front-end.
 * Outputs a :root block with --js-logo-width, --js-logo-mobile-width, etc.
 */
function jasanika_enqueue_logo_css_variables(): void {
	if ( ! function_exists( 'jasanika_logo_get_css_variables' ) ) {
		return;
	}

	$vars = jasanika_logo_get_css_variables();

	if ( '' === $vars ) {
		return;
	}

	wp_add_inline_style( 'jasanika-logo-placement', $vars );
}
add_action( 'wp_enqueue_scripts', 'jasanika_enqueue_logo_css_variables', 20 );

// ---------------------------------------------------------------------------
// Favicon
// ---------------------------------------------------------------------------

/**
 * Output the custom favicon link tag into the site <head>.
 */
function jasanika_output_favicon(): void {
	$favicon_url = jasanika_get_favicon_url();

	if ( ! $favicon_url ) {
		return;
	}

	echo '<link rel="icon" href="' . esc_url( $favicon_url ) . '">' . "\n";
}
add_action( 'wp_head', 'jasanika_output_favicon', 1 );
