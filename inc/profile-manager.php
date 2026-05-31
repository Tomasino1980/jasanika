<?php

/**
 * Jasanika – Profile Manager
 *
 * Core helper functions for the Profile System.
 * Allows saving, loading, activating, duplicating and deleting
 * complete Jasanika configuration profiles.
 *
 * Public API:
 *   jasanika_get_profiles()
 *   jasanika_get_active_profile()
 *   jasanika_activate_profile( string $profile_id )
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JASANIKA_PROFILES_OPTION', 'jasanika_profiles' );
define( 'JASANIKA_ACTIVE_PROFILE_OPTION', 'jasanika_active_profile_id' );

// Initialize default profile on fresh theme activation.
add_action( 'after_switch_theme', 'jasanika_profiles_maybe_init' );
add_action( 'admin_init',         'jasanika_profiles_maybe_init' );

// ---------------------------------------------------------------------------
// Public API
// ---------------------------------------------------------------------------

/**
 * Get all stored profiles.
 *
 * @return array<string, array<string, mixed>>
 */
function jasanika_get_profiles(): array {
	return (array) get_option( JASANIKA_PROFILES_OPTION, array() );
}

/**
 * Get the currently active profile.
 *
 * @return array<string, mixed>|null Active profile data or null if none set.
 */
function jasanika_get_active_profile(): ?array {
	$active_id = (string) get_option( JASANIKA_ACTIVE_PROFILE_OPTION, '' );

	if ( '' === $active_id ) {
		return null;
	}

	$profiles = jasanika_get_profiles();
	return $profiles[ $active_id ] ?? null;
}

/**
 * Activate a profile: apply all its stored settings to live WordPress options.
 *
 * @param string $profile_id Profile ID to activate.
 * @return bool True on success, false if profile not found.
 */
function jasanika_activate_profile( string $profile_id ): bool {
	$profiles = jasanika_get_profiles();

	if ( ! isset( $profiles[ $profile_id ] ) ) {
		return false;
	}

	jasanika_profile_apply_settings( $profiles[ $profile_id ]['settings'] ?? array() );
	update_option( JASANIKA_ACTIVE_PROFILE_OPTION, $profile_id );

	return true;
}

// ---------------------------------------------------------------------------
// Profile CRUD
// ---------------------------------------------------------------------------

/**
 * Create a new profile from the current live settings.
 *
 * @param string $name        Profile name.
 * @param string $description Profile description.
 * @return string New profile ID.
 */
function jasanika_profile_create( string $name, string $description = '' ): string {
	$profiles   = jasanika_get_profiles();
	$profile_id = jasanika_profile_generate_id();

	$profiles[ $profile_id ] = array(
		'id'          => $profile_id,
		'name'        => sanitize_text_field( $name ),
		'description' => sanitize_textarea_field( $description ),
		'version'     => (string) wp_get_theme()->get( 'Version' ),
		'created_at'  => gmdate( 'Y-m-d H:i:s' ),
		'settings'    => jasanika_profile_capture_settings(),
	);

	update_option( JASANIKA_PROFILES_OPTION, $profiles );

	return $profile_id;
}

/**
 * Duplicate an existing profile.
 *
 * @param string $profile_id Source profile ID.
 * @return string|null New profile ID or null if source not found.
 */
function jasanika_profile_duplicate( string $profile_id ): ?string {
	$profiles = jasanika_get_profiles();

	if ( ! isset( $profiles[ $profile_id ] ) ) {
		return null;
	}

	$source  = $profiles[ $profile_id ];
	$new_id  = jasanika_profile_generate_id();

	$profiles[ $new_id ] = array(
		'id'          => $new_id,
		'name'        => sanitize_text_field( $source['name'] ) . ' (Copy)',
		'description' => sanitize_textarea_field( $source['description'] ?? '' ),
		'version'     => (string) wp_get_theme()->get( 'Version' ),
		'created_at'  => gmdate( 'Y-m-d H:i:s' ),
		'settings'    => $source['settings'] ?? array(),
	);

	update_option( JASANIKA_PROFILES_OPTION, $profiles );

	return $new_id;
}

/**
 * Delete a profile by ID.
 *
 * If the deleted profile is active, the active profile option is cleared.
 *
 * @param string $profile_id Profile ID to delete.
 * @return bool True on success, false if not found.
 */
function jasanika_profile_delete( string $profile_id ): bool {
	$profiles = jasanika_get_profiles();

	if ( ! isset( $profiles[ $profile_id ] ) ) {
		return false;
	}

	unset( $profiles[ $profile_id ] );
	update_option( JASANIKA_PROFILES_OPTION, $profiles );

	$active_id = (string) get_option( JASANIKA_ACTIVE_PROFILE_OPTION, '' );
	if ( $active_id === $profile_id ) {
		delete_option( JASANIKA_ACTIVE_PROFILE_OPTION );
	}

	return true;
}

/**
 * Save a profile from validated import data (does not activate it).
 *
 * @param array<string, mixed> $data Validated import data.
 * @return string New profile ID.
 */
function jasanika_profile_save_from_import( array $data ): string {
	$profiles   = jasanika_get_profiles();
	$profile_id = jasanika_profile_generate_id();

	$profiles[ $profile_id ] = array(
		'id'          => $profile_id,
		'name'        => sanitize_text_field( $data['name'] ?? 'Imported Profile' ),
		'description' => sanitize_textarea_field( $data['description'] ?? '' ),
		'version'     => sanitize_text_field( $data['version'] ?? '' ),
		'created_at'  => sanitize_text_field( $data['created_at'] ?? gmdate( 'Y-m-d H:i:s' ) ),
		'settings'    => jasanika_profile_sanitize_settings( $data['settings'] ?? array() ),
	);

	update_option( JASANIKA_PROFILES_OPTION, $profiles );

	return $profile_id;
}

/**
 * Build the export array for a profile (ready for JSON encoding).
 *
 * @param string $profile_id Profile ID.
 * @return array<string, mixed>|null Export data or null if not found.
 */
function jasanika_profile_build_export( string $profile_id ): ?array {
	$profiles = jasanika_get_profiles();

	if ( ! isset( $profiles[ $profile_id ] ) ) {
		return null;
	}

	$profile = $profiles[ $profile_id ];

	return array(
		'jasanika_profile_export' => true,
		'name'                    => $profile['name'],
		'description'             => $profile['description'] ?? '',
		'version'                 => $profile['version'],
		'created_at'              => $profile['created_at'],
		'export_date'             => gmdate( 'Y-m-d\TH:i:s\Z' ),
		'settings'                => $profile['settings'] ?? array(),
	);
}

// ---------------------------------------------------------------------------
// Validation
// ---------------------------------------------------------------------------

/**
 * Validate imported profile JSON data.
 *
 * @param array<string, mixed> $data Decoded JSON.
 * @return true|WP_Error True on valid, WP_Error on failure.
 */
function jasanika_profile_validate_import( array $data ): bool|WP_Error {
	if ( empty( $data['jasanika_profile_export'] ) || true !== $data['jasanika_profile_export'] ) {
		return new WP_Error(
			'invalid_format',
			__( 'Invalid file: not a Jasanika profile export.', 'jasanika' )
		);
	}

	if ( empty( $data['name'] ) ) {
		return new WP_Error(
			'missing_name',
			__( 'Invalid profile file: missing profile name.', 'jasanika' )
		);
	}

	if ( empty( $data['version'] ) ) {
		return new WP_Error(
			'missing_version',
			__( 'Invalid profile file: missing version field.', 'jasanika' )
		);
	}

	if ( ! isset( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
		return new WP_Error(
			'missing_settings',
			__( 'Invalid profile file: missing settings data.', 'jasanika' )
		);
	}

	return true;
}

// ---------------------------------------------------------------------------
// Initialization
// ---------------------------------------------------------------------------

/**
 * Create the "Default Jasanika" profile if no profiles exist yet.
 */
function jasanika_profiles_maybe_init(): void {
	$profiles = jasanika_get_profiles();

	if ( ! empty( $profiles ) ) {
		return;
	}

	$profile_id = jasanika_profile_generate_id();

	$profiles[ $profile_id ] = array(
		'id'          => $profile_id,
		'name'        => 'Default Jasanika',
		'description' => 'Default Jasanika configuration.',
		'version'     => (string) wp_get_theme()->get( 'Version' ),
		'created_at'  => gmdate( 'Y-m-d H:i:s' ),
		'settings'    => jasanika_profile_capture_settings(),
	);

	update_option( JASANIKA_PROFILES_OPTION, $profiles );
	update_option( JASANIKA_ACTIVE_PROFILE_OPTION, $profile_id );
}

// ---------------------------------------------------------------------------
// Settings Capture & Apply
// ---------------------------------------------------------------------------

/**
 * Capture a snapshot of all current live settings.
 *
 * @return array<string, mixed>
 */
function jasanika_profile_capture_settings(): array {
	$all_jasanika_settings = (array) get_option( 'jasanika_settings', array() );

	$theme_settings = array_filter(
		$all_jasanika_settings,
		static fn( string $key ) => ! str_starts_with( $key, 'hb_' ),
		ARRAY_FILTER_USE_KEY
	);

	$hb_settings = array_filter(
		$all_jasanika_settings,
		static fn( string $key ) => str_starts_with( $key, 'hb_' ),
		ARRAY_FILTER_USE_KEY
	);

	return array(
		'theme_settings'       => $theme_settings,
		'homepage_builder'     => $hb_settings,
		'slider_configuration' => (array) get_option( 'jasanika_slides', array() ),
		'testimonials'         => (array) get_option( 'jasanika_testimonials', array() ),
		'newsletter_settings'  => (array) get_option( 'jasanika_newsletter_subscribers', array() ),
		'seo_settings'         => (array) get_option( 'jasanika_seo_settings', array() ),
		'cookie_settings'      => (array) get_option( 'jasanika_cookie_consent_settings', array() ),
		'footer_builder'       => (array) get_option( 'jasanika_footer_settings', array() ),
	);
}

/**
 * Apply all settings from a profile to live WordPress options.
 *
 * @param array<string, mixed> $settings Profile settings array.
 */
function jasanika_profile_apply_settings( array $settings ): void {
	$current = (array) get_option( 'jasanika_settings', array() );

	// Theme settings: non-hb_ keys.
	if ( isset( $settings['theme_settings'] ) && is_array( $settings['theme_settings'] ) ) {
		$hb_current = array_filter(
			$current,
			static fn( string $key ) => str_starts_with( $key, 'hb_' ),
			ARRAY_FILTER_USE_KEY
		);
		$merged = array_merge( $hb_current, jasanika_profile_sanitize_flat( $settings['theme_settings'] ) );
		update_option( 'jasanika_settings', $merged );
		$current = $merged;
	}

	// Homepage Builder: hb_ keys.
	if ( isset( $settings['homepage_builder'] ) && is_array( $settings['homepage_builder'] ) ) {
		$hb_import = array_filter(
			jasanika_profile_sanitize_flat( $settings['homepage_builder'] ),
			static fn( string $key ) => str_starts_with( $key, 'hb_' ),
			ARRAY_FILTER_USE_KEY
		);
		update_option( 'jasanika_settings', array_merge( $current, $hb_import ) );
	}

	if ( isset( $settings['slider_configuration'] ) && is_array( $settings['slider_configuration'] ) ) {
		update_option( 'jasanika_slides', jasanika_profile_sanitize_deep( $settings['slider_configuration'] ) );
	}

	if ( isset( $settings['testimonials'] ) && is_array( $settings['testimonials'] ) ) {
		update_option( 'jasanika_testimonials', jasanika_profile_sanitize_deep( $settings['testimonials'] ) );
	}

	if ( isset( $settings['newsletter_settings'] ) && is_array( $settings['newsletter_settings'] ) ) {
		update_option( 'jasanika_newsletter_subscribers', jasanika_profile_sanitize_deep( $settings['newsletter_settings'] ) );
	}

	if ( isset( $settings['seo_settings'] ) && is_array( $settings['seo_settings'] ) ) {
		update_option( 'jasanika_seo_settings', jasanika_profile_sanitize_flat( $settings['seo_settings'] ) );
	}

	if ( isset( $settings['cookie_settings'] ) && is_array( $settings['cookie_settings'] ) ) {
		update_option( 'jasanika_cookie_consent_settings', jasanika_profile_sanitize_flat( $settings['cookie_settings'] ) );
	}

	if ( isset( $settings['footer_builder'] ) && is_array( $settings['footer_builder'] ) ) {
		update_option( 'jasanika_footer_settings', jasanika_profile_sanitize_deep( $settings['footer_builder'] ) );
	}
}

// ---------------------------------------------------------------------------
// Internal Helpers
// ---------------------------------------------------------------------------

/**
 * Generate a unique profile ID.
 *
 * @return string
 */
function jasanika_profile_generate_id(): string {
	return 'profile_' . substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 12 );
}

/**
 * Count total number of settings entries across all sections.
 *
 * @param array<string, mixed> $settings
 * @return int
 */
function jasanika_profile_count_settings( array $settings ): int {
	$count = 0;
	foreach ( $settings as $section ) {
		if ( is_array( $section ) ) {
			$count += count( $section );
		}
	}
	return $count;
}

/**
 * Get list of enabled homepage sections from a profile's homepage_builder data.
 *
 * @param array<string, mixed> $settings Profile settings array.
 * @return array<string>
 */
function jasanika_profile_get_enabled_sections( array $settings ): array {
	$enabled  = array();
	$hb       = $settings['homepage_builder'] ?? array();
	$registry = function_exists( 'jasanika_homepage_sections_registry' )
		? jasanika_homepage_sections_registry()
		: array();

	foreach ( $registry as $section_key => $section_meta ) {
		$option_key = 'hb_' . $section_key . '_enabled';
		$is_enabled = isset( $hb[ $option_key ] ) ? (bool) $hb[ $option_key ] : ( $section_meta['default_enabled'] ?? false );
		if ( $is_enabled ) {
			$enabled[] = $section_meta['label'] ?? $section_key;
		}
	}

	return $enabled;
}

/**
 * Sanitize a flat (non-nested) settings array.
 *
 * @param array<string, mixed> $input
 * @return array<string, mixed>
 */
function jasanika_profile_sanitize_flat( array $input ): array {
	$output = array();
	foreach ( $input as $key => $value ) {
		$k = sanitize_key( (string) $key );
		if ( is_array( $value ) ) {
			$output[ $k ] = jasanika_profile_sanitize_deep( $value );
		} elseif ( is_bool( $value ) ) {
			$output[ $k ] = $value;
		} elseif ( is_int( $value ) || is_float( $value ) ) {
			$output[ $k ] = $value;
		} else {
			$output[ $k ] = sanitize_text_field( (string) $value );
		}
	}
	return $output;
}

/**
 * Recursively sanitize a settings array.
 *
 * @param array<mixed> $input
 * @return array<mixed>
 */
function jasanika_profile_sanitize_deep( array $input ): array {
	$output = array();
	foreach ( $input as $key => $value ) {
		if ( is_array( $value ) ) {
			$output[ $key ] = jasanika_profile_sanitize_deep( $value );
		} elseif ( is_bool( $value ) ) {
			$output[ $key ] = $value;
		} elseif ( is_int( $value ) || is_float( $value ) ) {
			$output[ $key ] = $value;
		} else {
			$output[ $key ] = sanitize_text_field( (string) $value );
		}
	}
	return $output;
}

/**
 * Sanitize a complete profile settings array, keeping only known section keys.
 *
 * @param array<string, mixed> $settings
 * @return array<string, mixed>
 */
function jasanika_profile_sanitize_settings( array $settings ): array {
	$allowed = array(
		'theme_settings',
		'homepage_builder',
		'slider_configuration',
		'testimonials',
		'newsletter_settings',
		'seo_settings',
		'cookie_settings',
		'footer_builder',
	);

	$output = array();
	foreach ( $allowed as $key ) {
		if ( isset( $settings[ $key ] ) && is_array( $settings[ $key ] ) ) {
			$output[ $key ] = jasanika_profile_sanitize_deep( $settings[ $key ] );
		}
	}
	return $output;
}
