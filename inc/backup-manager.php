<?php

/**
 * Jasanika – Backup Manager
 *
 * Core helper functions for exporting and importing Jasanika settings.
 * Used by the Backup Manager admin page.
 *
 * Functions:
 *   jasanika_export_settings()
 *   jasanika_import_settings()
 *   jasanika_get_backup_info()
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JASANIKA_BACKUP_HISTORY_OPTION', 'jasanika_backup_history' );
define( 'JASANIKA_BACKUP_HISTORY_LIMIT', 20 );

// ---------------------------------------------------------------------------
// Public API
// ---------------------------------------------------------------------------

/**
 * Export Jasanika settings as a structured array ready for JSON encoding.
 *
 * @param array<string> $sections Section keys to include. Empty = all sections.
 * @return array<string, mixed>
 */
function jasanika_export_settings( array $sections = array() ): array {
	$theme_version = (string) wp_get_theme()->get( 'Version' );

	$all = array(
		'theme_settings'      => jasanika_backup_extract_theme_settings(),
		'homepage_builder'    => jasanika_backup_extract_homepage_builder(),
		'slider_manager'      => (array) get_option( 'jasanika_slides', array() ),
		'testimonials'        => (array) get_option( 'jasanika_testimonials', array() ),
		'newsletter_settings' => (array) get_option( 'jasanika_newsletter_subscribers', array() ),
		'seo_settings'        => (array) get_option( 'jasanika_seo_settings', array() ),
		'cookie_manager'      => (array) get_option( 'jasanika_cookie_consent_settings', array() ),
	);

	if ( ! empty( $sections ) ) {
		$data = array();
		foreach ( $sections as $section ) {
			if ( array_key_exists( $section, $all ) ) {
				$data[ $section ] = $all[ $section ];
			}
		}
	} else {
		$data = $all;
	}

	return array(
		'jasanika_version'  => $theme_version,
		'wordpress_version' => (string) get_bloginfo( 'version' ),
		'export_date'       => gmdate( 'Y-m-d\TH:i:s\Z' ),
		'export_type'       => empty( $sections ) ? 'full' : 'settings',
		'data'              => $data,
	);
}

/**
 * Import Jasanika settings from a decoded backup array.
 *
 * Validates structure, sanitizes all values and updates WordPress options.
 * Records the import in the backup history.
 *
 * @param array<string, mixed> $backup Decoded JSON backup data.
 * @return bool|WP_Error True on success, WP_Error on validation failure.
 */
function jasanika_import_settings( array $backup ): bool|WP_Error {
	// Validate required fields.
	if ( empty( $backup['jasanika_version'] ) || ! isset( $backup['data'] ) || ! is_array( $backup['data'] ) ) {
		return new WP_Error(
			'invalid_backup',
			__( 'Invalid backup file: missing required fields (jasanika_version or data).', 'jasanika' )
		);
	}

	$data = $backup['data'];

	// Theme Settings – merge into jasanika_settings, preserving existing hb_ keys.
	if ( isset( $data['theme_settings'] ) && is_array( $data['theme_settings'] ) ) {
		$current    = (array) get_option( 'jasanika_settings', array() );
		$hb_current = jasanika_backup_filter_hb_keys( $current );
		$merged     = array_merge( jasanika_backup_sanitize_flat( $data['theme_settings'] ), $hb_current );
		update_option( 'jasanika_settings', $merged );
	}

	// Homepage Builder – only apply hb_ keys into jasanika_settings.
	if ( isset( $data['homepage_builder'] ) && is_array( $data['homepage_builder'] ) ) {
		$current   = (array) get_option( 'jasanika_settings', array() );
		$hb_import = array_filter(
			jasanika_backup_sanitize_flat( $data['homepage_builder'] ),
			static fn( string $key ) => str_starts_with( $key, 'hb_' ),
			ARRAY_FILTER_USE_KEY
		);
		update_option( 'jasanika_settings', array_merge( $current, $hb_import ) );
	}

	// Slider Manager.
	if ( isset( $data['slider_manager'] ) && is_array( $data['slider_manager'] ) ) {
		update_option( 'jasanika_slides', jasanika_backup_sanitize_deep( $data['slider_manager'] ) );
	}

	// Testimonials.
	if ( isset( $data['testimonials'] ) && is_array( $data['testimonials'] ) ) {
		update_option( 'jasanika_testimonials', jasanika_backup_sanitize_deep( $data['testimonials'] ) );
	}

	// Newsletter Settings (subscriber list).
	if ( isset( $data['newsletter_settings'] ) && is_array( $data['newsletter_settings'] ) ) {
		update_option( 'jasanika_newsletter_subscribers', jasanika_backup_sanitize_deep( $data['newsletter_settings'] ) );
	}

	// SEO Settings.
	if ( isset( $data['seo_settings'] ) && is_array( $data['seo_settings'] ) ) {
		update_option( 'jasanika_seo_settings', jasanika_backup_sanitize_flat( $data['seo_settings'] ) );
	}

	// Cookie Manager.
	if ( isset( $data['cookie_manager'] ) && is_array( $data['cookie_manager'] ) ) {
		update_option( 'jasanika_cookie_consent_settings', jasanika_backup_sanitize_flat( $data['cookie_manager'] ) );
	}

	// Record to backup history.
	jasanika_backup_history_add( array(
		'date'    => gmdate( 'Y-m-d H:i:s' ),
		'type'    => 'import',
		'version' => sanitize_text_field( (string) $backup['jasanika_version'] ),
	) );

	return true;
}

/**
 * Get stored backup history metadata (up to last 20 entries).
 *
 * @return array<int, array{ date: string, type: string, version: string }>
 */
function jasanika_get_backup_info(): array {
	return (array) get_option( JASANIKA_BACKUP_HISTORY_OPTION, array() );
}

// ---------------------------------------------------------------------------
// Internal helpers
// ---------------------------------------------------------------------------

/**
 * Extract non-hb_ keys from jasanika_settings (theme settings only).
 *
 * @return array<string, mixed>
 */
function jasanika_backup_extract_theme_settings(): array {
	$all = (array) get_option( 'jasanika_settings', array() );
	return array_filter(
		$all,
		static fn( string $key ) => ! str_starts_with( $key, 'hb_' ),
		ARRAY_FILTER_USE_KEY
	);
}

/**
 * Extract hb_ keys from jasanika_settings (homepage builder config only).
 *
 * @return array<string, mixed>
 */
function jasanika_backup_extract_homepage_builder(): array {
	return jasanika_backup_filter_hb_keys( (array) get_option( 'jasanika_settings', array() ) );
}

/**
 * Filter an array to only keep keys prefixed with 'hb_'.
 *
 * @param array<string, mixed> $input
 * @return array<string, mixed>
 */
function jasanika_backup_filter_hb_keys( array $input ): array {
	return array_filter(
		$input,
		static fn( string $key ) => str_starts_with( $key, 'hb_' ),
		ARRAY_FILTER_USE_KEY
	);
}

/**
 * Prepend an entry to the backup history, capping at JASANIKA_BACKUP_HISTORY_LIMIT.
 *
 * @param array{ date: string, type: string, version: string } $entry
 */
function jasanika_backup_history_add( array $entry ): void {
	$history = jasanika_get_backup_info();
	array_unshift( $history, $entry );
	$history = array_slice( $history, 0, JASANIKA_BACKUP_HISTORY_LIMIT );
	update_option( JASANIKA_BACKUP_HISTORY_OPTION, $history, false );
}

/**
 * Sanitize a flat associative array (one level deep).
 *
 * String values are passed through sanitize_textarea_field to preserve
 * multi-line content while stripping disallowed HTML.
 *
 * @param array<string, mixed> $input
 * @return array<string, mixed>
 */
function jasanika_backup_sanitize_flat( array $input ): array {
	$out = array();
	foreach ( $input as $key => $value ) {
		$key = sanitize_key( (string) $key );
		if ( '' === $key ) {
			continue;
		}
		if ( is_array( $value ) ) {
			$out[ $key ] = jasanika_backup_sanitize_deep( $value );
		} elseif ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
			$out[ $key ] = $value;
		} else {
			$out[ $key ] = sanitize_textarea_field( wp_unslash( (string) $value ) );
		}
	}
	return $out;
}

/**
 * Recursively sanitize a nested array.
 *
 * @param array<mixed> $input
 * @return array<mixed>
 */
function jasanika_backup_sanitize_deep( array $input ): array {
	$out = array();
	foreach ( $input as $key => $value ) {
		if ( is_array( $value ) ) {
			$out[ $key ] = jasanika_backup_sanitize_deep( $value );
		} elseif ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
			$out[ $key ] = $value;
		} else {
			$out[ $key ] = sanitize_textarea_field( wp_unslash( (string) $value ) );
		}
	}
	return $out;
}
