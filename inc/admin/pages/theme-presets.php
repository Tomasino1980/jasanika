<?php

/**
 * Jasanika Admin – Theme Presets Page
 *
 * Manage built-in and custom visual presets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_enqueue_scripts', 'jasanika_theme_presets_enqueue' );
add_action( 'admin_init', 'jasanika_theme_presets_handle_requests' );
add_action( 'wp_ajax_jasanika_preset_editor_save', 'jasanika_preset_editor_ajax_save' );
add_action( 'wp_ajax_jasanika_favorite_colors_save', 'jasanika_favorite_colors_ajax_save' );

/**
 * Enqueue Theme Presets assets only on Theme Presets page.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_theme_presets_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-theme-presets' !== $hook ) {
		return;
	}

	$version = wp_get_theme()->get( 'Version' );

	// Base presets stylesheet.
	wp_enqueue_style(
		'jasanika-theme-presets',
		get_template_directory_uri() . '/assets/css/admin/theme-presets.css',
		array(),
		$version
	);

	// Color editor modal stylesheet.
	wp_enqueue_style(
		'jasanika-theme-presets-editor',
		get_template_directory_uri() . '/assets/css/admin/theme-presets-editor.css',
		array( 'jasanika-theme-presets' ),
		$version
	);

	// Color editor – vanilla JS, no jQuery dependency.
	wp_enqueue_script(
		'jasanika-theme-presets-editor',
		get_template_directory_uri() . '/assets/js/admin/theme-presets-editor.js',
		array(),
		$version,
		true
	);

	// Existing presets JS (preview triggers) – loads after editor.
	wp_enqueue_script(
		'jasanika-theme-presets',
		get_template_directory_uri() . '/assets/js/admin/theme-presets.js',
		array( 'jasanika-theme-presets-editor' ),
		$version,
		true
	);

	// Prepare all preset data for the editor JS.
	$all_presets    = jasanika_theme_presets_get_all();
	$presets_for_js = array();
	foreach ( $all_presets as $id => $preset ) {
		$config                = jasanika_theme_presets_sanitize_config( $preset['config'] ?? array() );
		$presets_for_js[ $id ] = array(
			'id'      => $id,
			'name'    => (string) $preset['name'],
			'builtin' => ! empty( $preset['builtin'] ),
			'config'  => $config,
		);
	}

	// Load stored favorite colors.
	$favorites = get_option( 'jasanika_favorite_colors', array() );
	if ( ! is_array( $favorites ) ) {
		$favorites = array();
	}
	$favorites = array_values(
		array_filter(
			array_map( 'sanitize_hex_color', $favorites )
		)
	);

	wp_localize_script(
		'jasanika-theme-presets-editor',
		'jasanikaEditorData',
		array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'nonceSave'      => wp_create_nonce( 'jasanika_preset_editor_save' ),
			'nonceFavorites' => wp_create_nonce( 'jasanika_favorite_colors' ),
			'presets'        => $presets_for_js,
			'favorites'      => $favorites,
			'i18n'           => array(
				'editColors'      => __( 'Edit Colors', 'jasanika' ),
				'close'           => __( 'Close', 'jasanika' ),
				'save'            => __( 'Save', 'jasanika' ),
				'saving'          => __( 'Saving\u2026', 'jasanika' ),
				'cancel'          => __( 'Cancel', 'jasanika' ),
				'reset'           => __( 'Reset', 'jasanika' ),
				'hue'             => __( 'Hue', 'jasanika' ),
				'hex'             => __( 'HEX', 'jasanika' ),
				'hexValue'        => __( 'HEX color value', 'jasanika' ),
				'red'             => __( 'Red', 'jasanika' ),
				'green'           => __( 'Green', 'jasanika' ),
				'blue'            => __( 'Blue', 'jasanika' ),
				'preview'         => __( 'Preview', 'jasanika' ),
				'favoriteColors'  => __( 'Favorite Colors', 'jasanika' ),
				'saveToFavorites' => __( 'Save to favorites', 'jasanika' ),
				'noFavorites'     => __( 'No saved colors yet.', 'jasanika' ),
				'colorSpectrum'   => __( 'Color spectrum. Use arrow keys to adjust.', 'jasanika' ),
				'colorFields'     => __( 'Color fields', 'jasanika' ),
				'primaryColor'    => __( 'Primary Color', 'jasanika' ),
				'secondaryColor'  => __( 'Secondary Color', 'jasanika' ),
				'accentColor'     => __( 'Accent Color', 'jasanika' ),
				'backgroundColor' => __( 'Background Color', 'jasanika' ),
				'textColor'       => __( 'Text Color', 'jasanika' ),
				'saveError'       => __( 'Failed to save. Please try again.', 'jasanika' ),
				'networkError'    => __( 'Network error. Please check your connection.', 'jasanika' ),
			),
		)
	);

	wp_localize_script(
		'jasanika-theme-presets',
		'jasanikaPresetsData',
		array(
			'i18n' => array(
				'invalidColor' => __( 'Invalid color value. Please enter a valid HEX color (#rrggbb).', 'jasanika' ),
			),
		)
	);
}

/**
 * AJAX handler: save preset colors from the color editor modal.
 */
function jasanika_preset_editor_ajax_save(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Insufficient permissions.', 'jasanika' ) );
	}

	$nonce = isset( $_POST['nonce'] ) ? (string) wp_unslash( $_POST['nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'jasanika_preset_editor_save' ) ) {
		wp_send_json_error( __( 'Security check failed.', 'jasanika' ) );
	}

	$preset_id = isset( $_POST['preset_id'] ) ? sanitize_key( (string) wp_unslash( $_POST['preset_id'] ) ) : '';
	if ( '' === $preset_id ) {
		wp_send_json_error( __( 'Invalid preset ID.', 'jasanika' ) );
	}

	// Block editing of built-in presets.
	$builtin = jasanika_theme_presets_builtin();
	if ( isset( $builtin[ $preset_id ] ) ) {
		wp_send_json_error( __( 'Built-in presets cannot be edited.', 'jasanika' ) );
	}

	$raw_config = isset( $_POST['config'] ) && is_array( $_POST['config'] )
		? (array) wp_unslash( $_POST['config'] )
		: array();

	// Validate all required HEX color fields.
	$color_keys = array( 'primary_color', 'secondary_color', 'accent_color', 'background_color', 'text_color' );
	foreach ( $color_keys as $key ) {
		$val = sanitize_hex_color( (string) ( $raw_config[ $key ] ?? '' ) );
		if ( '' === $val ) {
			wp_send_json_error( __( 'Invalid color value.', 'jasanika' ) );
		}
	}

	// Preserve existing button_style.
	$custom       = jasanika_theme_presets_get_custom();
	$button_style = isset( $custom[ $preset_id ]['config']['button_style'] )
		? sanitize_key( (string) $custom[ $preset_id ]['config']['button_style'] )
		: 'solid';

	$config = array(
		'primary_color'    => sanitize_hex_color( (string) ( $raw_config['primary_color'] ?? '' ) ),
		'secondary_color'  => sanitize_hex_color( (string) ( $raw_config['secondary_color'] ?? '' ) ),
		'accent_color'     => sanitize_hex_color( (string) ( $raw_config['accent_color'] ?? '' ) ),
		'background_color' => sanitize_hex_color( (string) ( $raw_config['background_color'] ?? '' ) ),
		'text_color'       => sanitize_hex_color( (string) ( $raw_config['text_color'] ?? '' ) ),
		'button_style'     => $button_style,
	);

	$ok = jasanika_theme_presets_update_custom( $preset_id, $config );
	if ( $ok ) {
		wp_send_json_success( array( 'config' => $config ) );
	} else {
		wp_send_json_error( __( 'Preset not found.', 'jasanika' ) );
	}
}

/**
 * AJAX handler: save the user's favorite colors list.
 */
function jasanika_favorite_colors_ajax_save(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Insufficient permissions.', 'jasanika' ) );
	}

	$nonce = isset( $_POST['nonce'] ) ? (string) wp_unslash( $_POST['nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'jasanika_favorite_colors' ) ) {
		wp_send_json_error( __( 'Security check failed.', 'jasanika' ) );
	}

	$raw_json = isset( $_POST['colors'] ) ? (string) wp_unslash( $_POST['colors'] ) : '[]';
	$colors   = json_decode( $raw_json, true );

	if ( ! is_array( $colors ) ) {
		wp_send_json_error( __( 'Invalid data.', 'jasanika' ) );
	}

	$sanitized = array();
	foreach ( $colors as $color ) {
		$hex = sanitize_hex_color( (string) $color );
		if ( '' !== $hex ) {
			$sanitized[] = $hex;
		}
	}
	$sanitized = array_values( array_unique( array_slice( $sanitized, 0, 20 ) ) );

	update_option( 'jasanika_favorite_colors', $sanitized );
	wp_send_json_success();
}

/**
 * Return page URL for Theme Presets.
 *
 * @param array<string, string> $query_args Optional query args.
 */
function jasanika_theme_presets_page_url( array $query_args = array() ): string {
	$base_url = admin_url( 'admin.php?page=jasanika-theme-presets' );

	if ( empty( $query_args ) ) {
		return $base_url;
	}

	return add_query_arg( $query_args, $base_url );
}

/**
 * Process Theme Presets export and form requests.
 */
function jasanika_theme_presets_handle_requests(): void {
	$page = isset( $_GET['page'] ) ? sanitize_key( (string) wp_unslash( $_GET['page'] ) ) : '';
	if ( 'jasanika-theme-presets' !== $page ) {
		return;
	}

	if ( isset( $_GET['jasanika_preset_export'] ) ) {
		jasanika_theme_presets_handle_export_request();
		return;
	}

	jasanika_theme_presets_handle_post_actions();
}

/**
 * Handle GET export request.
 */
function jasanika_theme_presets_handle_export_request(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}

	$preset_id = sanitize_key( (string) wp_unslash( $_GET['jasanika_preset_export'] ) );
	$nonce     = isset( $_GET['_wpnonce'] ) ? (string) wp_unslash( $_GET['_wpnonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'jasanika-theme-preset-export-' . $preset_id ) ) {
		wp_die( esc_html__( 'Invalid export request.', 'jasanika' ) );
	}

	$json = jasanika_theme_presets_export_json( $preset_id );
	if ( is_wp_error( $json ) ) {
		wp_die( esc_html( $json->get_error_message() ) );
	}

	nocache_headers();
	header( 'Content-Type: application/json; charset=' . get_bloginfo( 'charset' ) );
	header( 'Content-Disposition: attachment; filename="jasanika-preset-' . $preset_id . '.json"' );
	echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}

/**
 * Process Theme Presets form submissions.
 */
function jasanika_theme_presets_handle_post_actions(): void {
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}

	$nonce = isset( $_POST['jasanika_theme_presets_nonce'] ) ? (string) wp_unslash( $_POST['jasanika_theme_presets_nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'jasanika_theme_presets_action' ) ) {
		wp_safe_redirect( jasanika_theme_presets_page_url( array( 'message' => 'invalid_nonce' ) ) );
		exit;
	}

	$action = isset( $_POST['preset_action'] ) ? sanitize_key( (string) wp_unslash( $_POST['preset_action'] ) ) : '';
	$preset = isset( $_POST['preset_id'] ) ? sanitize_key( (string) wp_unslash( $_POST['preset_id'] ) ) : '';

	if ( 'activate' === $action ) {
		$ok = jasanika_theme_presets_activate( $preset );
		wp_safe_redirect( jasanika_theme_presets_page_url( array( 'message' => $ok ? 'activated' : 'activate_failed' ) ) );
		exit;
	}

	if ( 'duplicate' === $action ) {
		$new_id = jasanika_theme_presets_duplicate( $preset );
		$state  = is_wp_error( $new_id ) ? 'duplicate_failed' : 'duplicated';
		wp_safe_redirect( jasanika_theme_presets_page_url( array( 'message' => $state ) ) );
		exit;
	}

	if ( 'delete_custom' === $action ) {
		$ok = jasanika_theme_presets_delete_custom( $preset );
		wp_safe_redirect( jasanika_theme_presets_page_url( array( 'message' => $ok ? 'deleted' : 'delete_failed' ) ) );
		exit;
	}

	if ( 'create_custom_from_current' === $action ) {
		$name   = isset( $_POST['custom_preset_name'] ) ? sanitize_text_field( (string) wp_unslash( $_POST['custom_preset_name'] ) ) : '';
		$config = jasanika_theme_presets_build_config_from_theme_settings();
		jasanika_theme_presets_create_custom( $name, $config );
		wp_safe_redirect( jasanika_theme_presets_page_url( array( 'message' => 'custom_created' ) ) );
		exit;
	}

	if ( 'save_colors' === $action ) {
		$builtin = jasanika_theme_presets_builtin();
		if ( isset( $builtin[ $preset ] ) ) {
			wp_safe_redirect( jasanika_theme_presets_page_url( array( 'message' => 'save_colors_failed' ) ) );
			exit;
		}

		$raw_config = isset( $_POST['config'] ) && is_array( $_POST['config'] )
			? (array) wp_unslash( $_POST['config'] )
			: array();

		$color_keys = array( 'primary_color', 'secondary_color', 'accent_color', 'background_color', 'text_color' );
		foreach ( $color_keys as $key ) {
			$val = sanitize_hex_color( (string) ( $raw_config[ $key ] ?? '' ) );
			if ( '' === $val ) {
				wp_safe_redirect( jasanika_theme_presets_page_url( array( 'message' => 'save_colors_failed' ) ) );
				exit;
			}
		}

		$config = array(
			'primary_color'    => sanitize_hex_color( (string) ( $raw_config['primary_color'] ?? '' ) ),
			'secondary_color'  => sanitize_hex_color( (string) ( $raw_config['secondary_color'] ?? '' ) ),
			'accent_color'     => sanitize_hex_color( (string) ( $raw_config['accent_color'] ?? '' ) ),
			'background_color' => sanitize_hex_color( (string) ( $raw_config['background_color'] ?? '' ) ),
			'text_color'       => sanitize_hex_color( (string) ( $raw_config['text_color'] ?? '' ) ),
			'button_style'     => sanitize_key( (string) ( $raw_config['button_style'] ?? 'solid' ) ),
		);

		$ok = jasanika_theme_presets_update_custom( $preset, $config );
		wp_safe_redirect( jasanika_theme_presets_page_url( array( 'message' => $ok ? 'saved_colors' : 'save_colors_failed' ) ) );
		exit;
	}

	if ( 'import' === $action ) {
		$json = '';

		if ( ! empty( $_FILES['preset_import_file']['tmp_name'] ) ) {
			$file = $_FILES['preset_import_file'];
			if ( isset( $file['error'] ) && UPLOAD_ERR_OK === (int) $file['error'] ) {
				$content = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				if ( false !== $content ) {
					$json = (string) $content;
				}
			}
		}

		if ( '' === $json && isset( $_POST['import_json'] ) ) {
			$json = (string) wp_unslash( $_POST['import_json'] );
		}

		$result = jasanika_theme_presets_import_json( $json );
		$state  = is_wp_error( $result ) ? 'import_failed' : 'imported';
		wp_safe_redirect( jasanika_theme_presets_page_url( array( 'message' => $state ) ) );
		exit;
	}
}

/**
 * Render status notice.
 */
function jasanika_theme_presets_render_notice(): void {
	$message = isset( $_GET['message'] ) ? sanitize_key( (string) wp_unslash( $_GET['message'] ) ) : '';
	if ( '' === $message ) {
		return;
	}

	$map = array(
		'activated'       => array( 'success', __( 'Preset activated.', 'jasanika' ) ),
		'duplicated'      => array( 'success', __( 'Preset duplicated as custom preset.', 'jasanika' ) ),
		'deleted'         => array( 'success', __( 'Custom preset deleted.', 'jasanika' ) ),
		'custom_created'  => array( 'success', __( 'Custom preset created from current Theme Settings.', 'jasanika' ) ),
		'imported'        => array( 'success', __( 'Preset imported successfully.', 'jasanika' ) ),
		'saved_colors'    => array( 'success', __( 'Preset colors saved.', 'jasanika' ) ),
		'invalid_nonce'   => array( 'error', __( 'Security check failed.', 'jasanika' ) ),
		'activate_failed' => array( 'error', __( 'Preset activation failed.', 'jasanika' ) ),
		'duplicate_failed'=> array( 'error', __( 'Preset duplication failed.', 'jasanika' ) ),
		'delete_failed'   => array( 'error', __( 'Preset deletion failed.', 'jasanika' ) ),
		'import_failed'   => array( 'error', __( 'Preset import failed. Check JSON structure and version.', 'jasanika' ) ),
		'save_colors_failed' => array( 'error', __( 'Failed to save colors. Invalid preset or color values.', 'jasanika' ) ),
	);

	if ( ! isset( $map[ $message ] ) ) {
		return;
	}

	$type = $map[ $message ][0];
	$text = $map[ $message ][1];

	printf(
		'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
		esc_attr( $type ),
		esc_html( $text )
	);
}

/**
 * Render the Theme Presets page.
 */
function jasanika_admin_page_theme_presets(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}

	$all       = jasanika_theme_presets_get_all();
	$active_id = jasanika_theme_presets_get_active_id();
	?>
	<div class="wrap jasanika-presets">
		<div class="jasanika-presets__header">
			<span class="dashicons dashicons-art jasanika-presets__header-icon"></span>
			<div>
				<h1 class="jasanika-presets__title"><?php esc_html_e( 'Theme Presets', 'jasanika' ); ?></h1>
				<p class="jasanika-presets__subtitle"><?php esc_html_e( 'Switch visual styles, preview changes, and manage custom preset library.', 'jasanika' ); ?></p>
			</div>
		</div>

		<?php jasanika_theme_presets_render_notice(); ?>

		<div class="jasanika-presets__preview" id="jasanika-preset-live-preview">
			<div class="jasanika-presets__preview-head">
				<strong><?php esc_html_e( 'Live Preview', 'jasanika' ); ?></strong>
				<span><?php esc_html_e( 'Header, button, card and footer preview before activation.', 'jasanika' ); ?></span>
			</div>
			<div class="jasanika-presets__preview-header"><?php esc_html_e( 'Header Preview', 'jasanika' ); ?></div>
			<div class="jasanika-presets__preview-body">
				<button type="button" class="jasanika-presets__preview-button"><?php esc_html_e( 'Button Preview', 'jasanika' ); ?></button>
				<div class="jasanika-presets__preview-card"><?php esc_html_e( 'Card Preview', 'jasanika' ); ?></div>
			</div>
			<div class="jasanika-presets__preview-footer"><?php esc_html_e( 'Footer Preview', 'jasanika' ); ?></div>
		</div>

		<div class="jasanika-presets__grid">
			<?php foreach ( $all as $preset_id => $preset ) : ?>
				<?php
				$is_active  = ( $active_id === $preset_id );
				$is_builtin = ! empty( $preset['builtin'] );
				$config     = jasanika_theme_presets_sanitize_config( $preset['config'] ?? array() );
				$export_url = wp_nonce_url(
					jasanika_theme_presets_page_url(
						array(
							'jasanika_preset_export' => $preset_id,
						)
					),
					'jasanika-theme-preset-export-' . $preset_id
				);
				?>
				<div class="jasanika-presets__card <?php echo $is_builtin ? 'jasanika-presets__card--builtin' : 'jasanika-presets__card--custom'; ?>">
					<div class="jasanika-presets__card-head">
						<h2><?php echo esc_html( (string) $preset['name'] ); ?></h2>
						<div class="jasanika-presets__badges">
							<?php if ( $is_builtin ) : ?>
								<span class="jasanika-presets__badge"><?php esc_html_e( 'Built-in', 'jasanika' ); ?></span>
							<?php endif; ?>
							<?php if ( $is_active ) : ?>
								<span class="jasanika-presets__badge jasanika-presets__badge--active"><?php esc_html_e( 'Active', 'jasanika' ); ?></span>
							<?php endif; ?>
						</div>
					</div>

					<div class="jasanika-presets__swatches">
						<span style="--swatch-color: <?php echo esc_attr( $config['primary_color'] ); ?>;" title="<?php esc_attr_e( 'Primary', 'jasanika' ); ?>"></span>
						<span style="--swatch-color: <?php echo esc_attr( $config['secondary_color'] ); ?>;" title="<?php esc_attr_e( 'Secondary', 'jasanika' ); ?>"></span>
						<span style="--swatch-color: <?php echo esc_attr( $config['accent_color'] ); ?>;" title="<?php esc_attr_e( 'Accent', 'jasanika' ); ?>"></span>
						<span style="--swatch-color: <?php echo esc_attr( $config['background_color'] ); ?>;" title="<?php esc_attr_e( 'Background', 'jasanika' ); ?>"></span>
						<span style="--swatch-color: <?php echo esc_attr( $config['text_color'] ); ?>;" title="<?php esc_attr_e( 'Text', 'jasanika' ); ?>"></span>
					</div>

					<dl class="jasanika-presets__config">
						<dt><?php esc_html_e( 'Button Style', 'jasanika' ); ?></dt>
						<dd><?php echo esc_html( ucfirst( $config['button_style'] ) ); ?></dd>
					</dl>

					<div class="jasanika-presets__actions">
						<button
							type="button"
							class="button jasanika-preview-trigger"
							data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>"
						><?php esc_html_e( 'Preview', 'jasanika' ); ?></button>

						<form method="post">
							<?php wp_nonce_field( 'jasanika_theme_presets_action', 'jasanika_theme_presets_nonce' ); ?>
							<input type="hidden" name="preset_action" value="activate">
							<input type="hidden" name="preset_id" value="<?php echo esc_attr( $preset_id ); ?>">
							<button type="submit" class="button button-primary" <?php disabled( $is_active ); ?>>
								<?php esc_html_e( 'Activate', 'jasanika' ); ?>
							</button>
						</form>

						<form method="post">
							<?php wp_nonce_field( 'jasanika_theme_presets_action', 'jasanika_theme_presets_nonce' ); ?>
							<input type="hidden" name="preset_action" value="duplicate">
							<input type="hidden" name="preset_id" value="<?php echo esc_attr( $preset_id ); ?>">
							<button type="submit" class="button"><?php esc_html_e( 'Duplicate', 'jasanika' ); ?></button>
						</form>

						<a class="button" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Export JSON', 'jasanika' ); ?></a>

						<?php if ( ! $is_builtin ) : ?>
							<button
								type="button"
								class="button jasanika-open-color-editor"
								data-preset-id="<?php echo esc_attr( $preset_id ); ?>"
							><?php esc_html_e( 'Edit Colors', 'jasanika' ); ?></button>
						<?php endif; ?>

						<?php if ( ! $is_builtin ) : ?>
							<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this custom preset?', 'jasanika' ) ); ?>');">
								<?php wp_nonce_field( 'jasanika_theme_presets_action', 'jasanika_theme_presets_nonce' ); ?>
								<input type="hidden" name="preset_action" value="delete_custom">
								<input type="hidden" name="preset_id" value="<?php echo esc_attr( $preset_id ); ?>">
								<button type="submit" class="button button-link-delete"><?php esc_html_e( 'Delete', 'jasanika' ); ?></button>
							</form>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="jasanika-presets__tools">
			<div class="jasanika-presets__tool">
				<h2><?php esc_html_e( 'Create Custom Preset', 'jasanika' ); ?></h2>
				<p><?php esc_html_e( 'Save current Theme Settings colors as a reusable preset.', 'jasanika' ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'jasanika_theme_presets_action', 'jasanika_theme_presets_nonce' ); ?>
					<input type="hidden" name="preset_action" value="create_custom_from_current">
					<input
						type="text"
						name="custom_preset_name"
						class="regular-text"
						placeholder="<?php esc_attr_e( 'My Custom Preset', 'jasanika' ); ?>"
					>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Save as Preset', 'jasanika' ); ?></button>
				</form>
			</div>

			<div class="jasanika-presets__tool">
				<h2><?php esc_html_e( 'Import Preset', 'jasanika' ); ?></h2>
				<p><?php esc_html_e( 'Import a preset JSON file (version 1.0).', 'jasanika' ); ?></p>
				<form method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'jasanika_theme_presets_action', 'jasanika_theme_presets_nonce' ); ?>
					<input type="hidden" name="preset_action" value="import">
					<p>
						<input type="file" name="preset_import_file" accept=".json,application/json">
					</p>
					<p><?php esc_html_e( 'Or paste JSON:', 'jasanika' ); ?></p>
					<textarea name="import_json" rows="8" class="large-text code"></textarea>
					<p>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Import Preset', 'jasanika' ); ?></button>
					</p>
				</form>
			</div>
		</div>
	</div>
	<?php
}
