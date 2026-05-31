<?php

/**
 * Jasanika Admin – Cookie Manager Page
 *
 * Registers and renders the Cookie Manager admin page.
 * Settings are stored in the jasanika_cookie_consent_settings option.
 *
 * Fields:
 *   – Banner Enabled
 *   – Banner Title
 *   – Banner Description
 *   – Privacy Policy URL
 *   – Cookie Policy URL
 *   – Consent Expiration (days)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'jasanika_cookie_manager_settings_init' );
add_action( 'admin_enqueue_scripts', 'jasanika_cookie_manager_enqueue' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue the Cookie Manager stylesheet on the Cookie Manager page.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_cookie_manager_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-cookie-manager' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'jasanika-cookie-manager',
		get_template_directory_uri() . '/assets/css/admin/cookie-manager.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}

// ---------------------------------------------------------------------------
// Settings Registration
// ---------------------------------------------------------------------------

/**
 * Register the cookie consent settings group, sections and fields.
 */
function jasanika_cookie_manager_settings_init(): void {

	register_setting(
		'jasanika_cookie_consent_settings_group',
		'jasanika_cookie_consent_settings',
		array( 'sanitize_callback' => 'jasanika_cookie_manager_sanitize_settings' )
	);

	// ---- Banner Section ----------------------------------------------------

	add_settings_section(
		'jasanika_cookie_section_banner',
		__( 'Cookie Banner Settings', 'jasanika' ),
		'jasanika_cookie_section_banner_description',
		'jasanika-cookie-manager'
	);

	add_settings_field(
		'jasanika_cookie_banner_enabled',
		__( 'Enable Cookie Banner', 'jasanika' ),
		'jasanika_cookie_field_banner_enabled',
		'jasanika-cookie-manager',
		'jasanika_cookie_section_banner'
	);

	add_settings_field(
		'jasanika_cookie_banner_title',
		__( 'Banner Title', 'jasanika' ),
		'jasanika_cookie_field_banner_title',
		'jasanika-cookie-manager',
		'jasanika_cookie_section_banner'
	);

	add_settings_field(
		'jasanika_cookie_banner_description',
		__( 'Banner Description', 'jasanika' ),
		'jasanika_cookie_field_banner_description',
		'jasanika-cookie-manager',
		'jasanika_cookie_section_banner'
	);

	// ---- Links Section -----------------------------------------------------

	add_settings_section(
		'jasanika_cookie_section_links',
		__( 'Policy Links', 'jasanika' ),
		'jasanika_cookie_section_links_description',
		'jasanika-cookie-manager'
	);

	add_settings_field(
		'jasanika_cookie_privacy_policy_url',
		__( 'Privacy Policy URL', 'jasanika' ),
		'jasanika_cookie_field_privacy_policy_url',
		'jasanika-cookie-manager',
		'jasanika_cookie_section_links'
	);

	add_settings_field(
		'jasanika_cookie_cookie_policy_url',
		__( 'Cookie Policy URL', 'jasanika' ),
		'jasanika_cookie_field_cookie_policy_url',
		'jasanika-cookie-manager',
		'jasanika_cookie_section_links'
	);

	// ---- Consent Section ---------------------------------------------------

	add_settings_section(
		'jasanika_cookie_section_consent',
		__( 'Consent Settings', 'jasanika' ),
		'jasanika_cookie_section_consent_description',
		'jasanika-cookie-manager'
	);

	add_settings_field(
		'jasanika_cookie_consent_expiration',
		__( 'Consent Expiration (days)', 'jasanika' ),
		'jasanika_cookie_field_consent_expiration',
		'jasanika-cookie-manager',
		'jasanika_cookie_section_consent'
	);
}

// ---------------------------------------------------------------------------
// Section Descriptions
// ---------------------------------------------------------------------------

/** Render Banner section description. */
function jasanika_cookie_section_banner_description(): void {
	echo '<p class="jasanika-cookie__section-desc">'
		. esc_html__( 'Configure the cookie consent banner shown to visitors on their first visit.', 'jasanika' )
		. '</p>';
}

/** Render Links section description. */
function jasanika_cookie_section_links_description(): void {
	echo '<p class="jasanika-cookie__section-desc">'
		. esc_html__( 'Links shown in the cookie banner. Leave empty to hide.', 'jasanika' )
		. '</p>';
}

/** Render Consent section description. */
function jasanika_cookie_section_consent_description(): void {
	echo '<p class="jasanika-cookie__section-desc">'
		. esc_html__( 'Configure how long consent decisions are stored.', 'jasanika' )
		. '</p>';
}

// ---------------------------------------------------------------------------
// Field Renderers
// ---------------------------------------------------------------------------

/** Render the Banner Enabled toggle. */
function jasanika_cookie_field_banner_enabled(): void {
	$settings = jasanika_cookie_consent_get_settings();
	$checked  = ! empty( $settings['banner_enabled'] );
	?>
	<label class="jasanika-cookie__toggle">
		<input
			type="checkbox"
			name="jasanika_cookie_consent_settings[banner_enabled]"
			id="jasanika_cookie_banner_enabled"
			value="1"
			<?php checked( $checked ); ?>
		>
		<span class="jasanika-cookie__toggle-label">
			<?php esc_html_e( 'Show cookie consent banner to visitors', 'jasanika' ); ?>
		</span>
	</label>
	<?php
}

/** Render the Banner Title field. */
function jasanika_cookie_field_banner_title(): void {
	$settings = jasanika_cookie_consent_get_settings();
	?>
	<input
		type="text"
		name="jasanika_cookie_consent_settings[banner_title]"
		id="jasanika_cookie_banner_title"
		class="jasanika-cookie__input regular-text"
		value="<?php echo esc_attr( $settings['banner_title'] ); ?>"
	>
	<p class="jasanika-cookie__hint">
		<?php esc_html_e( 'Default: Cookies & Privacy', 'jasanika' ); ?>
	</p>
	<?php
}

/** Render the Banner Description field. */
function jasanika_cookie_field_banner_description(): void {
	$settings = jasanika_cookie_consent_get_settings();
	?>
	<textarea
		name="jasanika_cookie_consent_settings[banner_description]"
		id="jasanika_cookie_banner_description"
		class="jasanika-cookie__textarea large-text"
		rows="3"
	><?php echo esc_textarea( $settings['banner_description'] ); ?></textarea>
	<p class="jasanika-cookie__hint">
		<?php esc_html_e( 'Brief explanation shown to visitors in the cookie banner.', 'jasanika' ); ?>
	</p>
	<?php
}

/** Render the Privacy Policy URL field. */
function jasanika_cookie_field_privacy_policy_url(): void {
	$settings = jasanika_cookie_consent_get_settings();
	?>
	<input
		type="url"
		name="jasanika_cookie_consent_settings[privacy_policy_url]"
		id="jasanika_cookie_privacy_policy_url"
		class="jasanika-cookie__input large-text"
		value="<?php echo esc_attr( $settings['privacy_policy_url'] ); ?>"
		placeholder="https://"
	>
	<p class="jasanika-cookie__hint">
		<?php esc_html_e( 'Leave empty to hide the Privacy Policy link in the banner.', 'jasanika' ); ?>
	</p>
	<?php
}

/** Render the Cookie Policy URL field. */
function jasanika_cookie_field_cookie_policy_url(): void {
	$settings = jasanika_cookie_consent_get_settings();
	?>
	<input
		type="url"
		name="jasanika_cookie_consent_settings[cookie_policy_url]"
		id="jasanika_cookie_cookie_policy_url"
		class="jasanika-cookie__input large-text"
		value="<?php echo esc_attr( $settings['cookie_policy_url'] ); ?>"
		placeholder="https://"
	>
	<p class="jasanika-cookie__hint">
		<?php esc_html_e( 'Leave empty to hide the Cookie Policy link in the banner.', 'jasanika' ); ?>
	</p>
	<?php
}

/** Render the Consent Expiration field. */
function jasanika_cookie_field_consent_expiration(): void {
	$settings = jasanika_cookie_consent_get_settings();
	?>
	<input
		type="number"
		name="jasanika_cookie_consent_settings[consent_expiration]"
		id="jasanika_cookie_consent_expiration"
		class="jasanika-cookie__input small-text"
		value="<?php echo esc_attr( (int) $settings['consent_expiration'] ); ?>"
		min="1"
		max="3650"
	>
	<span class="jasanika-cookie__hint-inline">
		<?php esc_html_e( 'days (default: 365)', 'jasanika' ); ?>
	</span>
	<?php
}

// ---------------------------------------------------------------------------
// Sanitization
// ---------------------------------------------------------------------------

/**
 * Sanitize all cookie consent settings before saving.
 *
 * @param mixed $input Raw input array.
 * @return array<string, mixed>
 */
function jasanika_cookie_manager_sanitize_settings( $input ): array {
	if ( ! is_array( $input ) ) {
		return array();
	}

	$sanitized = array();

	$sanitized['banner_enabled'] = ! empty( $input['banner_enabled'] ) ? '1' : '';

	$sanitized['banner_title'] = isset( $input['banner_title'] )
		? sanitize_text_field( wp_unslash( $input['banner_title'] ) )
		: '';

	$sanitized['banner_description'] = isset( $input['banner_description'] )
		? sanitize_textarea_field( wp_unslash( $input['banner_description'] ) )
		: '';

	$sanitized['privacy_policy_url'] = isset( $input['privacy_policy_url'] )
		? esc_url_raw( wp_unslash( $input['privacy_policy_url'] ) )
		: '';

	$sanitized['cookie_policy_url'] = isset( $input['cookie_policy_url'] )
		? esc_url_raw( wp_unslash( $input['cookie_policy_url'] ) )
		: '';

	$expiration = isset( $input['consent_expiration'] ) ? (int) $input['consent_expiration'] : 365;
	$sanitized['consent_expiration'] = max( 1, min( 3650, $expiration ) );

	return $sanitized;
}

// ---------------------------------------------------------------------------
// Page Renderer
// ---------------------------------------------------------------------------

/**
 * Render the Cookie Manager admin page.
 */
function jasanika_admin_page_cookie_manager(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}
	?>
	<div class="wrap jasanika-cookie">

		<!-- Header -->
		<div class="jasanika-cookie__header">
			<span class="jasanika-cookie__header-icon dashicons dashicons-privacy"></span>
			<div>
				<h1 class="jasanika-cookie__title"><?php esc_html_e( 'Cookie Manager', 'jasanika' ); ?></h1>
				<p class="jasanika-cookie__subtitle">
					<?php esc_html_e( 'GDPR-compliant cookie consent manager. Configure your cookie banner and consent categories.', 'jasanika' ); ?>
				</p>
			</div>
		</div>

		<!-- Category overview -->
		<div class="jasanika-cookie__categories-info">
			<h2 class="jasanika-cookie__categories-title">
				<?php esc_html_e( 'Cookie Categories', 'jasanika' ); ?>
			</h2>
			<div class="jasanika-cookie__categories-grid">

				<div class="jasanika-cookie__category-card jasanika-cookie__category-card--required">
					<span class="dashicons dashicons-shield"></span>
					<strong><?php esc_html_e( 'Necessary', 'jasanika' ); ?></strong>
					<p><?php esc_html_e( 'Always enabled. Required for the website to function properly.', 'jasanika' ); ?></p>
					<span class="jasanika-cookie__badge jasanika-cookie__badge--required">
						<?php esc_html_e( 'Always On', 'jasanika' ); ?>
					</span>
				</div>

				<div class="jasanika-cookie__category-card">
					<span class="dashicons dashicons-chart-bar"></span>
					<strong><?php esc_html_e( 'Analytics', 'jasanika' ); ?></strong>
					<p><?php esc_html_e( 'Helps understand how visitors interact with the website.', 'jasanika' ); ?></p>
					<span class="jasanika-cookie__badge jasanika-cookie__badge--optional">
						<?php esc_html_e( 'Optional', 'jasanika' ); ?>
					</span>
				</div>

				<div class="jasanika-cookie__category-card">
					<span class="dashicons dashicons-megaphone"></span>
					<strong><?php esc_html_e( 'Marketing', 'jasanika' ); ?></strong>
					<p><?php esc_html_e( 'Used to deliver personalised advertisements.', 'jasanika' ); ?></p>
					<span class="jasanika-cookie__badge jasanika-cookie__badge--optional">
						<?php esc_html_e( 'Optional', 'jasanika' ); ?>
					</span>
				</div>

			</div>
		</div>

		<?php settings_errors( 'jasanika_cookie_consent_settings' ); ?>

		<form method="post" action="options.php" class="jasanika-cookie__form">
			<?php
			settings_fields( 'jasanika_cookie_consent_settings_group' );
			do_settings_sections( 'jasanika-cookie-manager' );
			submit_button( __( 'Save Cookie Settings', 'jasanika' ), 'primary jasanika-cookie__submit' );
			?>
		</form>

	</div>
	<?php
}
