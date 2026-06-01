<?php

/**
 * Jasanika – Theme Presets
 *
 * Defines built-in presets, custom preset persistence and helper APIs
 * for active-preset runtime CSS variables.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return built-in presets bundled with the theme.
 *
 * @return array<string, array<string, mixed>>
 */
function jasanika_theme_presets_builtin(): array {
	return array(
		'jasanika-dark' => array(
			'id'       => 'jasanika-dark',
			'name'     => __( 'Jasanika Dark', 'jasanika' ),
			'builtin'  => true,
			'config'   => array(
				'primary_color'    => '#b78acb',
				'secondary_color'  => '#24212b',
				'accent_color'     => '#f1c95d',
				'background_color' => '#1b1a1f',
				'text_color'       => '#f5f2f7',
				'button_style'     => 'solid',
			),
		),
		'jasanika-purple' => array(
			'id'       => 'jasanika-purple',
			'name'     => __( 'Jasanika Purple', 'jasanika' ),
			'builtin'  => true,
			'config'   => array(
				'primary_color'    => '#c79cda',
				'secondary_color'  => '#24212b',
				'accent_color'     => '#f1c95d',
				'background_color' => '#1b1a1f',
				'text_color'       => '#f5f2f7',
				'button_style'     => 'solid',
			),
		),
		'jasanika-midnight' => array(
			'id'       => 'jasanika-midnight',
			'name'     => __( 'Jasanika Midnight', 'jasanika' ),
			'builtin'  => true,
			'config'   => array(
				'primary_color'    => '#b9b1c4',
				'secondary_color'  => '#1b1a1f',
				'accent_color'     => '#f1c95d',
				'background_color' => '#24212b',
				'text_color'       => '#f5f2f7',
				'button_style'     => 'ghost',
			),
		),
		'jasanika-elegant' => array(
			'id'       => 'jasanika-elegant',
			'name'     => __( 'Jasanika Elegant', 'jasanika' ),
			'builtin'  => true,
			'config'   => array(
				'primary_color'    => '#b08a67',
				'secondary_color'  => '#24212b',
				'accent_color'     => '#f1c95d',
				'background_color' => '#1b1a1f',
				'text_color'       => '#f5f2f7',
				'button_style'     => 'outline',
			),
		),
		'jasanika-minimal' => array(
			'id'       => 'jasanika-minimal',
			'name'     => __( 'Jasanika Minimal', 'jasanika' ),
			'builtin'  => true,
			'config'   => array(
				'primary_color'    => '#b9b1c4',
				'secondary_color'  => '#24212b',
				'accent_color'     => '#b08a67',
				'background_color' => '#1b1a1f',
				'text_color'       => '#f5f2f7',
				'button_style'     => 'outline',
			),
		),
	);
}

/**
 * Return preset schema version.
 */
function jasanika_theme_presets_version(): string {
	return '1.0';
}

/**
 * Return allowed button styles.
 *
 * @return string[]
 */
function jasanika_theme_presets_button_styles(): array {
	return array( 'solid', 'outline', 'ghost' );
}

/**
 * Get default preset config.
 *
 * @return array<string, string>
 */
function jasanika_theme_presets_default_config(): array {
	$builtin = jasanika_theme_presets_builtin();
	return $builtin['jasanika-dark']['config'];
}

/**
 * Sanitize and normalize a single preset config.
 *
 * @param mixed $config Raw preset config.
 * @return array<string, string>
 */
function jasanika_theme_presets_sanitize_config( mixed $config ): array {
	$defaults = jasanika_theme_presets_default_config();
	$config   = is_array( $config ) ? $config : array();

	$primary   = sanitize_hex_color( (string) ( $config['primary_color'] ?? '' ) );
	$secondary = sanitize_hex_color( (string) ( $config['secondary_color'] ?? '' ) );
	$accent    = sanitize_hex_color( (string) ( $config['accent_color'] ?? '' ) );
	$bg        = sanitize_hex_color( (string) ( $config['background_color'] ?? '' ) );
	$text      = sanitize_hex_color( (string) ( $config['text_color'] ?? '' ) );

	$button_style = sanitize_key( (string) ( $config['button_style'] ?? '' ) );
	if ( ! in_array( $button_style, jasanika_theme_presets_button_styles(), true ) ) {
		$button_style = $defaults['button_style'];
	}

	return array(
		'primary_color'    => $primary ?: $defaults['primary_color'],
		'secondary_color'  => $secondary ?: $defaults['secondary_color'],
		'accent_color'     => $accent ?: $defaults['accent_color'],
		'background_color' => $bg ?: $defaults['background_color'],
		'text_color'       => $text ?: $defaults['text_color'],
		'button_style'     => $button_style,
	);
}

/**
 * Get custom presets.
 *
 * @return array<string, array<string, mixed>>
 */
function jasanika_theme_presets_get_custom(): array {
	$stored = get_option( 'jasanika_theme_presets_custom', array() );

	if ( ! is_array( $stored ) ) {
		return array();
	}

	$custom = array();
	foreach ( $stored as $id => $preset ) {
		if ( ! is_string( $id ) || ! is_array( $preset ) ) {
			continue;
		}

		$custom_id = sanitize_key( $id );
		if ( '' === $custom_id ) {
			continue;
		}

		$custom[ $custom_id ] = array(
			'id'      => $custom_id,
			'name'    => sanitize_text_field( (string) ( $preset['name'] ?? '' ) ),
			'builtin' => false,
			'config'  => jasanika_theme_presets_sanitize_config( $preset['config'] ?? array() ),
		);
	}

	return $custom;
}

/**
 * Persist custom presets.
 *
 * @param array<string, array<string, mixed>> $custom Presets.
 */
function jasanika_theme_presets_save_custom( array $custom ): void {
	update_option( 'jasanika_theme_presets_custom', $custom );
}

/**
 * Get all available presets.
 *
 * @return array<string, array<string, mixed>>
 */
function jasanika_theme_presets_get_all(): array {
	return array_merge( jasanika_theme_presets_builtin(), jasanika_theme_presets_get_custom() );
}

/**
 * Get currently active preset ID.
 */
function jasanika_theme_presets_get_active_id(): string {
	$id = sanitize_key( (string) get_option( 'jasanika_theme_preset_active', 'jasanika-dark' ) );
	return $id ?: 'jasanika-dark';
}

/**
 * Get active preset array.
 *
 * @return array<string, mixed>
 */
function jasanika_theme_presets_get_active(): array {
	$all = jasanika_theme_presets_get_all();
	$id  = jasanika_theme_presets_get_active_id();

	if ( isset( $all[ $id ] ) ) {
		return $all[ $id ];
	}

	$builtin = jasanika_theme_presets_builtin();
	return $builtin['jasanika-dark'];
}

/**
 * Update an existing custom preset's config.
 *
 * @param string               $preset_id Preset ID.
 * @param array<string, mixed> $config    New config.
 * @return bool True on success, false when preset not found.
 */
function jasanika_theme_presets_update_custom( string $preset_id, array $config ): bool {
	$preset_id = sanitize_key( $preset_id );
	$custom    = jasanika_theme_presets_get_custom();

	if ( ! isset( $custom[ $preset_id ] ) ) {
		return false;
	}

	$custom[ $preset_id ]['config'] = jasanika_theme_presets_sanitize_config( $config );
	jasanika_theme_presets_save_custom( $custom );

	return true;
}

/**
 * Create a custom preset.
 *
 * @param string               $name   Preset name.
 * @param array<string, mixed> $config Preset config.
 * @return string Created preset ID.
 */
function jasanika_theme_presets_create_custom( string $name, array $config ): string {
	$name = sanitize_text_field( $name );
	if ( '' === $name ) {
		$name = __( 'Custom Preset', 'jasanika' );
	}

	$base_id = sanitize_title( $name );
	if ( '' === $base_id ) {
		$base_id = 'custom-preset';
	}

	$id     = $base_id;
	$all    = jasanika_theme_presets_get_all();
	$custom = jasanika_theme_presets_get_custom();
	$index  = 2;

	while ( isset( $all[ $id ] ) ) {
		$id = $base_id . '-' . $index;
		++$index;
	}

	$custom[ $id ] = array(
		'id'      => $id,
		'name'    => $name,
		'builtin' => false,
		'config'  => jasanika_theme_presets_sanitize_config( $config ),
	);

	jasanika_theme_presets_save_custom( $custom );

	return $id;
}

/**
 * Duplicate a preset into custom presets.
 *
 * @param string $preset_id Source preset ID.
 * @return string|WP_Error New preset ID or error.
 */
function jasanika_theme_presets_duplicate( string $preset_id ) {
	$all       = jasanika_theme_presets_get_all();
	$preset_id = sanitize_key( $preset_id );

	if ( ! isset( $all[ $preset_id ] ) ) {
		return new WP_Error( 'invalid_preset', __( 'Preset was not found.', 'jasanika' ) );
	}

	$source_name = (string) ( $all[ $preset_id ]['name'] ?? __( 'Preset', 'jasanika' ) );
	$new_name    = sprintf(
		/* translators: %s: preset name */
		__( '%s Copy', 'jasanika' ),
		$source_name
	);

	return jasanika_theme_presets_create_custom( $new_name, (array) ( $all[ $preset_id ]['config'] ?? array() ) );
}

/**
 * Delete a custom preset.
 *
 * @param string $preset_id Preset ID.
 * @return bool True when deleted.
 */
function jasanika_theme_presets_delete_custom( string $preset_id ): bool {
	$preset_id = sanitize_key( $preset_id );
	$custom    = jasanika_theme_presets_get_custom();

	if ( ! isset( $custom[ $preset_id ] ) ) {
		return false;
	}

	unset( $custom[ $preset_id ] );
	jasanika_theme_presets_save_custom( $custom );

	if ( jasanika_theme_presets_get_active_id() === $preset_id ) {
		update_option( 'jasanika_theme_preset_active', 'jasanika-dark' );
	}

	return true;
}

/**
 * Build preset config from current Theme Settings values.
 *
 * @return array<string, string>
 */
function jasanika_theme_presets_build_config_from_theme_settings(): array {
	$active   = jasanika_theme_presets_get_active();
	$defaults = jasanika_theme_presets_sanitize_config( $active['config'] ?? array() );

	$primary   = sanitize_hex_color( (string) jasanika_get_option( 'brand_primary', '' ) );
	$secondary = sanitize_hex_color( (string) jasanika_get_option( 'brand_secondary', '' ) );
	$accent    = sanitize_hex_color( (string) jasanika_get_option( 'brand_accent', '' ) );

	return array(
		'primary_color'    => $primary ?: $defaults['primary_color'],
		'secondary_color'  => $secondary ?: $defaults['secondary_color'],
		'accent_color'     => $accent ?: $defaults['accent_color'],
		'background_color' => $defaults['background_color'],
		'text_color'       => $defaults['text_color'],
		'button_style'     => $defaults['button_style'],
	);
}

/**
 * Activate a preset.
 *
 * @param string $preset_id Preset ID.
 * @return bool True when activated.
 */
function jasanika_theme_presets_activate( string $preset_id ): bool {
	$all       = jasanika_theme_presets_get_all();
	$preset_id = sanitize_key( $preset_id );

	if ( ! isset( $all[ $preset_id ] ) ) {
		return false;
	}

	$config           = jasanika_theme_presets_sanitize_config( $all[ $preset_id ]['config'] ?? array() );
	$current_settings = get_option( 'jasanika_settings', array() );
	$current_settings = is_array( $current_settings ) ? $current_settings : array();

	$current_settings['brand_primary']   = $config['primary_color'];
	$current_settings['brand_secondary'] = $config['secondary_color'];
	$current_settings['brand_accent']    = $config['accent_color'];

	update_option( 'jasanika_settings', $current_settings );
	update_option( 'jasanika_theme_preset_active', $preset_id );

	return true;
}

/**
 * Export a preset to JSON string.
 *
 * @param string $preset_id Preset ID.
 * @return string|WP_Error
 */
function jasanika_theme_presets_export_json( string $preset_id ) {
	$all       = jasanika_theme_presets_get_all();
	$preset_id = sanitize_key( $preset_id );

	if ( ! isset( $all[ $preset_id ] ) ) {
		return new WP_Error( 'invalid_preset', __( 'Preset was not found.', 'jasanika' ) );
	}

	$preset = $all[ $preset_id ];
	$config = jasanika_theme_presets_sanitize_config( $preset['config'] ?? array() );

	$payload = array(
		'version' => jasanika_theme_presets_version(),
		'name'    => (string) $preset['name'],
		'slug'    => (string) $preset['id'],
		'config'  => $config,
	);

	return (string) wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
}

/**
 * Import a preset from JSON.
 *
 * @param string $json JSON content.
 * @return string|WP_Error Imported preset ID or error.
 */
function jasanika_theme_presets_import_json( string $json ) {
	$data = json_decode( $json, true );

	if ( ! is_array( $data ) ) {
		return new WP_Error( 'invalid_json', __( 'Invalid JSON file.', 'jasanika' ) );
	}

	$version = sanitize_text_field( (string) ( $data['version'] ?? '' ) );
	if ( jasanika_theme_presets_version() !== $version ) {
		return new WP_Error( 'invalid_version', __( 'Unsupported preset version.', 'jasanika' ) );
	}

	$name = sanitize_text_field( (string) ( $data['name'] ?? '' ) );
	if ( '' === $name ) {
		return new WP_Error( 'invalid_name', __( 'Preset name is required.', 'jasanika' ) );
	}

	$config = $data['config'] ?? null;
	if ( ! is_array( $config ) ) {
		return new WP_Error( 'invalid_structure', __( 'Preset config is missing or invalid.', 'jasanika' ) );
	}

	return jasanika_theme_presets_create_custom( $name, $config );
}

/**
 * Return computed CSS variables for active preset.
 *
 * @return array<string, string>
 */
function jasanika_theme_presets_get_css_variables(): array {
	$active = jasanika_theme_presets_get_active();
	$config = jasanika_theme_presets_sanitize_config( $active['config'] ?? array() );

	$primary   = $config['primary_color'];
	$secondary = $config['secondary_color'];
	$accent    = $config['accent_color'];
	$bg        = $config['background_color'];
	$text      = $config['text_color'];

	return array(
		'--js-primary'      => $primary,
		'--js-secondary'    => $secondary,
		'--js-accent'       => $accent,
		'--js-bg'           => $bg,
		'--js-text'         => $text,
		'--js-button-style' => $config['button_style'],
		'--primary'         => $primary,
		'--primary-hover'   => jasanika_theme_presets_adjust_hex_color( $primary, 12 ),
		'--accent'          => $accent,
		'--bg-main'         => $bg,
		'--bg-secondary'    => $secondary,
		'--text-main'       => $text,
		'--brand-primary'   => $primary,
		'--brand-secondary' => $secondary,
		'--brand-accent'    => $accent,
	);
}

/**
 * Convert a CSS variable map to a :root block.
 *
 * @param array<string, string> $variables CSS variables.
 */
function jasanika_theme_presets_build_root_css( array $variables ): string {
	if ( empty( $variables ) ) {
		return '';
	}

	$allowed_vars = array(
		'--js-primary',
		'--js-secondary',
		'--js-accent',
		'--js-bg',
		'--js-text',
		'--js-button-style',
		'--primary',
		'--primary-hover',
		'--accent',
		'--bg-main',
		'--bg-secondary',
		'--text-main',
		'--brand-primary',
		'--brand-secondary',
		'--brand-accent',
	);

	$css = ':root{';
	foreach ( $variables as $name => $value ) {
		if ( ! in_array( $name, $allowed_vars, true ) ) {
			continue;
		}

		if ( '--js-button-style' === $name ) {
			$value = sanitize_key( (string) $value );
			if ( ! in_array( $value, jasanika_theme_presets_button_styles(), true ) ) {
				$value = 'solid';
			}
		} else {
			$sanitized = sanitize_hex_color( (string) $value );
			if ( ! $sanitized ) {
				continue;
			}
			$value = $sanitized;
		}

		$css .= $name . ':' . $value . ';';
	}
	$css .= '}';

	return $css;
}

/**
 * Output admin :root variables on Jasanika admin pages.
 */
function jasanika_theme_presets_output_admin_variables(): void {
	$screen = get_current_screen();

	if ( ! $screen || ! is_string( $screen->id ) ) {
		return;
	}

	$is_jasanika_screen = ( 'toplevel_page_jasanika' === $screen->id ) || 0 === strpos( $screen->id, 'jasanika_page_jasanika-' );
	if ( ! $is_jasanika_screen ) {
		return;
	}

	$css = jasanika_theme_presets_build_root_css( jasanika_theme_presets_get_css_variables() );
	if ( '' === $css ) {
		return;
	}

	echo '<style id="jasanika-theme-preset-admin-vars">' . $css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'admin_head', 'jasanika_theme_presets_output_admin_variables' );

/**
 * Adjust a HEX color by percentage.
 *
 * @param string $hex     #rrggbb.
 * @param int    $percent Positive brightens, negative darkens.
 */
function jasanika_theme_presets_adjust_hex_color( string $hex, int $percent ): string {
	$hex = ltrim( $hex, '#' );
	if ( 6 !== strlen( $hex ) ) {
		return '#c79cda';
	}

	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );

	$shift = (int) round( 255 * ( $percent / 100 ) );

	$r = max( 0, min( 255, $r + $shift ) );
	$g = max( 0, min( 255, $g + $shift ) );
	$b = max( 0, min( 255, $b + $shift ) );

	return sprintf( '#%02x%02x%02x', $r, $g, $b );
}
