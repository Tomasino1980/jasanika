<?php

/**
 * Jasanika Admin – Backup Manager Page
 *
 * Provides export (settings + full backup), import, version comparison,
 * system information and backup history for all Jasanika settings.
 *
 * Handlers registered:
 *   admin_post_jasanika_export_settings
 *   admin_post_jasanika_export_full_backup
 *   admin_post_jasanika_import_backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_jasanika_export_settings',    'jasanika_handle_export_settings' );
add_action( 'admin_post_jasanika_export_full_backup', 'jasanika_handle_export_full_backup' );
add_action( 'admin_post_jasanika_import_backup',      'jasanika_handle_import_backup' );
add_action( 'admin_enqueue_scripts',                  'jasanika_backup_manager_enqueue' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue Backup Manager stylesheet on its admin page.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_backup_manager_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-backup-manager' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'jasanika-backup-manager',
		get_template_directory_uri() . '/assets/css/admin/backup-manager.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}

// ---------------------------------------------------------------------------
// Export Handlers
// ---------------------------------------------------------------------------

/**
 * Handle Export Settings action.
 *
 * Exports theme settings, homepage builder, SEO, newsletter and cookie
 * configuration (no content data such as sliders or testimonials).
 */
function jasanika_handle_export_settings(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	check_admin_referer( 'jasanika_export_settings' );

	$sections = array( 'theme_settings', 'homepage_builder', 'seo_settings', 'newsletter_settings', 'cookie_manager' );
	$export   = jasanika_export_settings( $sections );
	$json     = wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
	$filename = 'jasanika-settings-' . gmdate( 'Y-m-d' ) . '.json';

	jasanika_backup_history_add( array(
		'date'    => gmdate( 'Y-m-d H:i:s' ),
		'type'    => 'export_settings',
		'version' => (string) wp_get_theme()->get( 'Version' ),
	) );

	jasanika_backup_send_download( $filename, (string) $json );
}

/**
 * Handle Export Full Backup action.
 *
 * Exports all Jasanika sections: settings, homepage builder, sliders,
 * testimonials, newsletter subscribers, SEO and cookie configuration.
 */
function jasanika_handle_export_full_backup(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	check_admin_referer( 'jasanika_export_full_backup' );

	$export   = jasanika_export_settings();
	$json     = wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
	$filename = 'jasanika-backup-' . gmdate( 'Y-m-d' ) . '.json';

	jasanika_backup_history_add( array(
		'date'    => gmdate( 'Y-m-d H:i:s' ),
		'type'    => 'export_full',
		'version' => (string) wp_get_theme()->get( 'Version' ),
	) );

	jasanika_backup_send_download( $filename, (string) $json );
}

/**
 * Send a JSON string to the browser as a file download, then exit.
 *
 * @param string $filename Download filename.
 * @param string $content  JSON content.
 */
function jasanika_backup_send_download( string $filename, string $content ): void {
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Content-Length: ' . strlen( $content ) );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $content;
	exit;
}

// ---------------------------------------------------------------------------
// Import Handler
// ---------------------------------------------------------------------------

/**
 * Handle Import Backup form submission.
 *
 * Validates the uploaded JSON file, confirms it is a Jasanika backup,
 * calls jasanika_import_settings() and redirects back with a status flag.
 */
function jasanika_handle_import_backup(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	check_admin_referer( 'jasanika_import_backup' );

	$redirect_url = admin_url( 'admin.php?page=jasanika-backup-manager' );

	// Check a file was actually uploaded without errors.
	$upload_error = isset( $_FILES['jasanika_backup_file']['error'] )
		? (int) $_FILES['jasanika_backup_file']['error']
		: UPLOAD_ERR_NO_FILE;

	if ( UPLOAD_ERR_OK !== $upload_error ) {
		wp_safe_redirect( add_query_arg( 'import_error', 'no_file', $redirect_url ) );
		exit;
	}

	$tmp_name = isset( $_FILES['jasanika_backup_file']['tmp_name'] )
		? sanitize_text_field( wp_unslash( (string) $_FILES['jasanika_backup_file']['tmp_name'] ) )
		: '';

	$original_name = isset( $_FILES['jasanika_backup_file']['name'] )
		? sanitize_file_name( wp_unslash( (string) $_FILES['jasanika_backup_file']['name'] ) )
		: '';

	// Validate file extension.
	if ( ! preg_match( '/\.json$/i', $original_name ) ) {
		wp_safe_redirect( add_query_arg( 'import_error', 'invalid_type', $redirect_url ) );
		exit;
	}

	// Read uploaded file.
	if ( ! is_uploaded_file( $tmp_name ) ) {
		wp_safe_redirect( add_query_arg( 'import_error', 'read_error', $redirect_url ) );
		exit;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$content = file_get_contents( $tmp_name );

	if ( false === $content ) {
		wp_safe_redirect( add_query_arg( 'import_error', 'read_error', $redirect_url ) );
		exit;
	}

	// Decode JSON.
	$decoded = json_decode( $content, true );

	if ( ! is_array( $decoded ) ) {
		wp_safe_redirect( add_query_arg( 'import_error', 'invalid_json', $redirect_url ) );
		exit;
	}

	// Validate Jasanika backup structure.
	if ( empty( $decoded['jasanika_version'] ) || empty( $decoded['data'] ) ) {
		wp_safe_redirect( add_query_arg( 'import_error', 'invalid_structure', $redirect_url ) );
		exit;
	}

	// Perform import.
	$result = jasanika_import_settings( $decoded );

	if ( is_wp_error( $result ) ) {
		wp_safe_redirect( add_query_arg( 'import_error', 'import_failed', $redirect_url ) );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'import_success', '1', $redirect_url ) );
	exit;
}

// ---------------------------------------------------------------------------
// Page Renderer
// ---------------------------------------------------------------------------

/**
 * Render the Backup Manager admin page.
 */
function jasanika_admin_page_backup_manager(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}

	$theme_version = (string) wp_get_theme()->get( 'Version' );
	$wp_version    = (string) get_bloginfo( 'version' );
	$php_version   = PHP_VERSION;
	$woo_version   = defined( 'WC_VERSION' ) ? (string) constant( 'WC_VERSION' ) : __( 'Not installed', 'jasanika' );
	$history       = jasanika_get_backup_info();

	// Find the most recent import in history for version comparison.
	$last_import = null;
	foreach ( $history as $entry ) {
		if ( isset( $entry['type'] ) && 'import' === $entry['type'] ) {
			$last_import = $entry;
			break;
		}
	}

	$last_import_version = $last_import['version'] ?? '';
	$version_mismatch    = $last_import_version && ( $last_import_version !== $theme_version );

	// Read status flags from redirect params.
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$import_success = isset( $_GET['import_success'] ) && '1' === $_GET['import_success'];
	$import_error   = isset( $_GET['import_error'] )
		? sanitize_text_field( wp_unslash( (string) $_GET['import_error'] ) )
		: '';
	// phpcs:enable

	$error_messages = array(
		'no_file'           => __( 'No file was uploaded. Please select a JSON backup file.', 'jasanika' ),
		'invalid_type'      => __( 'Invalid file type. Please upload a .json file.', 'jasanika' ),
		'read_error'        => __( 'Could not read the uploaded file. Please try again.', 'jasanika' ),
		'invalid_json'      => __( 'The uploaded file is not valid JSON. Please check the file and try again.', 'jasanika' ),
		'invalid_structure' => __( 'The uploaded file does not appear to be a valid Jasanika backup.', 'jasanika' ),
		'import_failed'     => __( 'Import failed. Please try again or contact support.', 'jasanika' ),
	);

	$type_labels = array(
		'export_settings' => __( 'Export Settings', 'jasanika' ),
		'export_full'     => __( 'Full Backup', 'jasanika' ),
		'import'          => __( 'Import', 'jasanika' ),
	);
	?>
	<div class="wrap jasanika-backup">

		<!-- Header -->
		<div class="jasanika-backup__header">
			<span class="jasanika-backup__header-icon dashicons dashicons-backup"></span>
			<div>
				<h1 class="jasanika-backup__title"><?php esc_html_e( 'Backup Manager', 'jasanika' ); ?></h1>
				<p class="jasanika-backup__subtitle">
					<?php esc_html_e( 'Export, import and manage Jasanika configuration backups. Backups are generated on demand.', 'jasanika' ); ?>
				</p>
			</div>
		</div>

		<!-- Import Success Notice -->
		<?php if ( $import_success ) : ?>
			<div class="jasanika-backup__notice jasanika-backup__notice--success">
				<span class="dashicons dashicons-yes-alt"></span>
				<div>
					<?php esc_html_e( 'Backup imported successfully.', 'jasanika' ); ?>
					<?php if ( $version_mismatch ) : ?>
						<span class="jasanika-backup__notice-warning">
							<?php
							printf(
								/* translators: 1: imported version, 2: current version */
								esc_html__( 'Version mismatch: backup was created with version %1$s, current theme version is %2$s. Please review your settings.', 'jasanika' ),
								'<strong>' . esc_html( $last_import_version ) . '</strong>',
								'<strong>' . esc_html( $theme_version ) . '</strong>'
							);
							?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Import Error Notice -->
		<?php if ( $import_error ) : ?>
			<div class="jasanika-backup__notice jasanika-backup__notice--error">
				<span class="dashicons dashicons-warning"></span>
				<div><?php echo esc_html( $error_messages[ $import_error ] ?? $import_error ); ?></div>
			</div>
		<?php endif; ?>

		<!-- Quick Actions -->
		<div class="jasanika-backup__card">
			<h2 class="jasanika-backup__card-title">
				<span class="dashicons dashicons-download"></span>
				<?php esc_html_e( 'Quick Backup', 'jasanika' ); ?>
			</h2>
			<p class="jasanika-backup__card-desc">
				<?php esc_html_e( 'Create or restore a backup of your Jasanika configuration.', 'jasanika' ); ?>
			</p>

			<div class="jasanika-backup__actions">

				<!-- Export Settings -->
				<div class="jasanika-backup__action-item">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="jasanika_export_settings">
						<?php wp_nonce_field( 'jasanika_export_settings' ); ?>
						<button type="submit" class="jasanika-backup__btn jasanika-backup__btn--primary">
							<span class="dashicons dashicons-download"></span>
							<?php esc_html_e( 'Export Settings', 'jasanika' ); ?>
						</button>
					</form>
					<p class="jasanika-backup__action-hint">
						<?php esc_html_e( 'Theme settings, homepage builder, SEO, newsletter and cookie configuration.', 'jasanika' ); ?>
					</p>
				</div>

				<!-- Export Full Backup -->
				<div class="jasanika-backup__action-item">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="jasanika_export_full_backup">
						<?php wp_nonce_field( 'jasanika_export_full_backup' ); ?>
						<button type="submit" class="jasanika-backup__btn jasanika-backup__btn--secondary">
							<span class="dashicons dashicons-archive"></span>
							<?php esc_html_e( 'Export Full Backup', 'jasanika' ); ?>
						</button>
					</form>
					<p class="jasanika-backup__action-hint">
						<?php esc_html_e( 'All settings including sliders, testimonials and all content data.', 'jasanika' ); ?>
					</p>
				</div>

				<!-- Import Backup (scrolls to import section) -->
				<div class="jasanika-backup__action-item">
					<button type="button" class="jasanika-backup__btn jasanika-backup__btn--outline" id="jasanika-import-scroll-btn">
						<span class="dashicons dashicons-upload"></span>
						<?php esc_html_e( 'Import Backup', 'jasanika' ); ?>
					</button>
					<p class="jasanika-backup__action-hint">
						<?php esc_html_e( 'Restore settings from a previously exported Jasanika backup file.', 'jasanika' ); ?>
					</p>
				</div>

			</div>
		</div>

		<!-- Version Information -->
		<div class="jasanika-backup__card">
			<h2 class="jasanika-backup__card-title">
				<span class="dashicons dashicons-info-outline"></span>
				<?php esc_html_e( 'Version Information', 'jasanika' ); ?>
			</h2>
			<div class="jasanika-backup__version-grid">
				<div class="jasanika-backup__version-item">
					<span class="jasanika-backup__version-label">
						<?php esc_html_e( 'Current Theme Version', 'jasanika' ); ?>
					</span>
					<span class="jasanika-backup__version-value">
						<code><?php echo esc_html( $theme_version ); ?></code>
					</span>
				</div>
				<div class="jasanika-backup__version-item <?php echo $version_mismatch ? 'jasanika-backup__version-item--warning' : ''; ?>">
					<span class="jasanika-backup__version-label">
						<?php esc_html_e( 'Last Import Version', 'jasanika' ); ?>
					</span>
					<span class="jasanika-backup__version-value">
						<?php if ( $last_import_version ) : ?>
							<code><?php echo esc_html( $last_import_version ); ?></code>
							<?php if ( $version_mismatch ) : ?>
								<span class="jasanika-backup__badge jasanika-backup__badge--warning">
									<?php esc_html_e( 'Mismatch', 'jasanika' ); ?>
								</span>
							<?php else : ?>
								<span class="jasanika-backup__badge jasanika-backup__badge--ok">
									<?php esc_html_e( 'OK', 'jasanika' ); ?>
								</span>
							<?php endif; ?>
						<?php else : ?>
							<span class="jasanika-backup__version-none">
								<?php esc_html_e( 'No import recorded', 'jasanika' ); ?>
							</span>
						<?php endif; ?>
					</span>
				</div>
			</div>
		</div>

		<!-- System Information -->
		<div class="jasanika-backup__card">
			<h2 class="jasanika-backup__card-title">
				<span class="dashicons dashicons-info"></span>
				<?php esc_html_e( 'System Information', 'jasanika' ); ?>
			</h2>
			<p class="jasanika-backup__card-desc">
				<?php esc_html_e( 'Useful information for support and compatibility checks.', 'jasanika' ); ?>
			</p>
			<table class="jasanika-backup__info-table">
				<tbody>
					<tr>
						<th><?php esc_html_e( 'WordPress Version', 'jasanika' ); ?></th>
						<td><code><?php echo esc_html( $wp_version ); ?></code></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Theme Version', 'jasanika' ); ?></th>
						<td><code><?php echo esc_html( $theme_version ); ?></code></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'WooCommerce Version', 'jasanika' ); ?></th>
						<td><code><?php echo esc_html( $woo_version ); ?></code></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'PHP Version', 'jasanika' ); ?></th>
						<td><code><?php echo esc_html( $php_version ); ?></code></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Import Section -->
		<div class="jasanika-backup__card" id="jasanika-import-section">
			<h2 class="jasanika-backup__card-title">
				<span class="dashicons dashicons-upload"></span>
				<?php esc_html_e( 'Import Backup', 'jasanika' ); ?>
			</h2>

			<!-- Restore Protection Warning -->
			<div class="jasanika-backup__restore-warning">
				<span class="dashicons dashicons-shield-alt"></span>
				<div>
					<strong><?php esc_html_e( 'Restore Protection', 'jasanika' ); ?></strong>
					<p>
						<?php esc_html_e( 'Importing a backup will overwrite your current Jasanika settings. This action cannot be undone. We strongly recommend creating a full backup before importing.', 'jasanika' ); ?>
					</p>
				</div>
			</div>

			<form
				method="post"
				action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
				enctype="multipart/form-data"
				id="jasanika-import-form"
				class="jasanika-backup__import-form"
			>
				<input type="hidden" name="action" value="jasanika_import_backup">
				<?php wp_nonce_field( 'jasanika_import_backup' ); ?>

				<div class="jasanika-backup__file-row">
					<label for="jasanika_backup_file" class="jasanika-backup__file-label">
						<?php esc_html_e( 'Backup File', 'jasanika' ); ?>
					</label>
					<div class="jasanika-backup__file-controls">
						<input
							type="file"
							name="jasanika_backup_file"
							id="jasanika_backup_file"
							accept=".json,application/json"
							required
							class="jasanika-backup__file-input"
						>
						<span class="jasanika-backup__file-name" id="jasanika-file-name" aria-live="polite">
							<?php esc_html_e( 'No file selected', 'jasanika' ); ?>
						</span>
					</div>
					<p class="jasanika-backup__file-hint">
						<?php esc_html_e( 'Select a .json file exported from Jasanika Backup Manager.', 'jasanika' ); ?>
					</p>
				</div>

				<button type="submit" class="jasanika-backup__btn jasanika-backup__btn--danger">
					<span class="dashicons dashicons-upload"></span>
					<?php esc_html_e( 'Import & Restore', 'jasanika' ); ?>
				</button>
			</form>
		</div>

		<!-- Backup History -->
		<div class="jasanika-backup__card">
			<h2 class="jasanika-backup__card-title">
				<span class="dashicons dashicons-list-view"></span>
				<?php esc_html_e( 'Backup History', 'jasanika' ); ?>
			</h2>
			<p class="jasanika-backup__card-desc">
				<?php esc_html_e( 'Last 20 backup operations. Metadata only – no backup files are stored on the server.', 'jasanika' ); ?>
			</p>

			<?php if ( empty( $history ) ) : ?>
				<p class="jasanika-backup__empty">
					<?php esc_html_e( 'No backup history yet. Create your first backup using the buttons above.', 'jasanika' ); ?>
				</p>
			<?php else : ?>
				<table class="jasanika-backup__history-table widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Backup Date', 'jasanika' ); ?></th>
							<th><?php esc_html_e( 'Backup Type', 'jasanika' ); ?></th>
							<th><?php esc_html_e( 'Version', 'jasanika' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $history as $entry ) : ?>
							<?php
							$entry_type    = $entry['type'] ?? '';
							$entry_date    = $entry['date'] ?? '-';
							$entry_version = $entry['version'] ?? '-';
							?>
							<tr>
								<td><?php echo esc_html( $entry_date ); ?></td>
								<td>
									<span class="jasanika-backup__type-badge jasanika-backup__type-badge--<?php echo esc_attr( $entry_type ); ?>">
										<?php echo esc_html( $type_labels[ $entry_type ] ?? $entry_type ); ?>
									</span>
								</td>
								<td><code><?php echo esc_html( $entry_version ); ?></code></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

	</div>

	<script>
	( function () {
		'use strict';

		// Import scroll button → smooth-scroll to import section.
		var scrollBtn     = document.getElementById( 'jasanika-import-scroll-btn' );
		var importSection = document.getElementById( 'jasanika-import-section' );

		if ( scrollBtn && importSection ) {
			scrollBtn.addEventListener( 'click', function () {
				importSection.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				var fileInput = document.getElementById( 'jasanika_backup_file' );
				if ( fileInput ) {
					setTimeout( function () { fileInput.focus(); }, 400 );
				}
			} );
		}

		// Display selected filename next to file input.
		var fileInput = document.getElementById( 'jasanika_backup_file' );
		var fileName  = document.getElementById( 'jasanika-file-name' );

		if ( fileInput && fileName ) {
			fileInput.addEventListener( 'change', function () {
				fileName.textContent = fileInput.files.length > 0
					? fileInput.files[0].name
					: '<?php echo esc_js( __( 'No file selected', 'jasanika' ) ); ?>';
			} );
		}

		// Restore protection: require confirmation before submitting import form.
		var importForm = document.getElementById( 'jasanika-import-form' );

		if ( importForm ) {
			importForm.addEventListener( 'submit', function ( e ) {
				var confirmed = window.confirm(
					'<?php echo esc_js( __( 'Are you sure you want to import this backup? This will overwrite your current Jasanika settings and cannot be undone.', 'jasanika' ) ); ?>'
				);
				if ( ! confirmed ) {
					e.preventDefault();
				}
			} );
		}
	} )();
	</script>
	<?php
}
