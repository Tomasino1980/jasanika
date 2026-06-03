<?php

/**
 * Jasanika Admin – Logo Settings Page
 *
 * Manages all logo-related settings with independent controls per location:
 *  – Header Logo
 *  – Footer Logo
 *  – Hero Logo
 *  – Mobile Logo
 *  – Favicon
 *
 * Uses numeric inputs instead of range sliders.
 * Each location has its own live preview panel.
 * Settings are stored inside the shared jasanika_settings option.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'jasanika_logo_settings_init' );
add_action( 'admin_enqueue_scripts', 'jasanika_logo_settings_enqueue' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue Logo Settings assets only on the Logo Settings page.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_logo_settings_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-logo-settings' !== $hook ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'jasanika-logo-settings',
		get_template_directory_uri() . '/assets/css/admin/logo-settings.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'jasanika-media-uploader',
		get_template_directory_uri() . '/assets/js/admin/media-uploader.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

	wp_enqueue_script(
		'jasanika-logo-settings',
		get_template_directory_uri() . '/assets/js/admin/logo-settings.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);
}

// ---------------------------------------------------------------------------
// Settings Registration
// ---------------------------------------------------------------------------

/**
 * Register Logo Settings fields under jasanika_logo_settings_group.
 */
function jasanika_logo_settings_init(): void {
	register_setting(
		'jasanika_logo_settings_group',
		'jasanika_settings',
		array( 'sanitize_callback' => 'jasanika_sanitize_logo_settings' )
	);

	// The actual field registration is handled inline in the page render
	// to support the custom grouped layout (not the standard WP table layout).
}

// ---------------------------------------------------------------------------
// Sanitize
// ---------------------------------------------------------------------------

/**
 * Sanitize Logo Settings input.
 * Merges updated logo values into the full jasanika_settings option.
 *
 * @param mixed $input Raw POST input.
 * @return array Full merged settings array.
 */
function jasanika_sanitize_logo_settings( mixed $input ): array {
	if ( ! is_array( $input ) ) {
		$input = array();
	}

	$existing = get_option( 'jasanika_settings', array() );

	// Logo URLs.
	$existing['logo_url']        = esc_url_raw( $input['logo_url']        ?? '' );
	$existing['footer_logo_url'] = esc_url_raw( $input['footer_logo_url'] ?? '' );
	$existing['logo_hero_url']   = esc_url_raw( $input['logo_hero_url']   ?? '' );
	$existing['logo_mobile_url'] = esc_url_raw( $input['logo_mobile_url'] ?? '' );
	$existing['favicon_url']     = esc_url_raw( $input['favicon_url']     ?? '' );

	// Visibility flags.
	$existing['logo_show_header'] = isset( $input['logo_show_header'] ) ? 1 : 0;
	$existing['logo_show_footer'] = isset( $input['logo_show_footer'] ) ? 1 : 0;
	$existing['logo_show_hero']   = isset( $input['logo_show_hero'] )   ? 1 : 0;
	$existing['logo_show_mobile'] = isset( $input['logo_show_mobile'] ) ? 1 : 0;

	// Position values.
	$valid_h = array( 'left', 'center', 'right' );
	$valid_v = array( 'top_left', 'top_center', 'top_right', 'center', 'bottom_left', 'bottom_center', 'bottom_right' );

	$header_pos = sanitize_key( $input['logo_header_pos'] ?? 'left' );
	$existing['logo_header_pos'] = in_array( $header_pos, $valid_h, true ) ? $header_pos : 'left';

	$footer_pos = sanitize_key( $input['logo_footer_pos'] ?? 'left' );
	$existing['logo_footer_pos'] = in_array( $footer_pos, $valid_h, true ) ? $footer_pos : 'left';

	$hero_pos = sanitize_key( $input['logo_hero_pos'] ?? 'center' );
	$existing['logo_hero_pos'] = in_array( $hero_pos, $valid_v, true ) ? $hero_pos : 'center';

	$mobile_pos = sanitize_key( $input['logo_mobile_pos'] ?? 'left' );
	$existing['logo_mobile_pos'] = in_array( $mobile_pos, $valid_h, true ) ? $mobile_pos : 'left';

	// Per-location numeric fields: key => [ min, max, default ].
	$logo_numeric = array(
		'logo_header_width'  => array( 50, 600, 200 ),
		'logo_header_height' => array( 20, 600, 100 ),
		'logo_footer_width'  => array( 50, 600, 200 ),
		'logo_footer_height' => array( 20, 600, 100 ),
		'logo_hero_width'    => array( 50, 600, 200 ),
		'logo_hero_height'   => array( 20, 600, 100 ),
		'logo_mobile_width'  => array( 30, 400, 120 ),
		'logo_mobile_height' => array( 20, 400,  80 ),
	);

	foreach ( $logo_numeric as $field => $range ) {
		[ $min, $max, $default ] = $range;
		$val = '' !== ( $input[ $field ] ?? '' ) ? absint( $input[ $field ] ) : $default;
		$existing[ $field ] = min( $max, max( $min, $val ) );
	}

	// Height modes per location.
	$valid_modes = array( 'auto', 'custom' );

	foreach ( array( 'header', 'footer', 'hero', 'mobile' ) as $loc ) {
		$mode_key = 'logo_' . $loc . '_height_mode';
		$mode_val = sanitize_key( $input[ $mode_key ] ?? 'auto' );
		$existing[ $mode_key ] = in_array( $mode_val, $valid_modes, true ) ? $mode_val : 'auto';
	}

	// Per-location margins.
	$locations = array( 'header', 'footer', 'hero', 'mobile' );
	$sides     = array( 'top', 'right', 'bottom', 'left' );

	foreach ( $locations as $loc ) {
		foreach ( $sides as $side ) {
			$key = 'logo_' . $loc . '_margin_' . $side;
			$val = absint( $input[ $key ] ?? 0 );
			$existing[ $key ] = min( 200, max( 0, $val ) );
		}
	}

	return $existing;
}

// ---------------------------------------------------------------------------
// Helper: get per-location value with migration fallback
// ---------------------------------------------------------------------------

/**
 * Get a per-location logo numeric value with fallback to legacy keys.
 *
 * @param string $location header|footer|hero|mobile
 * @param string $property width|height
 * @return int
 */
function jasanika_logo_loc_get_numeric( string $location, string $property ): int {
	$options = get_option( 'jasanika_settings', array() );
	$key     = 'logo_' . $location . '_' . $property;

	if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
		return (int) $options[ $key ];
	}

	// Migration fallbacks.
	$fallback_map = array(
		'header_width'  => array( 'logo_desktop_width', 'logo_width' ),
		'footer_width'  => array( 'logo_width' ),
		'hero_width'    => array( 'logo_width' ),
		'mobile_width'  => array( 'logo_mobile_width' ),
		'header_height' => array( 'logo_height' ),
		'footer_height' => array( 'logo_height' ),
		'hero_height'   => array( 'logo_height' ),
		'mobile_height' => array( 'logo_height' ),
	);

	$map_key   = $location . '_' . $property;
	$fallbacks = $fallback_map[ $map_key ] ?? array();
	$defaults  = array(
		'header_width'  => 200,
		'footer_width'  => 200,
		'hero_width'    => 200,
		'mobile_width'  => 120,
		'header_height' => 100,
		'footer_height' => 100,
		'hero_height'   => 100,
		'mobile_height' => 80,
	);

	foreach ( $fallbacks as $fb ) {
		if ( isset( $options[ $fb ] ) && '' !== $options[ $fb ] ) {
			return (int) $options[ $fb ];
		}
	}

	return $defaults[ $map_key ] ?? 200;
}

/**
 * Get a per-location logo height mode with migration fallback.
 *
 * @param string $location header|footer|hero|mobile
 * @return string 'auto'|'custom'
 */
function jasanika_logo_loc_get_height_mode( string $location ): string {
	$options  = get_option( 'jasanika_settings', array() );
	$key      = 'logo_' . $location . '_height_mode';
	$valid    = array( 'auto', 'custom' );

	if ( isset( $options[ $key ] ) && in_array( $options[ $key ], $valid, true ) ) {
		return $options[ $key ];
	}

	// Migration: fall back to legacy logo_height_mode.
	$legacy = $options['logo_height_mode'] ?? 'auto';
	return in_array( $legacy, $valid, true ) ? $legacy : 'auto';
}

/**
 * Get a per-location logo margin value with migration fallback.
 *
 * @param string $location header|footer|hero|mobile
 * @param string $side     top|right|bottom|left
 * @return int
 */
function jasanika_logo_loc_get_margin( string $location, string $side ): int {
	$options = get_option( 'jasanika_settings', array() );
	$key     = 'logo_' . $location . '_margin_' . $side;

	if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
		return min( 200, max( 0, (int) $options[ $key ] ) );
	}

	// Migration: header margins fall back to legacy logo_margin_*.
	if ( 'header' === $location ) {
		$legacy_key = 'logo_margin_' . $side;
		if ( isset( $options[ $legacy_key ] ) && '' !== $options[ $legacy_key ] ) {
			return min( 200, max( 0, (int) $options[ $legacy_key ] ) );
		}
	}

	return 0;
}

// ---------------------------------------------------------------------------
// Field Renderers – Logo Settings
// ---------------------------------------------------------------------------

/**
 * Render a numeric px input for logo dimensions.
 *
 * @param string $key      Option key.
 * @param int    $value    Current value.
 * @param int    $min      Minimum value.
 * @param int    $max      Maximum value.
 */
function jasanika_logo_settings_render_px_input( string $key, int $value, int $min = 20, int $max = 600 ): void {
	printf(
		'<div class="jasanika-px-field"><input type="number" id="jasanika_%1$s" name="jasanika_settings[%1$s]" value="%2$d" min="%3$d" max="%4$d" class="small-text"> <span class="jasanika-px-unit">px</span></div>',
		esc_attr( $key ),
		$value,
		$min,
		$max
	);
}

/**
 * Render height mode toggle + custom px input for a location.
 *
 * @param string $location header|footer|hero|mobile
 */
function jasanika_logo_settings_render_height_field( string $location ): void {
	$mode    = jasanika_logo_loc_get_height_mode( $location );
	$height  = jasanika_logo_loc_get_numeric( $location, 'height' );
	$modekey = 'logo_' . $location . '_height_mode';
	$hkey    = 'logo_' . $location . '_height';
	$is_custom = 'custom' === $mode;
	?>
	<div class="jasanika-logo-radio-group" style="margin-bottom:6px;">
		<label class="jasanika-logo-radio">
			<input type="radio" name="jasanika_settings[<?php echo esc_attr( $modekey ); ?>]" value="auto" <?php checked( $mode, 'auto' ); ?> class="jasanika-height-mode-radio" data-loc="<?php echo esc_attr( $location ); ?>">
			<?php esc_html_e( 'Auto', 'jasanika' ); ?>
		</label>
		<label class="jasanika-logo-radio">
			<input type="radio" name="jasanika_settings[<?php echo esc_attr( $modekey ); ?>]" value="custom" <?php checked( $mode, 'custom' ); ?> class="jasanika-height-mode-radio" data-loc="<?php echo esc_attr( $location ); ?>">
			<?php esc_html_e( 'Custom', 'jasanika' ); ?>
		</label>
	</div>
	<div class="jasanika-height-custom-field" id="jasanika_height_custom_<?php echo esc_attr( $location ); ?>" style="<?php echo $is_custom ? '' : 'display:none;'; ?>">
		<div class="jasanika-px-field">
			<input type="number" id="jasanika_<?php echo esc_attr( $hkey ); ?>" name="jasanika_settings[<?php echo esc_attr( $hkey ); ?>]" value="<?php echo esc_attr( (string) $height ); ?>" min="20" max="600" class="small-text">
			<span class="jasanika-px-unit">px</span>
		</div>
	</div>
	<?php
}

/**
 * Render margin fields for a location.
 *
 * @param string $location header|footer|hero|mobile
 */
function jasanika_logo_settings_render_margin_fields( string $location ): void {
	$sides  = array(
		'top'    => __( 'Top', 'jasanika' ),
		'right'  => __( 'Right', 'jasanika' ),
		'bottom' => __( 'Bottom', 'jasanika' ),
		'left'   => __( 'Left', 'jasanika' ),
	);
	echo '<div class="jasanika-margin-grid">';
	foreach ( $sides as $side => $label ) {
		$key   = 'logo_' . $location . '_margin_' . $side;
		$value = jasanika_logo_loc_get_margin( $location, $side );
		printf(
			'<label>%1$s <div class="jasanika-px-field"><input type="number" name="jasanika_settings[%2$s]" value="%3$d" min="0" max="200" class="small-text"> <span class="jasanika-px-unit">px</span></div></label>',
			esc_html( $label ),
			esc_attr( $key ),
			$value
		);
	}
	echo '</div>';
}

/**
 * Render a media uploader row for logo settings.
 *
 * @param string $key         Option key.
 * @param string $field_id    HTML input element ID.
 * @param string $media_title Media modal title.
 */
function jasanika_logo_settings_render_media( string $key, string $field_id, string $media_title ): void {
	$options    = get_option( 'jasanika_settings', array() );
	$value      = $options[ $key ] ?? '';
	$preview_id = $field_id . '_preview';
	$remove_id  = $field_id . '_remove';
	$has_image  = ! empty( $value );
	?>
	<div>
		<input type="text" id="<?php echo esc_attr( $field_id ); ?>" name="jasanika_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<button type="button" class="button jasanika-media-upload-btn" data-target="<?php echo esc_attr( $field_id ); ?>" data-preview="<?php echo esc_attr( $preview_id ); ?>" data-title="<?php echo esc_attr( $media_title ); ?>">
			<?php esc_html_e( 'Select Image', 'jasanika' ); ?>
		</button>
		<button type="button" id="<?php echo esc_attr( $remove_id ); ?>" class="button jasanika-media-remove-btn" data-remove="<?php echo esc_attr( $field_id ); ?>" data-preview="<?php echo esc_attr( $preview_id ); ?>" style="<?php echo $has_image ? '' : 'display:none;'; ?>">
			<?php esc_html_e( 'Remove', 'jasanika' ); ?>
		</button>
		<br>
		<img id="<?php echo esc_attr( $preview_id ); ?>" src="<?php echo esc_url( $value ); ?>" style="max-width:100px;margin-top:8px;<?php echo $has_image ? '' : 'display:none;'; ?>" alt="">
	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Preview Renderer
// ---------------------------------------------------------------------------

/**
 * Render a preview canvas for a specific logo location.
 *
 * @param string $location     header|footer|hero|mobile
 * @param string $canvas_id    HTML element ID.
 * @param string $url          Current logo URL.
 * @param string $position     Current position value.
 */
function jasanika_logo_settings_render_preview( string $location, string $canvas_id, string $url, string $position ): void {
	$pos_class = 'pos-' . esc_attr( $position );
	$label     = array(
		'header' => __( 'Header Preview', 'jasanika' ),
		'footer' => __( 'Footer Preview', 'jasanika' ),
		'hero'   => __( 'Hero Preview', 'jasanika' ),
		'mobile' => __( 'Mobile Preview', 'jasanika' ),
	);
	$canvas_class = array(
		'header' => 'jasanika-lsp-header',
		'footer' => 'jasanika-lsp-footer',
		'hero'   => 'jasanika-lsp-hero',
		'mobile' => 'jasanika-lsp-mobile',
	);
	?>
	<div class="jasanika-logo-location-preview">
		<div class="jasanika-lsp-title"><?php echo esc_html( $label[ $location ] ?? $location ); ?></div>
		<div class="jasanika-lsp-canvas">
			<div id="<?php echo esc_attr( $canvas_id ); ?>" class="<?php echo esc_attr( ( $canvas_class[ $location ] ?? '' ) . ' ' . $pos_class ); ?>">
				<?php if ( $url ) : ?>
					<img src="<?php echo esc_url( $url ); ?>" alt="" class="jasanika-lsp-logo">
				<?php else : ?>
					<span class="jasanika-lsp-logo-text">LOGO</span>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Location Section Renderer
// ---------------------------------------------------------------------------

/**
 * Render a full logo location section (fields + preview).
 *
 * @param string $location     header|footer|hero|mobile
 * @param array  $config       Section config: label, url_key, url_field_id, url_media_title, show_key, canvas_id
 * @param array  $pos_options  Position radio options: value => label
 * @param string $pos_key      Settings key for position.
 * @param string $current_pos  Current position value.
 * @param string $url          Current logo URL.
 * @param bool   $is_shown     Visibility flag.
 */
function jasanika_logo_settings_render_location( string $location, array $config, array $pos_options, string $pos_key, string $current_pos, string $url, bool $is_shown ): void {
	$width     = jasanika_logo_loc_get_numeric( $location, 'width' );
	$min_width = 'mobile' === $location ? 30 : 50;
	$max_width = 'mobile' === $location ? 400 : 600;
	?>
	<details class="jasanika-logo-location" open>
		<summary>
			<span class="dashicons dashicons-format-image"></span>
			<?php echo esc_html( $config['label'] ); ?>
		</summary>

		<div class="jasanika-logo-location-body">

			<!-- Fields column -->
			<div class="jasanika-logo-location-fields">

				<!-- Visibility -->
				<div class="jasanika-lsf-row">
					<div class="jasanika-lsf-label"><?php esc_html_e( 'Show Logo', 'jasanika' ); ?></div>
					<div class="jasanika-lsf-control">
						<label class="jasanika-lsf-toggle">
							<input
								type="checkbox"
								name="jasanika_settings[<?php echo esc_attr( $config['show_key'] ); ?>]"
								value="1"
								<?php checked( $is_shown ); ?>
							>
							<?php esc_html_e( 'Enabled', 'jasanika' ); ?>
						</label>
					</div>
				</div>

				<!-- Logo URL -->
				<div class="jasanika-lsf-row">
					<div class="jasanika-lsf-label"><?php esc_html_e( 'Logo Image', 'jasanika' ); ?></div>
					<div class="jasanika-lsf-control">
						<?php jasanika_logo_settings_render_media( $config['url_key'], $config['url_field_id'], $config['url_media_title'] ); ?>
					</div>
				</div>

				<!-- Width -->
				<div class="jasanika-lsf-row">
					<div class="jasanika-lsf-label"><?php esc_html_e( 'Width', 'jasanika' ); ?></div>
					<div class="jasanika-lsf-control">
						<?php jasanika_logo_settings_render_px_input( 'logo_' . $location . '_width', $width, $min_width, $max_width ); ?>
					</div>
				</div>

				<!-- Height -->
				<div class="jasanika-lsf-row">
					<div class="jasanika-lsf-label"><?php esc_html_e( 'Height', 'jasanika' ); ?></div>
					<div class="jasanika-lsf-control">
						<?php jasanika_logo_settings_render_height_field( $location ); ?>
					</div>
				</div>

				<!-- Position -->
				<div class="jasanika-lsf-row">
					<div class="jasanika-lsf-label"><?php esc_html_e( 'Position', 'jasanika' ); ?></div>
					<div class="jasanika-lsf-control">
						<div class="jasanika-logo-radio-group" data-preview-canvas="<?php echo esc_attr( $config['canvas_id'] ); ?>">
							<?php foreach ( $pos_options as $val => $lbl ) : ?>
								<label class="jasanika-logo-radio">
									<input
										type="radio"
										name="jasanika_settings[<?php echo esc_attr( $pos_key ); ?>]"
										value="<?php echo esc_attr( $val ); ?>"
										<?php checked( $current_pos, $val ); ?>
									>
									<?php echo esc_html( $lbl ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- Margins -->
				<div class="jasanika-lsf-row">
					<div class="jasanika-lsf-label"><?php esc_html_e( 'Margins', 'jasanika' ); ?></div>
					<div class="jasanika-lsf-control">
						<?php jasanika_logo_settings_render_margin_fields( $location ); ?>
					</div>
				</div>

			</div><!-- .jasanika-logo-location-fields -->

			<!-- Preview column -->
			<?php jasanika_logo_settings_render_preview( $location, $config['canvas_id'], $url, $current_pos ); ?>

		</div><!-- .jasanika-logo-location-body -->

	</details>
	<?php
}

// ---------------------------------------------------------------------------
// Page Render
// ---------------------------------------------------------------------------

/**
 * Render the Logo Settings admin page.
 */
function jasanika_admin_page_logo_settings(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}

	$options = get_option( 'jasanika_settings', array() );

	// Header logo URL: use logo_url (main key).
	$header_url = (string) ( $options['logo_url']        ?? '' );
	$footer_url = (string) ( $options['footer_logo_url'] ?? '' );
	$hero_url   = (string) ( $options['logo_hero_url']   ?? $options['logo_url'] ?? '' );
	$mobile_url = (string) ( $options['logo_mobile_url'] ?? $options['logo_url'] ?? '' );
	$favicon    = (string) ( $options['favicon_url']     ?? '' );

	// Position values.
	$valid_h   = array( 'left', 'center', 'right' );
	$valid_v   = array( 'top_left', 'top_center', 'top_right', 'center', 'bottom_left', 'bottom_center', 'bottom_right' );

	$header_pos = in_array( $options['logo_header_pos'] ?? '', $valid_h, true ) ? $options['logo_header_pos'] : 'left';
	$footer_pos = in_array( $options['logo_footer_pos'] ?? '', $valid_h, true ) ? $options['logo_footer_pos'] : 'left';
	$hero_pos   = in_array( $options['logo_hero_pos']   ?? '', $valid_v, true ) ? $options['logo_hero_pos']   : 'center';
	$mobile_pos = in_array( $options['logo_mobile_pos'] ?? '', $valid_h, true ) ? $options['logo_mobile_pos'] : 'left';

	// Visibility.
	$defaults_shown = array( 'header' => 1, 'footer' => 1, 'hero' => 0, 'mobile' => 1 );
	$show_header    = isset( $options['logo_show_header'] ) ? (bool) $options['logo_show_header'] : (bool) $defaults_shown['header'];
	$show_footer    = isset( $options['logo_show_footer'] ) ? (bool) $options['logo_show_footer'] : (bool) $defaults_shown['footer'];
	$show_hero      = isset( $options['logo_show_hero'] )   ? (bool) $options['logo_show_hero']   : (bool) $defaults_shown['hero'];
	$show_mobile    = isset( $options['logo_show_mobile'] ) ? (bool) $options['logo_show_mobile'] : (bool) $defaults_shown['mobile'];

	$pos_h = array(
		'left'   => __( 'Left', 'jasanika' ),
		'center' => __( 'Center', 'jasanika' ),
		'right'  => __( 'Right', 'jasanika' ),
	);
	$pos_v = array(
		'top_left'      => __( 'Top Left', 'jasanika' ),
		'top_center'    => __( 'Top Center', 'jasanika' ),
		'top_right'     => __( 'Top Right', 'jasanika' ),
		'center'        => __( 'Center', 'jasanika' ),
		'bottom_left'   => __( 'Bottom Left', 'jasanika' ),
		'bottom_center' => __( 'Bottom Center', 'jasanika' ),
		'bottom_right'  => __( 'Bottom Right', 'jasanika' ),
	);
	?>
	<div class="wrap jasanika-logo-settings-wrap">

		<div class="jasanika-admin-header">
			<span class="dashicons dashicons-format-image"></span>
			<div>
				<h1><?php esc_html_e( 'Logo Settings', 'jasanika' ); ?></h1>
				<p><?php esc_html_e( 'Configure logos independently for each location: Header, Footer, Hero and Mobile.', 'jasanika' ); ?></p>
			</div>
		</div>

		<form method="post" action="options.php">
			<?php settings_fields( 'jasanika_logo_settings_group' ); ?>

			<?php
			// Header Logo.
			jasanika_logo_settings_render_location(
				'header',
				array(
					'label'           => __( 'Header Logo', 'jasanika' ),
					'url_key'         => 'logo_url',
					'url_field_id'    => 'jasanika_logo_url',
					'url_media_title' => __( 'Select Header Logo', 'jasanika' ),
					'show_key'        => 'logo_show_header',
					'canvas_id'       => 'jlsp-header-canvas',
				),
				$pos_h,
				'logo_header_pos',
				$header_pos,
				$header_url,
				$show_header
			);

			// Footer Logo.
			jasanika_logo_settings_render_location(
				'footer',
				array(
					'label'           => __( 'Footer Logo', 'jasanika' ),
					'url_key'         => 'footer_logo_url',
					'url_field_id'    => 'jasanika_footer_logo_url',
					'url_media_title' => __( 'Select Footer Logo', 'jasanika' ),
					'show_key'        => 'logo_show_footer',
					'canvas_id'       => 'jlsp-footer-canvas',
				),
				$pos_h,
				'logo_footer_pos',
				$footer_pos,
				$footer_url,
				$show_footer
			);

			// Hero Logo.
			jasanika_logo_settings_render_location(
				'hero',
				array(
					'label'           => __( 'Hero Logo', 'jasanika' ),
					'url_key'         => 'logo_hero_url',
					'url_field_id'    => 'jasanika_logo_hero_url',
					'url_media_title' => __( 'Select Hero Logo', 'jasanika' ),
					'show_key'        => 'logo_show_hero',
					'canvas_id'       => 'jlsp-hero-canvas',
				),
				$pos_v,
				'logo_hero_pos',
				$hero_pos,
				$hero_url,
				$show_hero
			);

			// Mobile Logo.
			jasanika_logo_settings_render_location(
				'mobile',
				array(
					'label'           => __( 'Mobile Logo', 'jasanika' ),
					'url_key'         => 'logo_mobile_url',
					'url_field_id'    => 'jasanika_logo_mobile_url',
					'url_media_title' => __( 'Select Mobile Logo', 'jasanika' ),
					'show_key'        => 'logo_show_mobile',
					'canvas_id'       => 'jlsp-mobile-canvas',
				),
				$pos_h,
				'logo_mobile_pos',
				$mobile_pos,
				$mobile_url,
				$show_mobile
			);
			?>

			<!-- Favicon -->
			<div class="jasanika-logo-location">
				<div class="jasanika-favicon-wrap">
					<h3><?php esc_html_e( 'Favicon', 'jasanika' ); ?></h3>
					<?php jasanika_logo_settings_render_media( 'favicon_url', 'jasanika_favicon_url', __( 'Select Favicon', 'jasanika' ) ); ?>
					<p class="description"><?php esc_html_e( 'Recommended size: 32×32 or 16×16 px.', 'jasanika' ); ?></p>
				</div>
			</div>

			<?php submit_button( __( 'Save Logo Settings', 'jasanika' ) ); ?>

		</form>

	</div>

	<script>
	( function () {
		// Show/hide custom height field based on mode radio toggle.
		document.addEventListener( 'DOMContentLoaded', function () {
			document.querySelectorAll( '.jasanika-height-mode-radio' ).forEach( function ( radio ) {
				radio.addEventListener( 'change', function () {
					var loc       = this.dataset.loc;
					var container = document.getElementById( 'jasanika_height_custom_' + loc );
					if ( container ) {
						container.style.display = ( 'custom' === this.value ) ? '' : 'none';
					}
				} );
			} );
		} );
	} )();
	</script>
	<?php
}
