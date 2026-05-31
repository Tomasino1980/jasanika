<?php

/**
 * Cookie Consent
 *
 * Core helper functions for the Cookie Consent Manager.
 * Provides consent detection, settings retrieval and script-blocking hooks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Settings Helpers
// ---------------------------------------------------------------------------

/**
 * Returns the cookie consent settings with defaults applied.
 *
 * @return array<string, mixed>
 */
function jasanika_cookie_consent_get_settings(): array {
	$defaults = array(
		'banner_enabled'     => '1',
		'banner_title'       => __( 'Cookies & Privacy', 'jasanika' ),
		'banner_description' => __( 'We use cookies to improve your experience and analyze website usage.', 'jasanika' ),
		'privacy_policy_url' => '',
		'cookie_policy_url'  => '',
		'consent_expiration' => 365,
	);

	$saved = get_option( 'jasanika_cookie_consent_settings', array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, $defaults );
}

/**
 * Returns true if the cookie banner is enabled in settings.
 *
 * @return bool
 */
function jasanika_cookie_consent_is_banner_enabled(): bool {
	$settings = jasanika_cookie_consent_get_settings();
	return ! empty( $settings['banner_enabled'] );
}

// ---------------------------------------------------------------------------
// Consent Detection
// ---------------------------------------------------------------------------

/**
 * Returns true if the user has recorded any cookie consent decision.
 *
 * @return bool
 */
function jasanika_has_cookie_consent(): bool {
	return isset( $_COOKIE['jasanika_cookie_consent'] );
}

/**
 * Returns true if the user has consented to analytics cookies.
 *
 * @return bool
 */
function jasanika_has_analytics_consent(): bool {
	if ( ! jasanika_has_cookie_consent() ) {
		return false;
	}

	$consent = jasanika_cookie_consent_parse();
	return ! empty( $consent['analytics'] );
}

/**
 * Returns true if the user has consented to marketing cookies.
 *
 * @return bool
 */
function jasanika_has_marketing_consent(): bool {
	if ( ! jasanika_has_cookie_consent() ) {
		return false;
	}

	$consent = jasanika_cookie_consent_parse();
	return ! empty( $consent['marketing'] );
}

// ---------------------------------------------------------------------------
// Internal Helpers
// ---------------------------------------------------------------------------

/**
 * Parses the stored consent cookie and returns the decoded array.
 * Returns an empty array if the cookie is missing or malformed.
 *
 * @return array<string, mixed>
 */
function jasanika_cookie_consent_parse(): array {
	if ( ! isset( $_COOKIE['jasanika_cookie_consent'] ) ) {
		return array();
	}

	$raw    = sanitize_text_field( wp_unslash( $_COOKIE['jasanika_cookie_consent'] ) );
	$parsed = json_decode( $raw, true );

	if ( ! is_array( $parsed ) ) {
		return array();
	}

	return $parsed;
}

// ---------------------------------------------------------------------------
// Script Blocking Foundation
// ---------------------------------------------------------------------------

/**
 * Fires action hooks that future milestones can use to gate analytics and
 * marketing script loading behind consent checks.
 *
 * Usage in future milestones:
 *
 *   add_action( 'jasanika_enqueue_analytics_scripts', function() {
 *       if ( ! jasanika_has_analytics_consent() ) { return; }
 *       // enqueue analytics scripts here
 *   } );
 *
 *   add_action( 'jasanika_enqueue_marketing_scripts', function() {
 *       if ( ! jasanika_has_marketing_consent() ) { return; }
 *       // enqueue marketing scripts here
 *   } );
 */
function jasanika_cookie_consent_script_hooks(): void {
	do_action( 'jasanika_enqueue_analytics_scripts' );
	do_action( 'jasanika_enqueue_marketing_scripts' );
}
add_action( 'wp_enqueue_scripts', 'jasanika_cookie_consent_script_hooks', 20 );
