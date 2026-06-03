<?php

/**
 * Jasanika Admin – Color Settings Page
 *
 * Manages Primary, Secondary and Accent brand colours.
 * Settings are stored inside the shared jasanika_settings option.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'jasanika_color_settings_init' );
add_action( 'admin_enqueue_scripts', 'jasanika_color_settings_enqueue' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue Color Settings stylesheet only on the Color Settings page.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_color_settings_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-color-settings' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'jasanika-color-settings',
		get_template_directory_uri() . '/assets/css/admin/color-settings.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}

// ---------------------------------------------------------------------------
// Settings Registration
// ---------------------------------------------------------------------------

/**
 * Register Color Settings fields under jasanika_color_settings_group.
 */
function jasanika_color_settings_init(): void {
	register_setting(
		'jasanika_color_settings_group',
		'jasanika_settings',
		array( 'sanitize_callback' => 'jasanika_sanitize_color_settings' )
	);

	add_settings_section(
		'jasanika_color_section_brand',
		__( 'Brand Colors', 'jasanika' ),
		'jasanika_color_section_brand_cb',
		'jasanika-color-settings'
	);

	add_settings_field(
		'jasanika_brand_primary',
		__( 'Primary Color', 'jasanika' ),
		'jasanika_settings_field_color',
		'jasanika-color-settings',
		'jasanika_color_section_brand',
		array(
			'key'     => 'brand_primary',
			'default' => '#b78acb',
		)
	);

	add_settings_field(
		'jasanika_brand_secondary',
		__( 'Secondary Color', 'jasanika' ),
		'jasanika_settings_field_color',
		'jasanika-color-settings',
		'jasanika_color_section_brand',
		array(
			'key'     => 'brand_secondary',
			'default' => '#24212b',
		)
	);

	add_settings_field(
		'jasanika_brand_accent',
		__( 'Accent Color', 'jasanika' ),
		'jasanika_settings_field_color',
		'jasanika-color-settings',
		'jasanika_color_section_brand',
		array(
			'key'     => 'brand_accent',
			'default' => '#f1c95d',
		)
	);
}

/**
 * Brand Colors section description.
 */
function jasanika_color_section_brand_cb(): void {
	echo '<p class="description">' . esc_html__( 'Configure the primary brand colours used throughout the theme.', 'jasanika' ) . '</p>';
}

// ---------------------------------------------------------------------------
// Sanitize
// ---------------------------------------------------------------------------

/**
 * Sanitize Color Settings input.
 * Merges updated colour values into the full jasanika_settings option.
 *
 * @param mixed $input Raw POST input.
 * @return array Full merged settings array.
 */
function jasanika_sanitize_color_settings( mixed $input ): array {
	if ( ! is_array( $input ) ) {
		$input = array();
	}

	$existing = get_option( 'jasanika_settings', array() );

	$existing['brand_primary']   = jasanika_sanitize_hex_color( $input['brand_primary']   ?? '' );
	$existing['brand_secondary'] = jasanika_sanitize_hex_color( $input['brand_secondary'] ?? '' );
	$existing['brand_accent']    = jasanika_sanitize_hex_color( $input['brand_accent']    ?? '' );

	return $existing;
}

// ---------------------------------------------------------------------------
// Page Render
// ---------------------------------------------------------------------------

/**
 * Render the Color Settings admin page.
 */
function jasanika_admin_page_color_settings(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}
	?>
	<div class="wrap jasanika-color-settings-wrap">

		<div class="jasanika-admin-header">
			<span class="dashicons dashicons-art"></span>
			<div>
				<h1><?php esc_html_e( 'Color Settings', 'jasanika' ); ?></h1>
				<p><?php esc_html_e( 'Manage brand colours used across the entire theme.', 'jasanika' ); ?></p>
			</div>
		</div>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'jasanika_color_settings_group' );
			do_settings_sections( 'jasanika-color-settings' );
			submit_button( __( 'Save Color Settings', 'jasanika' ) );
			?>
		</form>

	</div>
	<?php
}
