<?php

/**
 * Jasanika Admin – Maintenance Manager Page
 *
 * Settings for the Maintenance Mode system.
 * Stored in the jasanika_maintenance WordPress option.
 *
 * Sections:
 *   – General (enable/disable, access rules)
 *   – Page Content (title, description, contact)
 *   – Countdown Timer (launch date)
 *   – Social Links (Facebook, Instagram, LinkedIn, YouTube)
 *   – Newsletter (newsletter signup integration)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'jasanika_maintenance_settings_init' );

// ---------------------------------------------------------------------------
// Settings Registration
// ---------------------------------------------------------------------------

/**
 * Register the maintenance settings group, sections and fields.
 */
function jasanika_maintenance_settings_init(): void {

	register_setting(
		'jasanika_maintenance_group',
		JASANIKA_MAINTENANCE_OPTION,
		array( 'sanitize_callback' => 'jasanika_maintenance_sanitize_settings' )
	);

	// ---- General Section ---------------------------------------------------

	add_settings_section(
		'jasanika_maintenance_section_general',
		__( 'Maintenance Mode', 'jasanika' ),
		'__return_false',
		'jasanika-maintenance-manager'
	);

	add_settings_field(
		'jasanika_maintenance_enabled',
		__( 'Enable Maintenance Mode', 'jasanika' ),
		'jasanika_maintenance_field_enabled',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_general'
	);

	add_settings_field(
		'jasanika_maintenance_allow_editors',
		__( 'Allow Editors', 'jasanika' ),
		'jasanika_maintenance_field_allow_editors',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_general'
	);

	// ---- Content Section ---------------------------------------------------

	add_settings_section(
		'jasanika_maintenance_section_content',
		__( 'Page Content', 'jasanika' ),
		'__return_false',
		'jasanika-maintenance-manager'
	);

	add_settings_field(
		'jasanika_maintenance_title',
		__( 'Title', 'jasanika' ),
		'jasanika_maintenance_field_title',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_content'
	);

	add_settings_field(
		'jasanika_maintenance_description',
		__( 'Description', 'jasanika' ),
		'jasanika_maintenance_field_description',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_content'
	);

	add_settings_field(
		'jasanika_maintenance_contact_email',
		__( 'Contact Email', 'jasanika' ),
		'jasanika_maintenance_field_contact_email',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_content'
	);

	add_settings_field(
		'jasanika_maintenance_contact_phone',
		__( 'Contact Phone', 'jasanika' ),
		'jasanika_maintenance_field_contact_phone',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_content'
	);

	// ---- Countdown Section -------------------------------------------------

	add_settings_section(
		'jasanika_maintenance_section_countdown',
		__( 'Countdown Timer', 'jasanika' ),
		'__return_false',
		'jasanika-maintenance-manager'
	);

	add_settings_field(
		'jasanika_maintenance_show_countdown',
		__( 'Show Countdown', 'jasanika' ),
		'jasanika_maintenance_field_show_countdown',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_countdown'
	);

	add_settings_field(
		'jasanika_maintenance_launch_date',
		__( 'Launch Date & Time', 'jasanika' ),
		'jasanika_maintenance_field_launch_date',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_countdown'
	);

	// ---- Social Section ----------------------------------------------------

	add_settings_section(
		'jasanika_maintenance_section_social',
		__( 'Social Links', 'jasanika' ),
		'jasanika_maintenance_section_social_description',
		'jasanika-maintenance-manager'
	);

	add_settings_field(
		'jasanika_maintenance_facebook_url',
		__( 'Facebook', 'jasanika' ),
		'jasanika_maintenance_field_facebook_url',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_social'
	);

	add_settings_field(
		'jasanika_maintenance_instagram_url',
		__( 'Instagram', 'jasanika' ),
		'jasanika_maintenance_field_instagram_url',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_social'
	);

	add_settings_field(
		'jasanika_maintenance_linkedin_url',
		__( 'LinkedIn', 'jasanika' ),
		'jasanika_maintenance_field_linkedin_url',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_social'
	);

	add_settings_field(
		'jasanika_maintenance_youtube_url',
		__( 'YouTube', 'jasanika' ),
		'jasanika_maintenance_field_youtube_url',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_social'
	);

	// ---- Newsletter Section ------------------------------------------------

	add_settings_section(
		'jasanika_maintenance_section_newsletter',
		__( 'Newsletter', 'jasanika' ),
		'__return_false',
		'jasanika-maintenance-manager'
	);

	add_settings_field(
		'jasanika_maintenance_newsletter_enabled',
		__( 'Enable Newsletter Signup', 'jasanika' ),
		'jasanika_maintenance_field_newsletter_enabled',
		'jasanika-maintenance-manager',
		'jasanika_maintenance_section_newsletter'
	);
}

// ---------------------------------------------------------------------------
// Sanitization
// ---------------------------------------------------------------------------

/**
 * Sanitize and validate all maintenance settings before saving.
 *
 * @param mixed $input Raw posted data.
 * @return array<string, mixed>
 */
function jasanika_maintenance_sanitize_settings( mixed $input ): array {
	if ( ! is_array( $input ) ) {
		return array();
	}

	$clean = array();

	$clean['enabled']            = ! empty( $input['enabled'] );
	$clean['allow_editors']      = ! empty( $input['allow_editors'] );
	$clean['title']              = sanitize_text_field( $input['title'] ?? '' );
	$clean['description']        = sanitize_textarea_field( $input['description'] ?? '' );
	$clean['contact_email']      = sanitize_email( $input['contact_email'] ?? '' );
	$clean['contact_phone']      = sanitize_text_field( $input['contact_phone'] ?? '' );
	$clean['show_countdown']     = ! empty( $input['show_countdown'] );
	$clean['launch_date']        = sanitize_text_field( $input['launch_date'] ?? '' );
	$clean['facebook_url']       = esc_url_raw( $input['facebook_url'] ?? '' );
	$clean['instagram_url']      = esc_url_raw( $input['instagram_url'] ?? '' );
	$clean['linkedin_url']       = esc_url_raw( $input['linkedin_url'] ?? '' );
	$clean['youtube_url']        = esc_url_raw( $input['youtube_url'] ?? '' );
	$clean['newsletter_enabled'] = ! empty( $input['newsletter_enabled'] );

	return $clean;
}

// ---------------------------------------------------------------------------
// Section Callbacks
// ---------------------------------------------------------------------------

function jasanika_maintenance_section_social_description(): void {
	echo '<p class="description">' . esc_html__( 'Leave fields empty to hide the corresponding icon.', 'jasanika' ) . '</p>';
}

// ---------------------------------------------------------------------------
// Field Renderers
// ---------------------------------------------------------------------------

function jasanika_maintenance_field_enabled(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<label>
		<input
			type="checkbox"
			name="<?php echo $name; ?>[enabled]"
			value="1"
			<?php checked( $settings['enabled'] ); ?>
		>
		<?php esc_html_e( 'Put the website into maintenance mode', 'jasanika' ); ?>
	</label>
	<p class="description">
		<?php esc_html_e( 'When enabled, visitors will see the maintenance page. Administrators will continue to see the website normally.', 'jasanika' ); ?>
	</p>
	<?php
}

function jasanika_maintenance_field_allow_editors(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<label>
		<input
			type="checkbox"
			name="<?php echo $name; ?>[allow_editors]"
			value="1"
			<?php checked( $settings['allow_editors'] ); ?>
		>
		<?php esc_html_e( 'Allow Editors to bypass the maintenance page', 'jasanika' ); ?>
	</label>
	<?php
}

function jasanika_maintenance_field_title(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<input
		type="text"
		name="<?php echo $name; ?>[title]"
		value="<?php echo esc_attr( $settings['title'] ); ?>"
		class="regular-text"
	>
	<?php
}

function jasanika_maintenance_field_description(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<textarea
		name="<?php echo $name; ?>[description]"
		rows="4"
		class="large-text"
	><?php echo esc_textarea( $settings['description'] ); ?></textarea>
	<?php
}

function jasanika_maintenance_field_contact_email(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<input
		type="email"
		name="<?php echo $name; ?>[contact_email]"
		value="<?php echo esc_attr( $settings['contact_email'] ); ?>"
		class="regular-text"
	>
	<p class="description"><?php esc_html_e( 'Contact email shown on the maintenance page.', 'jasanika' ); ?></p>
	<?php
}

function jasanika_maintenance_field_contact_phone(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<input
		type="text"
		name="<?php echo $name; ?>[contact_phone]"
		value="<?php echo esc_attr( $settings['contact_phone'] ); ?>"
		class="regular-text"
	>
	<p class="description"><?php esc_html_e( 'Contact phone shown on the maintenance page.', 'jasanika' ); ?></p>
	<?php
}

function jasanika_maintenance_field_show_countdown(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<label>
		<input
			type="checkbox"
			name="<?php echo $name; ?>[show_countdown]"
			value="1"
			<?php checked( $settings['show_countdown'] ); ?>
		>
		<?php esc_html_e( 'Display countdown timer until launch date', 'jasanika' ); ?>
	</label>
	<?php
}

function jasanika_maintenance_field_launch_date(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<input
		type="datetime-local"
		name="<?php echo $name; ?>[launch_date]"
		value="<?php echo esc_attr( $settings['launch_date'] ); ?>"
		class="regular-text"
	>
	<p class="description"><?php esc_html_e( 'Required for the countdown timer. Also sets the Retry-After HTTP header.', 'jasanika' ); ?></p>
	<?php
}

function jasanika_maintenance_field_facebook_url(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<input
		type="url"
		name="<?php echo $name; ?>[facebook_url]"
		value="<?php echo esc_attr( $settings['facebook_url'] ); ?>"
		class="regular-text"
		placeholder="https://facebook.com/..."
	>
	<?php
}

function jasanika_maintenance_field_instagram_url(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<input
		type="url"
		name="<?php echo $name; ?>[instagram_url]"
		value="<?php echo esc_attr( $settings['instagram_url'] ); ?>"
		class="regular-text"
		placeholder="https://instagram.com/..."
	>
	<?php
}

function jasanika_maintenance_field_linkedin_url(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<input
		type="url"
		name="<?php echo $name; ?>[linkedin_url]"
		value="<?php echo esc_attr( $settings['linkedin_url'] ); ?>"
		class="regular-text"
		placeholder="https://linkedin.com/in/..."
	>
	<?php
}

function jasanika_maintenance_field_youtube_url(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<input
		type="url"
		name="<?php echo $name; ?>[youtube_url]"
		value="<?php echo esc_attr( $settings['youtube_url'] ); ?>"
		class="regular-text"
		placeholder="https://youtube.com/..."
	>
	<?php
}

function jasanika_maintenance_field_newsletter_enabled(): void {
	$settings = jasanika_maintenance_get_settings();
	$name     = esc_attr( JASANIKA_MAINTENANCE_OPTION );
	?>
	<label>
		<input
			type="checkbox"
			name="<?php echo $name; ?>[newsletter_enabled]"
			value="1"
			<?php checked( $settings['newsletter_enabled'] ); ?>
		>
		<?php esc_html_e( 'Show newsletter signup form on the maintenance page', 'jasanika' ); ?>
	</label>
	<p class="description"><?php esc_html_e( 'Uses the existing Newsletter Manager integration.', 'jasanika' ); ?></p>
	<?php
}

// ---------------------------------------------------------------------------
// Page Renderer
// ---------------------------------------------------------------------------

/**
 * Renders the Maintenance Manager admin page.
 */
function jasanika_admin_page_maintenance_manager(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$is_active = jasanika_maintenance_is_active();
	?>
	<div class="wrap">

		<h1><?php esc_html_e( 'Jasanika – Maintenance Mode', 'jasanika' ); ?></h1>

		<?php if ( $is_active ) : ?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Maintenance mode is currently active.', 'jasanika' ); ?></strong>
					<?php esc_html_e( 'Visitors see the maintenance page. Administrators access the site normally.', 'jasanika' ); ?>
				</p>
			</div>
		<?php endif; ?>

		<?php settings_errors( JASANIKA_MAINTENANCE_OPTION ); ?>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'jasanika_maintenance_group' );
			do_settings_sections( 'jasanika-maintenance-manager' );
			submit_button( __( 'Save Settings', 'jasanika' ) );
			?>
		</form>

	</div>
	<?php
}
