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

	wp_enqueue_style( 'wp-color-picker' );

	wp_enqueue_style(
		'jasanika-theme-presets',
		get_template_directory_uri() . '/assets/css/admin/theme-presets.css',
		array( 'wp-color-picker' ),
		$version
	);

	wp_enqueue_script(
		'jasanika-theme-presets',
		get_template_directory_uri() . '/assets/js/admin/theme-presets.js',
		array( 'jquery', 'wp-color-picker' ),
		$version,
		true
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
								class="button jasanika-presets__edit-toggle"
								data-panel="jasanika-edit-panel-<?php echo esc_attr( $preset_id ); ?>"
								data-label-edit="<?php esc_attr_e( 'Edit Colors', 'jasanika' ); ?>"
								data-label-close="<?php esc_attr_e( 'Close Editor', 'jasanika' ); ?>"
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

					<?php if ( ! $is_builtin ) : ?>
						<?php
						$color_fields = array(
							'primary_color'    => __( 'Primary Color', 'jasanika' ),
							'secondary_color'  => __( 'Secondary Color', 'jasanika' ),
							'accent_color'     => __( 'Accent Color', 'jasanika' ),
							'background_color' => __( 'Background Color', 'jasanika' ),
							'text_color'       => __( 'Text Color', 'jasanika' ),
						);
						?>
						<div
							id="jasanika-edit-panel-<?php echo esc_attr( $preset_id ); ?>"
							class="jasanika-presets__edit-panel"
							style="display:none;"
						>
							<form method="post" class="jasanika-presets__edit-form">
								<?php wp_nonce_field( 'jasanika_theme_presets_action', 'jasanika_theme_presets_nonce' ); ?>
								<input type="hidden" name="preset_action" value="save_colors">
								<input type="hidden" name="preset_id" value="<?php echo esc_attr( $preset_id ); ?>">
								<input type="hidden" name="config[button_style]" value="<?php echo esc_attr( $config['button_style'] ); ?>">

								<div class="jasanika-presets__color-fields">
									<?php foreach ( $color_fields as $field_key => $field_label ) : ?>
										<div class="jasanika-presets__color-row">
											<label class="jasanika-presets__color-label"><?php echo esc_html( $field_label ); ?></label>
											<input
												type="text"
												name="config[<?php echo esc_attr( $field_key ); ?>]"
												value="<?php echo esc_attr( $config[ $field_key ] ); ?>"
												class="jasanika-color-picker"
												data-field="<?php echo esc_attr( $field_key ); ?>"
												data-default-color="<?php echo esc_attr( $config[ $field_key ] ); ?>"
											>
										</div>
									<?php endforeach; ?>
								</div>

								<div class="jasanika-presets__edit-actions">
									<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Colors', 'jasanika' ); ?></button>
									<span class="jasanika-presets__validation-error" style="display:none;color:#cc1818;margin-left:8px;"></span>
								</div>
							</form>
						</div>
					<?php endif; ?>
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
