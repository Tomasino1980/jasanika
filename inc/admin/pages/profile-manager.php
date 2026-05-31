<?php

/**
 * Jasanika Admin – Profile Manager Page
 *
 * Renders the Profile Manager admin page and handles all profile actions:
 *   – Create Profile
 *   – Activate Profile
 *   – Duplicate Profile
 *   – Delete Profile
 *   – Export Profile (JSON download)
 *   – Import Profile (upload JSON with preview & comparison)
 *
 * Handlers:
 *   admin_post_jasanika_profile_create
 *   admin_post_jasanika_profile_activate
 *   admin_post_jasanika_profile_duplicate
 *   admin_post_jasanika_profile_delete
 *   admin_post_jasanika_profile_export
 *   admin_post_jasanika_profile_preview_import
 *   admin_post_jasanika_profile_import_confirm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_enqueue_scripts',                         'jasanika_profile_manager_enqueue' );
add_action( 'admin_post_jasanika_profile_create',            'jasanika_handle_profile_create' );
add_action( 'admin_post_jasanika_profile_activate',          'jasanika_handle_profile_activate' );
add_action( 'admin_post_jasanika_profile_duplicate',         'jasanika_handle_profile_duplicate' );
add_action( 'admin_post_jasanika_profile_delete',            'jasanika_handle_profile_delete' );
add_action( 'admin_post_jasanika_profile_export',            'jasanika_handle_profile_export' );
add_action( 'admin_post_jasanika_profile_preview_import',    'jasanika_handle_profile_preview_import' );
add_action( 'admin_post_jasanika_profile_import_confirm',    'jasanika_handle_profile_import_confirm' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue Profile Manager stylesheet only on its own admin page.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_profile_manager_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-profile-manager' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'jasanika-profile-manager',
		get_template_directory_uri() . '/assets/css/admin/profile-manager.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}

// ---------------------------------------------------------------------------
// Action Handlers
// ---------------------------------------------------------------------------

/**
 * Handle Create Profile.
 */
function jasanika_handle_profile_create(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	check_admin_referer( 'jasanika_profile_create' );

	$name        = sanitize_text_field( wp_unslash( $_POST['profile_name'] ?? '' ) );
	$description = sanitize_textarea_field( wp_unslash( $_POST['profile_description'] ?? '' ) );

	if ( '' === $name ) {
		wp_safe_redirect( add_query_arg( 'pm_notice', 'name_required', jasanika_profile_page_url() ) );
		exit;
	}

	jasanika_profile_create( $name, $description );

	wp_safe_redirect( add_query_arg( 'pm_notice', 'created', jasanika_profile_page_url() ) );
	exit;
}

/**
 * Handle Activate Profile.
 */
function jasanika_handle_profile_activate(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	$profile_id = sanitize_key( wp_unslash( $_POST['profile_id'] ?? '' ) );
	check_admin_referer( 'jasanika_profile_activate_' . $profile_id );

	$result = jasanika_activate_profile( $profile_id );
	$notice = $result ? 'activated' : 'not_found';

	wp_safe_redirect( add_query_arg( 'pm_notice', $notice, jasanika_profile_page_url() ) );
	exit;
}

/**
 * Handle Duplicate Profile.
 */
function jasanika_handle_profile_duplicate(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	$profile_id = sanitize_key( wp_unslash( $_POST['profile_id'] ?? '' ) );
	check_admin_referer( 'jasanika_profile_duplicate_' . $profile_id );

	$new_id = jasanika_profile_duplicate( $profile_id );
	$notice = null !== $new_id ? 'duplicated' : 'not_found';

	wp_safe_redirect( add_query_arg( 'pm_notice', $notice, jasanika_profile_page_url() ) );
	exit;
}

/**
 * Handle Delete Profile.
 */
function jasanika_handle_profile_delete(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	$profile_id = sanitize_key( wp_unslash( $_POST['profile_id'] ?? '' ) );
	check_admin_referer( 'jasanika_profile_delete_' . $profile_id );

	$result = jasanika_profile_delete( $profile_id );
	$notice = $result ? 'deleted' : 'not_found';

	wp_safe_redirect( add_query_arg( 'pm_notice', $notice, jasanika_profile_page_url() ) );
	exit;
}

/**
 * Handle Export Profile — streams the JSON file as a download.
 */
function jasanika_handle_profile_export(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	$profile_id = sanitize_key( wp_unslash( $_POST['profile_id'] ?? '' ) );
	check_admin_referer( 'jasanika_profile_export_' . $profile_id );

	$export = jasanika_profile_build_export( $profile_id );

	if ( null === $export ) {
		wp_safe_redirect( add_query_arg( 'pm_notice', 'not_found', jasanika_profile_page_url() ) );
		exit;
	}

	$profiles = jasanika_get_profiles();
	$name     = sanitize_file_name( $profiles[ $profile_id ]['name'] ?? 'profile' );
	$filename = 'jasanika-profile-' . strtolower( str_replace( ' ', '-', $name ) ) . '.json';
	$json     = wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Cache-Control: no-cache, no-store, must-revalidate' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $json;
	exit;
}

/**
 * Handle Profile Preview Import — validates JSON and stores comparison data in transient.
 */
function jasanika_handle_profile_preview_import(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	check_admin_referer( 'jasanika_profile_preview_import' );

	if ( empty( $_FILES['profile_file']['tmp_name'] ) ) {
		wp_safe_redirect( add_query_arg( 'pm_notice', 'no_file', jasanika_profile_page_url() ) );
		exit;
	}

	$file     = $_FILES['profile_file'];
	$ext      = strtolower( pathinfo( sanitize_file_name( $file['name'] ), PATHINFO_EXTENSION ) );

	if ( 'json' !== $ext ) {
		wp_safe_redirect( add_query_arg( 'pm_notice', 'invalid_format', jasanika_profile_page_url() ) );
		exit;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$raw = file_get_contents( $file['tmp_name'] );

	if ( false === $raw ) {
		wp_safe_redirect( add_query_arg( 'pm_notice', 'read_error', jasanika_profile_page_url() ) );
		exit;
	}

	$data = json_decode( $raw, true );

	if ( ! is_array( $data ) ) {
		wp_safe_redirect( add_query_arg( 'pm_notice', 'json_error', jasanika_profile_page_url() ) );
		exit;
	}

	$validation = jasanika_profile_validate_import( $data );

	if ( is_wp_error( $validation ) ) {
		wp_safe_redirect( add_query_arg(
			array(
				'pm_notice'  => 'import_invalid',
				'pm_message' => urlencode( $validation->get_error_message() ),
			),
			jasanika_profile_page_url()
		) );
		exit;
	}

	// Store validated data in a transient for 30 minutes.
	$transient_key = 'jasanika_profile_import_' . get_current_user_id();
	set_transient( $transient_key, $data, 30 * MINUTE_IN_SECONDS );

	wp_safe_redirect( add_query_arg( 'pm_compare', '1', jasanika_profile_page_url() ) );
	exit;
}

/**
 * Handle Profile Import Confirm — saves the previewed profile from transient.
 */
function jasanika_handle_profile_import_confirm(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	check_admin_referer( 'jasanika_profile_import_confirm' );

	$transient_key = 'jasanika_profile_import_' . get_current_user_id();
	$data          = get_transient( $transient_key );

	if ( ! is_array( $data ) ) {
		wp_safe_redirect( add_query_arg( 'pm_notice', 'import_expired', jasanika_profile_page_url() ) );
		exit;
	}

	jasanika_profile_save_from_import( $data );
	delete_transient( $transient_key );

	wp_safe_redirect( add_query_arg( 'pm_notice', 'imported', jasanika_profile_page_url() ) );
	exit;
}

// ---------------------------------------------------------------------------
// Page Renderer
// ---------------------------------------------------------------------------

/**
 * Render the Profile Manager admin page.
 */
function jasanika_admin_page_profile_manager(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	$profiles       = jasanika_get_profiles();
	$active_id      = (string) get_option( JASANIKA_ACTIVE_PROFILE_OPTION, '' );
	$show_compare   = ! empty( $_GET['pm_compare'] );
	$notice_type    = sanitize_key( $_GET['pm_notice'] ?? '' );
	$notice_message = sanitize_text_field( urldecode( $_GET['pm_message'] ?? '' ) );

	// Load comparison data if in comparison mode.
	$import_preview = null;
	if ( $show_compare ) {
		$transient_key  = 'jasanika_profile_import_' . get_current_user_id();
		$import_preview = get_transient( $transient_key );
		if ( ! is_array( $import_preview ) ) {
			$import_preview = null;
			$show_compare   = false;
		}
	}

	?>
	<div class="wrap jasanika-profile-manager">

		<!-- Header -->
		<div class="jasanika-pm__header">
			<span class="dashicons dashicons-id jasanika-pm__header-icon"></span>
			<div>
				<h1 class="jasanika-pm__title"><?php esc_html_e( 'Profile Manager', 'jasanika' ); ?></h1>
				<p class="jasanika-pm__subtitle"><?php esc_html_e( 'Save, export, import and switch complete Jasanika configurations.', 'jasanika' ); ?></p>
			</div>
		</div>

		<!-- Notices -->
		<?php jasanika_profile_manager_render_notice( $notice_type, $notice_message ); ?>

		<!-- Comparison Panel -->
		<?php if ( $show_compare && is_array( $import_preview ) ) : ?>
			<?php jasanika_profile_manager_render_comparison( $import_preview, $profiles, $active_id ); ?>
		<?php endif; ?>

		<!-- Main two-column layout -->
		<div class="jasanika-pm__layout">

			<!-- LEFT: Profile List + Create Form -->
			<div class="jasanika-pm__col-main">

				<!-- Profile List -->
				<div class="jasanika-pm__card">
					<h2 class="jasanika-pm__card-title">
						<span class="dashicons dashicons-list-view"></span>
						<?php esc_html_e( 'Profiles', 'jasanika' ); ?>
						<span class="jasanika-pm__count"><?php echo esc_html( (string) count( $profiles ) ); ?></span>
					</h2>

					<?php if ( empty( $profiles ) ) : ?>
						<p class="jasanika-pm__empty"><?php esc_html_e( 'No profiles yet. Create your first profile below.', 'jasanika' ); ?></p>
					<?php else : ?>
						<div class="jasanika-pm__profiles">
							<?php foreach ( $profiles as $profile_id => $profile ) :
								$is_active = ( $profile_id === $active_id );
								?>
								<div class="jasanika-pm__profile-card <?php echo $is_active ? 'jasanika-pm__profile-card--active' : ''; ?>">

									<!-- Profile Info -->
									<div class="jasanika-pm__profile-info">
										<div class="jasanika-pm__profile-name">
											<?php if ( $is_active ) : ?>
												<span class="jasanika-pm__active-badge">
													<span class="dashicons dashicons-yes-alt"></span>
													<?php esc_html_e( 'Active', 'jasanika' ); ?>
												</span>
											<?php endif; ?>
											<?php echo esc_html( $profile['name'] ); ?>
										</div>
										<details class="jasanika-pm__profile-preview">
											<summary class="jasanika-pm__preview-toggle">
												<?php esc_html_e( 'Profile Details', 'jasanika' ); ?>
											</summary>
											<div class="jasanika-pm__preview-body">
												<div class="jasanika-pm__preview-grid">
													<div class="jasanika-pm__preview-row">
														<span class="jasanika-pm__preview-label"><?php esc_html_e( 'Version', 'jasanika' ); ?></span>
														<span class="jasanika-pm__preview-value"><?php echo esc_html( $profile['version'] ?? '—' ); ?></span>
													</div>
													<div class="jasanika-pm__preview-row">
														<span class="jasanika-pm__preview-label"><?php esc_html_e( 'Created', 'jasanika' ); ?></span>
														<span class="jasanika-pm__preview-value"><?php echo esc_html( $profile['created_at'] ?? '—' ); ?></span>
													</div>
													<?php if ( ! empty( $profile['description'] ) ) : ?>
													<div class="jasanika-pm__preview-row jasanika-pm__preview-row--full">
														<span class="jasanika-pm__preview-label"><?php esc_html_e( 'Description', 'jasanika' ); ?></span>
														<span class="jasanika-pm__preview-value"><?php echo esc_html( $profile['description'] ); ?></span>
													</div>
													<?php endif; ?>
													<div class="jasanika-pm__preview-row">
														<span class="jasanika-pm__preview-label"><?php esc_html_e( 'Settings', 'jasanika' ); ?></span>
														<span class="jasanika-pm__preview-value"><?php echo esc_html( (string) jasanika_profile_count_settings( $profile['settings'] ?? array() ) ); ?></span>
													</div>
												</div>
											</div>
										</details>
									</div>

									<!-- Quick Actions -->
									<div class="jasanika-pm__profile-actions">

										<?php if ( ! $is_active ) : ?>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
											<input type="hidden" name="action" value="jasanika_profile_activate">
											<input type="hidden" name="profile_id" value="<?php echo esc_attr( $profile_id ); ?>">
											<?php wp_nonce_field( 'jasanika_profile_activate_' . $profile_id ); ?>
											<button type="submit" class="jasanika-pm__btn jasanika-pm__btn--activate">
												<span class="dashicons dashicons-controls-play"></span>
												<?php esc_html_e( 'Activate', 'jasanika' ); ?>
											</button>
										</form>
										<?php endif; ?>

										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
											<input type="hidden" name="action" value="jasanika_profile_export">
											<input type="hidden" name="profile_id" value="<?php echo esc_attr( $profile_id ); ?>">
											<?php wp_nonce_field( 'jasanika_profile_export_' . $profile_id ); ?>
											<button type="submit" class="jasanika-pm__btn jasanika-pm__btn--export">
												<span class="dashicons dashicons-download"></span>
												<?php esc_html_e( 'Export', 'jasanika' ); ?>
											</button>
										</form>

										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
											<input type="hidden" name="action" value="jasanika_profile_duplicate">
											<input type="hidden" name="profile_id" value="<?php echo esc_attr( $profile_id ); ?>">
											<?php wp_nonce_field( 'jasanika_profile_duplicate_' . $profile_id ); ?>
											<button type="submit" class="jasanika-pm__btn jasanika-pm__btn--duplicate">
												<span class="dashicons dashicons-admin-page"></span>
												<?php esc_html_e( 'Duplicate', 'jasanika' ); ?>
											</button>
										</form>

										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
											  onsubmit="return confirm('<?php echo esc_js( __( 'Delete this profile? This cannot be undone.', 'jasanika' ) ); ?>');">
											<input type="hidden" name="action" value="jasanika_profile_delete">
											<input type="hidden" name="profile_id" value="<?php echo esc_attr( $profile_id ); ?>">
											<?php wp_nonce_field( 'jasanika_profile_delete_' . $profile_id ); ?>
											<button type="submit" class="jasanika-pm__btn jasanika-pm__btn--delete">
												<span class="dashicons dashicons-trash"></span>
												<?php esc_html_e( 'Delete', 'jasanika' ); ?>
											</button>
										</form>

									</div><!-- /.jasanika-pm__profile-actions -->
								</div><!-- /.jasanika-pm__profile-card -->
							<?php endforeach; ?>
						</div><!-- /.jasanika-pm__profiles -->
					<?php endif; ?>
				</div><!-- /.jasanika-pm__card (profiles) -->

				<!-- Create New Profile -->
				<div class="jasanika-pm__card">
					<h2 class="jasanika-pm__card-title">
						<span class="dashicons dashicons-plus-alt"></span>
						<?php esc_html_e( 'Create New Profile', 'jasanika' ); ?>
					</h2>
					<p class="jasanika-pm__card-desc">
						<?php esc_html_e( 'Save the current live settings as a new named profile.', 'jasanika' ); ?>
					</p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="jasanika-pm__form">
						<input type="hidden" name="action" value="jasanika_profile_create">
						<?php wp_nonce_field( 'jasanika_profile_create' ); ?>

						<div class="jasanika-pm__form-row">
							<label for="pm_profile_name" class="jasanika-pm__label">
								<?php esc_html_e( 'Profile Name', 'jasanika' ); ?>
								<span class="jasanika-pm__required">*</span>
							</label>
							<input type="text" id="pm_profile_name" name="profile_name"
								   class="jasanika-pm__input" required
								   placeholder="<?php esc_attr_e( 'e.g. Christmas Campaign', 'jasanika' ); ?>">
						</div>

						<div class="jasanika-pm__form-row">
							<label for="pm_profile_desc" class="jasanika-pm__label">
								<?php esc_html_e( 'Description', 'jasanika' ); ?>
							</label>
							<textarea id="pm_profile_desc" name="profile_description"
									  class="jasanika-pm__textarea" rows="3"
									  placeholder="<?php esc_attr_e( 'Optional description…', 'jasanika' ); ?>"></textarea>
						</div>

						<div class="jasanika-pm__form-actions">
							<button type="submit" class="jasanika-pm__btn jasanika-pm__btn--primary">
								<span class="dashicons dashicons-saved"></span>
								<?php esc_html_e( 'Save Current Settings as Profile', 'jasanika' ); ?>
							</button>
						</div>
					</form>
				</div><!-- /.jasanika-pm__card (create) -->

			</div><!-- /.jasanika-pm__col-main -->

			<!-- RIGHT: Import -->
			<div class="jasanika-pm__col-side">

				<div class="jasanika-pm__card">
					<h2 class="jasanika-pm__card-title">
						<span class="dashicons dashicons-upload"></span>
						<?php esc_html_e( 'Import Profile', 'jasanika' ); ?>
					</h2>
					<p class="jasanika-pm__card-desc">
						<?php esc_html_e( 'Upload a Jasanika profile JSON file. You will see a comparison before saving.', 'jasanika' ); ?>
					</p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
						  enctype="multipart/form-data" class="jasanika-pm__form">
						<input type="hidden" name="action" value="jasanika_profile_preview_import">
						<?php wp_nonce_field( 'jasanika_profile_preview_import' ); ?>

						<div class="jasanika-pm__form-row">
							<label for="pm_profile_file" class="jasanika-pm__label">
								<?php esc_html_e( 'Profile File (.json)', 'jasanika' ); ?>
							</label>
							<input type="file" id="pm_profile_file" name="profile_file"
								   accept=".json,application/json"
								   class="jasanika-pm__file-input">
							<p class="jasanika-pm__hint">
								<?php esc_html_e( 'Only jasanika-profile-*.json files are accepted.', 'jasanika' ); ?>
							</p>
						</div>

						<div class="jasanika-pm__form-actions">
							<button type="submit" class="jasanika-pm__btn jasanika-pm__btn--secondary">
								<span class="dashicons dashicons-search"></span>
								<?php esc_html_e( 'Preview & Compare', 'jasanika' ); ?>
							</button>
						</div>
					</form>
				</div>

			</div><!-- /.jasanika-pm__col-side -->

		</div><!-- /.jasanika-pm__layout -->

	</div><!-- /.wrap -->
	<?php
}

// ---------------------------------------------------------------------------
// Sub-renderers
// ---------------------------------------------------------------------------

/**
 * Render a notice banner based on the pm_notice query parameter.
 *
 * @param string $notice_type    Notice key.
 * @param string $notice_message Optional custom message.
 */
function jasanika_profile_manager_render_notice( string $notice_type, string $notice_message = '' ): void {
	if ( '' === $notice_type ) {
		return;
	}

	$notices = array(
		'created'        => array( 'success', __( 'Profile created successfully.', 'jasanika' ), 'yes' ),
		'activated'      => array( 'success', __( 'Profile activated. Settings applied to your live site.', 'jasanika' ), 'yes-alt' ),
		'duplicated'     => array( 'success', __( 'Profile duplicated successfully.', 'jasanika' ), 'admin-page' ),
		'deleted'        => array( 'success', __( 'Profile deleted.', 'jasanika' ), 'trash' ),
		'imported'       => array( 'success', __( 'Profile imported successfully. Activate it to apply its settings.', 'jasanika' ), 'upload' ),
		'not_found'      => array( 'error',   __( 'Profile not found.', 'jasanika' ), 'warning' ),
		'name_required'  => array( 'error',   __( 'Profile name is required.', 'jasanika' ), 'warning' ),
		'no_file'        => array( 'error',   __( 'No file was uploaded.', 'jasanika' ), 'warning' ),
		'invalid_format' => array( 'error',   __( 'Invalid file format. Only .json files are accepted.', 'jasanika' ), 'warning' ),
		'json_error'     => array( 'error',   __( 'Could not parse the JSON file. Please verify it is valid.', 'jasanika' ), 'warning' ),
		'import_invalid' => array( 'error',   $notice_message ?: __( 'The profile file is invalid.', 'jasanika' ), 'warning' ),
		'import_expired' => array( 'error',   __( 'Import session expired. Please upload the file again.', 'jasanika' ), 'warning' ),
		'read_error'     => array( 'error',   __( 'Could not read the uploaded file.', 'jasanika' ), 'warning' ),
	);

	if ( ! isset( $notices[ $notice_type ] ) ) {
		return;
	}

	[ $type, $message, $icon ] = $notices[ $notice_type ];
	$class = 'jasanika-pm__notice jasanika-pm__notice--' . $type;

	printf(
		'<div class="%s"><span class="dashicons dashicons-%s"></span><span>%s</span></div>',
		esc_attr( $class ),
		esc_attr( $icon ),
		esc_html( $message )
	);
}

/**
 * Render the profile comparison panel.
 *
 * @param array<string, mixed>              $import_data  Validated import data from transient.
 * @param array<string, array<string,mixed>> $profiles     All stored profiles.
 * @param string                            $active_id    Currently active profile ID.
 */
function jasanika_profile_manager_render_comparison( array $import_data, array $profiles, string $active_id ): void {
	$active_profile    = $profiles[ $active_id ] ?? null;
	$current_settings  = $active_profile ? ( $active_profile['settings'] ?? array() ) : jasanika_profile_capture_settings();
	$import_settings   = $import_data['settings'] ?? array();

	$current_count     = jasanika_profile_count_settings( $current_settings );
	$import_count      = jasanika_profile_count_settings( $import_settings );
	$current_sections  = jasanika_profile_get_enabled_sections( $current_settings );
	$import_sections   = jasanika_profile_get_enabled_sections( $import_settings );
	$current_version   = $active_profile ? ( $active_profile['version'] ?? '—' ) : '—';
	$import_version    = $import_data['version'] ?? '—';

	// Detect modules that differ.
	$theme_version     = (string) wp_get_theme()->get( 'Version' );
	$version_matches   = version_compare( $import_version, $theme_version, '==' );
	?>
	<div class="jasanika-pm__card jasanika-pm__card--compare">
		<h2 class="jasanika-pm__card-title">
			<span class="dashicons dashicons-image-flip-horizontal"></span>
			<?php esc_html_e( 'Profile Comparison', 'jasanika' ); ?>
		</h2>

		<?php if ( ! $version_matches ) : ?>
		<div class="jasanika-pm__notice jasanika-pm__notice--warning">
			<span class="dashicons dashicons-warning"></span>
			<span>
				<?php
				printf(
					/* translators: 1: imported version, 2: current theme version */
					esc_html__( 'Version mismatch: imported profile is %1$s, current theme is %2$s. Some settings may differ.', 'jasanika' ),
					esc_html( $import_version ),
					esc_html( $theme_version )
				);
				?>
			</span>
		</div>
		<?php endif; ?>

		<div class="jasanika-pm__compare-grid">

			<!-- Current Profile Column -->
			<div class="jasanika-pm__compare-col">
				<h3 class="jasanika-pm__compare-col-title jasanika-pm__compare-col-title--current">
					<?php esc_html_e( 'Current Profile', 'jasanika' ); ?>
					<?php if ( $active_profile ) : ?>
						<span class="jasanika-pm__compare-name"><?php echo esc_html( $active_profile['name'] ); ?></span>
					<?php else : ?>
						<span class="jasanika-pm__compare-name jasanika-pm__text-muted"><?php esc_html_e( '(none)', 'jasanika' ); ?></span>
					<?php endif; ?>
				</h3>
				<div class="jasanika-pm__compare-rows">
					<div class="jasanika-pm__compare-row">
						<span class="jasanika-pm__compare-label"><?php esc_html_e( 'Version', 'jasanika' ); ?></span>
						<span class="jasanika-pm__compare-value"><?php echo esc_html( $current_version ); ?></span>
					</div>
					<div class="jasanika-pm__compare-row">
						<span class="jasanika-pm__compare-label"><?php esc_html_e( 'Settings Count', 'jasanika' ); ?></span>
						<span class="jasanika-pm__compare-value"><?php echo esc_html( (string) $current_count ); ?></span>
					</div>
					<div class="jasanika-pm__compare-row">
						<span class="jasanika-pm__compare-label"><?php esc_html_e( 'Homepage Sections', 'jasanika' ); ?></span>
						<span class="jasanika-pm__compare-value">
							<?php echo ! empty( $current_sections )
								? esc_html( implode( ', ', $current_sections ) )
								: '<span class="jasanika-pm__text-muted">—</span>'; ?>
						</span>
					</div>
					<div class="jasanika-pm__compare-row">
						<span class="jasanika-pm__compare-label"><?php esc_html_e( 'Enabled Modules', 'jasanika' ); ?></span>
						<span class="jasanika-pm__compare-value"><?php echo esc_html( (string) count( $current_sections ) ); ?></span>
					</div>
				</div>
			</div>

			<!-- Import Arrow -->
			<div class="jasanika-pm__compare-arrow">
				<span class="dashicons dashicons-arrow-right-alt"></span>
			</div>

			<!-- Imported Profile Column -->
			<div class="jasanika-pm__compare-col">
				<h3 class="jasanika-pm__compare-col-title jasanika-pm__compare-col-title--import">
					<?php esc_html_e( 'Imported Profile', 'jasanika' ); ?>
					<span class="jasanika-pm__compare-name"><?php echo esc_html( $import_data['name'] ?? '—' ); ?></span>
				</h3>
				<div class="jasanika-pm__compare-rows">
					<div class="jasanika-pm__compare-row">
						<span class="jasanika-pm__compare-label"><?php esc_html_e( 'Version', 'jasanika' ); ?></span>
						<span class="jasanika-pm__compare-value <?php echo $version_matches ? '' : 'jasanika-pm__text-warning'; ?>">
							<?php echo esc_html( $import_version ); ?>
						</span>
					</div>
					<div class="jasanika-pm__compare-row">
						<span class="jasanika-pm__compare-label"><?php esc_html_e( 'Settings Count', 'jasanika' ); ?></span>
						<span class="jasanika-pm__compare-value"><?php echo esc_html( (string) $import_count ); ?></span>
					</div>
					<div class="jasanika-pm__compare-row">
						<span class="jasanika-pm__compare-label"><?php esc_html_e( 'Homepage Sections', 'jasanika' ); ?></span>
						<span class="jasanika-pm__compare-value">
							<?php echo ! empty( $import_sections )
								? esc_html( implode( ', ', $import_sections ) )
								: '<span class="jasanika-pm__text-muted">—</span>'; ?>
						</span>
					</div>
					<div class="jasanika-pm__compare-row">
						<span class="jasanika-pm__compare-label"><?php esc_html_e( 'Enabled Modules', 'jasanika' ); ?></span>
						<span class="jasanika-pm__compare-value"><?php echo esc_html( (string) count( $import_sections ) ); ?></span>
					</div>
				</div>

				<?php if ( ! empty( $import_data['description'] ) ) : ?>
				<p class="jasanika-pm__compare-desc"><?php echo esc_html( $import_data['description'] ); ?></p>
				<?php endif; ?>
			</div>

		</div><!-- /.jasanika-pm__compare-grid -->

		<!-- Confirm Import Form -->
		<div class="jasanika-pm__compare-actions">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="jasanika_profile_import_confirm">
				<?php wp_nonce_field( 'jasanika_profile_import_confirm' ); ?>
				<button type="submit" class="jasanika-pm__btn jasanika-pm__btn--primary">
					<span class="dashicons dashicons-upload"></span>
					<?php esc_html_e( 'Save as New Profile', 'jasanika' ); ?>
				</button>
			</form>
			<a href="<?php echo esc_url( jasanika_profile_page_url() ); ?>" class="jasanika-pm__btn jasanika-pm__btn--outline">
				<span class="dashicons dashicons-no"></span>
				<?php esc_html_e( 'Cancel', 'jasanika' ); ?>
			</a>
		</div>

	</div><!-- /.jasanika-pm__card--compare -->
	<?php
}

// ---------------------------------------------------------------------------
// Utility
// ---------------------------------------------------------------------------

/**
 * Return the canonical URL for the Profile Manager admin page.
 *
 * @return string
 */
function jasanika_profile_page_url(): string {
	return admin_url( 'admin.php?page=jasanika-profile-manager' );
}
