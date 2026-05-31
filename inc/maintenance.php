<?php

/**
 * Jasanika – Maintenance Mode
 *
 * Core logic for the Maintenance Mode system.
 * Intercepts front-end requests when maintenance mode is active,
 * returning a 503 Service Unavailable response with a branded maintenance page.
 * Administrators (and optionally Editors) bypass the maintenance page normally.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JASANIKA_MAINTENANCE_OPTION', 'jasanika_maintenance' );

// ---------------------------------------------------------------------------
// Settings Helpers
// ---------------------------------------------------------------------------

/**
 * Return all maintenance settings with defaults applied.
 *
 * @return array<string, mixed>
 */
function jasanika_maintenance_get_settings(): array {
	$defaults = array(
		'enabled'            => false,
		'title'              => __( "We'll be back soon!", 'jasanika' ),
		'description'        => __( 'We are currently performing scheduled maintenance. We will be back online shortly.', 'jasanika' ),
		'contact_email'      => '',
		'contact_phone'      => '',
		'launch_date'        => '',
		'show_countdown'     => false,
		'facebook_url'       => '',
		'instagram_url'      => '',
		'linkedin_url'       => '',
		'youtube_url'        => '',
		'newsletter_enabled' => false,
		'allow_editors'      => false,
	);

	$saved = get_option( JASANIKA_MAINTENANCE_OPTION, array() );

	return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
}

/**
 * Check whether maintenance mode is currently active.
 *
 * @return bool
 */
function jasanika_maintenance_is_active(): bool {
	$settings = jasanika_maintenance_get_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Check whether the current user may bypass the maintenance page.
 *
 * @return bool
 */
function jasanika_maintenance_user_has_access(): bool {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( current_user_can( 'manage_options' ) ) {
		return true;
	}

	$settings = jasanika_maintenance_get_settings();

	if ( ! empty( $settings['allow_editors'] ) && current_user_can( 'edit_others_posts' ) ) {
		return true;
	}

	return false;
}

// ---------------------------------------------------------------------------
// Front-end Intercept
// ---------------------------------------------------------------------------

add_action( 'template_redirect', 'jasanika_maintenance_intercept', 1 );

/**
 * Intercept front-end requests and serve the maintenance page when active.
 * REST API requests are excluded to avoid breaking the WP REST API.
 */
function jasanika_maintenance_intercept(): void {
	if ( ! jasanika_maintenance_is_active() ) {
		return;
	}

	// Skip REST API requests.
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	if ( jasanika_maintenance_user_has_access() ) {
		return;
	}

	jasanika_maintenance_render();
}

/**
 * Render the maintenance page, set HTTP headers, and exit.
 */
function jasanika_maintenance_render(): void {
	$settings = jasanika_maintenance_get_settings();

	// 503 Service Unavailable.
	status_header( 503 );

	// Calculate Retry-After: time until launch date or a 1-hour default.
	$retry_after = 3600;
	if ( ! empty( $settings['launch_date'] ) ) {
		$launch_ts = strtotime( $settings['launch_date'] );
		if ( $launch_ts && $launch_ts > time() ) {
			$retry_after = $launch_ts - time();
		}
	}

	header( 'Retry-After: ' . $retry_after );

	// Prevent caching and search engine indexing.
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
	header( 'X-Robots-Tag: noindex, nofollow' );

	include get_template_directory() . '/template-parts/system/maintenance.php';

	exit;
}
