<?php

/**
 * Jasanika – Theme Options
 *
 * Helper functions for accessing theme settings stored via the Settings API.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a single theme option value.
 *
 * @param string $key     Option key within the jasanika_settings array.
 * @param mixed  $default Default value when the option is not set or empty.
 * @return mixed
 */
function jasanika_get_option( string $key, mixed $default = '' ): mixed {
	$options = get_option( 'jasanika_settings', array() );

	if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
		return $options[ $key ];
	}

	return $default;
}

// ---------------------------------------------------------------------------
// Branding
// ---------------------------------------------------------------------------

function jasanika_get_company_name(): string {
	return (string) jasanika_get_option( 'company_name', get_bloginfo( 'name' ) );
}

function jasanika_get_company_slogan(): string {
	return (string) jasanika_get_option( 'company_slogan', get_bloginfo( 'description' ) );
}

function jasanika_get_logo_url(): string {
	return (string) jasanika_get_option( 'logo_url', '' );
}

function jasanika_get_favicon_url(): string {
	return (string) jasanika_get_option( 'favicon_url', '' );
}

// ---------------------------------------------------------------------------
// Contact Information
// ---------------------------------------------------------------------------

function jasanika_get_phone(): string {
	return (string) jasanika_get_option( 'phone', '' );
}

function jasanika_get_email(): string {
	return (string) jasanika_get_option( 'email', '' );
}

function jasanika_get_address_street(): string {
	return (string) jasanika_get_option( 'address_street', '' );
}

function jasanika_get_address_city(): string {
	return (string) jasanika_get_option( 'address_city', '' );
}

function jasanika_get_address_zip(): string {
	return (string) jasanika_get_option( 'address_zip', '' );
}

// ---------------------------------------------------------------------------
// Social Networks
// ---------------------------------------------------------------------------

function jasanika_get_facebook_url(): string {
	return (string) jasanika_get_option( 'facebook_url', '' );
}

function jasanika_get_instagram_url(): string {
	return (string) jasanika_get_option( 'instagram_url', '' );
}

function jasanika_get_youtube_url(): string {
	return (string) jasanika_get_option( 'youtube_url', '' );
}

function jasanika_get_linkedin_url(): string {
	return (string) jasanika_get_option( 'linkedin_url', '' );
}

// ---------------------------------------------------------------------------
// Footer
// ---------------------------------------------------------------------------

function jasanika_get_copyright_text(): string {
	$default = sprintf( '© %s %s', gmdate( 'Y' ), get_bloginfo( 'name' ) );
	return (string) jasanika_get_option( 'copyright_text', $default );
}

function jasanika_get_footer_note(): string {
	return (string) jasanika_get_option( 'footer_note', '' );
}
