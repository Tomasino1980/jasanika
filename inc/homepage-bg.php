<?php

/**
 * Jasanika – Homepage Section Background Images
 *
 * Provides helper functions for generating per-section background image CSS
 * and outputting the generated styles into the front-end page head.
 *
 * Settings are stored flat inside jasanika_settings using keys:
 *   hb_{section_key}_bg_type
 *   hb_{section_key}_bg_image_id
 *   hb_{section_key}_bg_fit
 *   hb_{section_key}_bg_position
 *   hb_{section_key}_bg_repeat
 *   hb_{section_key}_bg_overlay_color
 *   hb_{section_key}_bg_overlay_opacity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Section CSS Class Registry
// ---------------------------------------------------------------------------

/**
 * Maps each Homepage Builder section key to its front-end CSS class name.
 *
 * @return array<string, string>
 */
function jasanika_section_bg_css_class_map(): array {
	return array(
		'hero_slider'        => 'hero-slider',
		'feature_blocks'     => 'feature-blocks',
		'product_categories' => 'categories',
		'latest_posts'       => 'latest-posts',
		'cta_section'        => 'cta-section',
		'testimonials'       => 'testimonials',
		'featured_products'  => 'featured-products',
		'newsletter'         => 'newsletter-section',
	);
}

// ---------------------------------------------------------------------------
// Config Reader
// ---------------------------------------------------------------------------

/**
 * Returns the resolved background configuration for a single section.
 *
 * @param string $key Section key (e.g. 'hero_slider').
 * @return array{ type: string, image_id: int, fit: string, position: string, repeat: bool, overlay_color: string, overlay_opacity: int }
 */
function jasanika_get_section_bg_config( string $key ): array {
	$settings = get_option( 'jasanika_settings', array() );
	$prefix   = 'hb_' . $key . '_bg_';

	return array(
		'type'            => $settings[ $prefix . 'type' ]            ?? 'color',
		'image_id'        => absint( $settings[ $prefix . 'image_id' ] ?? 0 ),
		'fit'             => $settings[ $prefix . 'fit' ]             ?? 'cover',
		'position'        => $settings[ $prefix . 'position' ]        ?? 'center',
		'repeat'          => ! empty( $settings[ $prefix . 'repeat' ] ),
		'overlay_color'   => $settings[ $prefix . 'overlay_color' ]   ?? '#000000',
		'overlay_opacity' => min( 100, max( 0, absint( $settings[ $prefix . 'overlay_opacity' ] ?? 50 ) ) ),
	);
}

// ---------------------------------------------------------------------------
// CSS Mappers
// ---------------------------------------------------------------------------

/**
 * Maps the Image Fit option to its CSS background-size value.
 *
 * @param string $fit Fit option key.
 * @return string CSS background-size value.
 */
function jasanika_bg_fit_to_css( string $fit ): string {
	$map = array(
		'cover'    => 'cover',
		'contain'  => 'contain',
		'stretch'  => '100% 100%',
		'original' => 'auto',
		'repeat'   => 'auto',
	);

	return $map[ $fit ] ?? 'cover';
}

/**
 * Maps the Image Position option to its CSS background-position value.
 *
 * @param string $position Position option key.
 * @return string CSS background-position value.
 */
function jasanika_bg_position_to_css( string $position ): string {
	$map = array(
		'center'       => 'center center',
		'top'          => 'center top',
		'bottom'       => 'center bottom',
		'left'         => 'left center',
		'right'        => 'right center',
		'top_left'     => 'left top',
		'top_right'    => 'right top',
		'bottom_left'  => 'left bottom',
		'bottom_right' => 'right bottom',
	);

	return $map[ $position ] ?? 'center center';
}

// ---------------------------------------------------------------------------
// CSS Generator
// ---------------------------------------------------------------------------

/**
 * Generates the background CSS block for a single section.
 *
 * Returns an empty string when no background image is configured or when
 * the background type is 'none' or 'color'.
 *
 * @param string $key       Section key (e.g. 'hero_slider').
 * @param string $css_class Front-end CSS class for the section element.
 * @return string CSS declarations or empty string.
 */
function jasanika_get_section_bg_css( string $key, string $css_class ): string {
	$cfg       = jasanika_get_section_bg_config( $key );
	$has_image = ( 'image' === $cfg['type'] || 'color_image' === $cfg['type'] );

	if ( ! $has_image ) {
		return '';
	}

	if ( ! $cfg['image_id'] ) {
		return '';
	}

	if ( ! wp_attachment_is_image( $cfg['image_id'] ) ) {
		return '';
	}

	$image_url = wp_get_attachment_image_url( $cfg['image_id'], 'full' );

	if ( ! $image_url ) {
		return '';
	}

	$size     = jasanika_bg_fit_to_css( $cfg['fit'] );
	$pos      = jasanika_bg_position_to_css( $cfg['position'] );
	$repeat   = ( 'repeat' === $cfg['fit'] || $cfg['repeat'] ) ? 'repeat' : 'no-repeat';
	$selector = '.' . $css_class;

	$css  = "{$selector} {\n";
	$css .= "\tposition: relative;\n";
	$css .= "\tbackground-image: url('" . esc_url( $image_url ) . "');\n";
	$css .= "\tbackground-size: {$size};\n";
	$css .= "\tbackground-position: {$pos};\n";
	$css .= "\tbackground-repeat: {$repeat};\n";
	$css .= "}\n";

	// Direct children must stack above the overlay pseudo-element.
	$css .= "{$selector} > * {\n";
	$css .= "\tposition: relative;\n";
	$css .= "\tz-index: 1;\n";
	$css .= "}\n";

	if ( 'color_image' === $cfg['type'] ) {
		$hex = $cfg['overlay_color'];

		if ( ! preg_match( '/^#[0-9a-fA-F]{6}$/', $hex ) ) {
			$hex = '#000000';
		}

		$r = hexdec( substr( $hex, 1, 2 ) );
		$g = hexdec( substr( $hex, 3, 2 ) );
		$b = hexdec( substr( $hex, 5, 2 ) );
		$a = round( $cfg['overlay_opacity'] / 100, 2 );

		$css .= "{$selector}::before {\n";
		$css .= "\tcontent: '';\n";
		$css .= "\tposition: absolute;\n";
		$css .= "\tinset: 0;\n";
		$css .= "\tbackground: rgba({$r}, {$g}, {$b}, {$a});\n";
		$css .= "\tz-index: 0;\n";
		$css .= "\tpointer-events: none;\n";
		$css .= "}\n";
	}

	return $css;
}

// ---------------------------------------------------------------------------
// Frontend Output
// ---------------------------------------------------------------------------

/**
 * Outputs all active section background styles as an inline <style> block
 * in the front-end <head>. Runs only on the front page.
 */
function jasanika_output_section_bg_styles(): void {
	if ( ! is_front_page() ) {
		return;
	}

	$class_map = jasanika_section_bg_css_class_map();
	$css       = '';

	foreach ( $class_map as $key => $css_class ) {
		$css .= jasanika_get_section_bg_css( $key, $css_class );
	}

	if ( '' === $css ) {
		return;
	}

	echo '<style id="jasanika-section-bg">' . "\n";
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS built from wp_get_attachment_image_url and trusted option values.
	echo $css;
	echo '</style>' . "\n";
}
add_action( 'wp_head', 'jasanika_output_section_bg_styles', 20 );
