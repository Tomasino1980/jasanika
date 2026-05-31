<?php

/**
 * Jasanika Admin – Theme Settings Page
 *
 * Registers and renders the Theme Settings admin page using the WordPress Settings API.
 * Sections: Branding, Contact Information, Social Networks, Footer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'jasanika_theme_settings_init' );
add_action( 'admin_enqueue_scripts', 'jasanika_theme_settings_enqueue' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue the WordPress media uploader on the Theme Settings page.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_theme_settings_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-theme-settings' !== $hook ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_script(
		'jasanika-media-uploader',
		get_template_directory_uri() . '/assets/js/admin/media-uploader.js',
		array(),
		'0.31.0',
		true
	);
}

// ---------------------------------------------------------------------------
// Settings Registration
// ---------------------------------------------------------------------------

/**
 * Register the settings group, sections and fields.
 */
function jasanika_theme_settings_init(): void {

	register_setting(
		'jasanika_settings_group',
		'jasanika_settings',
		array( 'sanitize_callback' => 'jasanika_sanitize_settings' )
	);

	// --- Branding Section ---------------------------------------------------

	add_settings_section(
		'jasanika_section_branding',
		__( 'Branding', 'jasanika' ),
		'__return_false',
		'jasanika-theme-settings'
	);

	add_settings_field(
		'jasanika_company_name',
		__( 'Company Name', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array( 'key' => 'company_name' )
	);

	add_settings_field(
		'jasanika_company_slogan',
		__( 'Company Slogan', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array( 'key' => 'company_slogan' )
	);

	add_settings_field(
		'jasanika_logo_url',
		__( 'Logo URL', 'jasanika' ),
		'jasanika_settings_field_media',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array(
			'key'         => 'logo_url',
			'media_title' => __( 'Select Logo', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_favicon_url',
		__( 'Favicon URL', 'jasanika' ),
		'jasanika_settings_field_media',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array(
			'key'         => 'favicon_url',
			'media_title' => __( 'Select Favicon', 'jasanika' ),
		)
	);

	// --- Contact Information Section ----------------------------------------

	add_settings_section(
		'jasanika_section_contact',
		__( 'Contact Information', 'jasanika' ),
		'__return_false',
		'jasanika-theme-settings'
	);

	add_settings_field(
		'jasanika_phone',
		__( 'Phone', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_contact',
		array( 'key' => 'phone' )
	);

	add_settings_field(
		'jasanika_email',
		__( 'Email', 'jasanika' ),
		'jasanika_settings_field_email',
		'jasanika-theme-settings',
		'jasanika_section_contact',
		array( 'key' => 'email' )
	);

	add_settings_field(
		'jasanika_address_street',
		__( 'Street Address', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_contact',
		array( 'key' => 'address_street' )
	);

	add_settings_field(
		'jasanika_address_city',
		__( 'City', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_contact',
		array( 'key' => 'address_city' )
	);

	add_settings_field(
		'jasanika_address_zip',
		__( 'ZIP Code', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_contact',
		array( 'key' => 'address_zip' )
	);

	// --- Social Networks Section ---------------------------------------------

	add_settings_section(
		'jasanika_section_social',
		__( 'Social Networks', 'jasanika' ),
		'__return_false',
		'jasanika-theme-settings'
	);

	add_settings_field(
		'jasanika_facebook_url',
		__( 'Facebook URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_social',
		array( 'key' => 'facebook_url' )
	);

	add_settings_field(
		'jasanika_instagram_url',
		__( 'Instagram URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_social',
		array( 'key' => 'instagram_url' )
	);

	add_settings_field(
		'jasanika_youtube_url',
		__( 'YouTube URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_social',
		array( 'key' => 'youtube_url' )
	);

	add_settings_field(
		'jasanika_linkedin_url',
		__( 'LinkedIn URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_social',
		array( 'key' => 'linkedin_url' )
	);

	// --- Footer Section ------------------------------------------------------

	add_settings_section(
		'jasanika_section_footer',
		__( 'Footer', 'jasanika' ),
		'__return_false',
		'jasanika-theme-settings'
	);

	add_settings_field(
		'jasanika_copyright_text',
		__( 'Copyright Text', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_footer',
		array(
			'key'         => 'copyright_text',
			'placeholder' => sprintf( '© %s Company Name', gmdate( 'Y' ) ),
		)
	);

	add_settings_field(
		'jasanika_footer_note',
		__( 'Footer Note', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_footer',
		array(
			'key'         => 'footer_note',
			'placeholder' => __( 'Handmade with love', 'jasanika' ),
		)
	);
}

// ---------------------------------------------------------------------------
// Sanitize
// ---------------------------------------------------------------------------

/**
 * Sanitize all settings before saving.
 *
 * @param mixed $input Raw input array.
 * @return array Sanitized values.
 */
function jasanika_sanitize_settings( mixed $input ): array {
	if ( ! is_array( $input ) ) {
		return array();
	}

	$sanitized = array();

	// Branding.
	$sanitized['company_name']   = sanitize_text_field( $input['company_name']   ?? '' );
	$sanitized['company_slogan'] = sanitize_text_field( $input['company_slogan'] ?? '' );
	$sanitized['logo_url']       = esc_url_raw( $input['logo_url']       ?? '' );
	$sanitized['favicon_url']    = esc_url_raw( $input['favicon_url']    ?? '' );

	// Contact.
	$sanitized['phone']          = sanitize_text_field( $input['phone']          ?? '' );
	$sanitized['email']          = sanitize_email( $input['email']               ?? '' );
	$sanitized['address_street'] = sanitize_text_field( $input['address_street'] ?? '' );
	$sanitized['address_city']   = sanitize_text_field( $input['address_city']   ?? '' );
	$sanitized['address_zip']    = sanitize_text_field( $input['address_zip']    ?? '' );

	// Social.
	$sanitized['facebook_url']   = esc_url_raw( $input['facebook_url']   ?? '' );
	$sanitized['instagram_url']  = esc_url_raw( $input['instagram_url']  ?? '' );
	$sanitized['youtube_url']    = esc_url_raw( $input['youtube_url']    ?? '' );
	$sanitized['linkedin_url']   = esc_url_raw( $input['linkedin_url']   ?? '' );

	// Footer.
	$sanitized['copyright_text'] = sanitize_text_field( $input['copyright_text'] ?? '' );
	$sanitized['footer_note']    = sanitize_text_field( $input['footer_note']    ?? '' );

	return $sanitized;
}

// ---------------------------------------------------------------------------
// Field Renderers
// ---------------------------------------------------------------------------

/**
 * Render a plain text input field.
 *
 * @param array $args Field arguments: key, placeholder (optional).
 */
function jasanika_settings_field_text( array $args ): void {
	$options     = get_option( 'jasanika_settings', array() );
	$value       = $options[ $args['key'] ] ?? '';
	$placeholder = $args['placeholder'] ?? '';

	printf(
		'<input type="text" id="jasanika_%1$s" name="jasanika_settings[%1$s]" value="%2$s" placeholder="%3$s" class="regular-text">',
		esc_attr( $args['key'] ),
		esc_attr( $value ),
		esc_attr( $placeholder )
	);
}

/**
 * Render an email input field.
 *
 * @param array $args Field arguments: key.
 */
function jasanika_settings_field_email( array $args ): void {
	$options = get_option( 'jasanika_settings', array() );
	$value   = $options[ $args['key'] ] ?? '';

	printf(
		'<input type="email" id="jasanika_%1$s" name="jasanika_settings[%1$s]" value="%2$s" class="regular-text">',
		esc_attr( $args['key'] ),
		esc_attr( $value )
	);
}

/**
 * Render a URL input field.
 *
 * @param array $args Field arguments: key.
 */
function jasanika_settings_field_url( array $args ): void {
	$options = get_option( 'jasanika_settings', array() );
	$value   = $options[ $args['key'] ] ?? '';

	printf(
		'<input type="url" id="jasanika_%1$s" name="jasanika_settings[%1$s]" value="%2$s" class="regular-text">',
		esc_attr( $args['key'] ),
		esc_url( $value )
	);
}

/**
 * Render a media uploader field (text input + Select Image button + preview).
 *
 * @param array $args Field arguments: key, media_title (optional).
 */
function jasanika_settings_field_media( array $args ): void {
	$options     = get_option( 'jasanika_settings', array() );
	$value       = $options[ $args['key'] ] ?? '';
	$field_id    = 'jasanika_' . $args['key'];
	$preview_id  = 'jasanika_preview_' . $args['key'];
	$media_title = $args['media_title'] ?? __( 'Select Image', 'jasanika' );
	?>
	<input
		type="text"
		id="<?php echo esc_attr( $field_id ); ?>"
		name="jasanika_settings[<?php echo esc_attr( $args['key'] ); ?>]"
		value="<?php echo esc_attr( $value ); ?>"
		class="regular-text"
	>
	<button
		type="button"
		class="button jasanika-media-upload-btn"
		data-target="<?php echo esc_attr( $field_id ); ?>"
		data-preview="<?php echo esc_attr( $preview_id ); ?>"
		data-title="<?php echo esc_attr( $media_title ); ?>"
	>
		<?php esc_html_e( 'Select Image', 'jasanika' ); ?>
	</button>
	<?php if ( $value ) : ?>
		<br>
		<img
			id="<?php echo esc_attr( $preview_id ); ?>"
			src="<?php echo esc_url( $value ); ?>"
			style="max-width:150px;margin-top:8px;"
			alt=""
		>
	<?php else : ?>
		<img
			id="<?php echo esc_attr( $preview_id ); ?>"
			src=""
			style="max-width:150px;margin-top:8px;display:none;"
			alt=""
		>
	<?php endif; ?>
	<?php
}

// ---------------------------------------------------------------------------
// Page Render
// ---------------------------------------------------------------------------

/**
 * Renders the Theme Settings admin page.
 */
function jasanika_admin_page_theme_settings(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Jasanika – Theme Settings', 'jasanika' ); ?></h1>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'jasanika_settings_group' );
			do_settings_sections( 'jasanika-theme-settings' );
			submit_button( __( 'Save Settings', 'jasanika' ) );
			?>
		</form>
	</div>
	<?php
}
