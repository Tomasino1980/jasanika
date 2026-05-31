<?php

/**
 * Jasanika Admin – Update Center Page
 *
 * Central update and version management center for the Jasanika framework.
 *
 * Sections:
 *   – Update Notices
 *   – Version Overview
 *   – Theme Information (from style.css)
 *   – Module Overview
 *   – System Compatibility
 *   – Changelog Viewer
 *   – Release History
 *   – Export Version Report
 *
 * Also registers an Admin Dashboard Widget: Jasanika Version Information.
 *
 * Export handlers:
 *   admin_post_jasanika_export_report_txt
 *   admin_post_jasanika_export_report_json
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_enqueue_scripts', 'jasanika_update_center_enqueue' );
add_action( 'admin_post_jasanika_export_report_txt',  'jasanika_update_center_handle_export_txt' );
add_action( 'admin_post_jasanika_export_report_json', 'jasanika_update_center_handle_export_json' );
add_action( 'wp_dashboard_setup', 'jasanika_update_center_register_dashboard_widget' );
add_action( 'admin_enqueue_scripts', 'jasanika_update_center_enqueue_widget_styles' );
add_action( 'init', 'jasanika_update_center_maybe_set_install_notice' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue Update Center stylesheet only on its own admin page.
 *
 * @param string $hook Current admin page hook suffix.
 */
function jasanika_update_center_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-update-center' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'jasanika-update-center',
		get_template_directory_uri() . '/assets/css/admin/update-center.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}

/**
 * Enqueue widget styles only on the main WordPress dashboard.
 *
 * @param string $hook Current admin page hook suffix.
 */
function jasanika_update_center_enqueue_widget_styles( string $hook ): void {
	if ( 'index.php' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'jasanika-update-center-widget',
		get_template_directory_uri() . '/assets/css/admin/update-center.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}

// ---------------------------------------------------------------------------
// Install Notice
// ---------------------------------------------------------------------------

/**
 * On first run after a version update, store a "New Milestone Installed" notice.
 */
function jasanika_update_center_maybe_set_install_notice(): void {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$current_version = wp_get_theme()->get( 'Version' );
	$noticed_version = get_option( 'jasanika_update_center_noticed_version', '' );

	if ( $noticed_version === $current_version ) {
		return;
	}

	$notices   = get_option( 'jasanika_update_notices', array() );
	$milestone = jasanika_update_center_version_to_milestone( $current_version );
	$changelog = jasanika_update_center_get_changelog();
	$name      = isset( $changelog[ $milestone ] ) ? $changelog[ $milestone ]['name'] : 'Update Center';

	$notices[] = array(
		'type'    => 'milestone',
		'title'   => sprintf( __( 'New Milestone Installed: M%d – %s', 'jasanika' ), $milestone, $name ),
		'message' => sprintf(
			__( 'Version %s has been installed successfully. Review the changelog for what is new.', 'jasanika' ),
			esc_html( $current_version )
		),
	);

	update_option( 'jasanika_update_notices', $notices );
	update_option( 'jasanika_update_center_noticed_version', $current_version );
}

/**
 * Convert a version string (0.X.0) to the milestone number.
 *
 * @param string $version Version string, e.g. "0.48.0".
 * @return int Milestone number.
 */
function jasanika_update_center_version_to_milestone( string $version ): int {
	$parts = explode( '.', $version );

	return isset( $parts[1] ) ? (int) $parts[1] : 0;
}

// ---------------------------------------------------------------------------
// Data
// ---------------------------------------------------------------------------

/**
 * Return the full milestone changelog array, newest first.
 *
 * Each entry:
 *   'name'    => string  Human-readable milestone name
 *   'version' => string  Theme version (0.X.0)
 *   'date'    => string  Approximate release date (YYYY-MM)
 *
 * @return array<int, array{name: string, version: string, date: string}>
 */
function jasanika_update_center_get_changelog(): array {
	return array(
		50 => array( 'name' => 'Release Candidate & Stabilization', 'version' => '0.50.0', 'date' => '2026-05' ),
		49 => array( 'name' => 'Theme Presets',                     'version' => '0.49.0', 'date' => '2026-05' ),
		48 => array( 'name' => 'Update Center',              'version' => '0.48.0', 'date' => '2026-05' ),
		47 => array( 'name' => 'Maintenance Mode',           'version' => '0.47.0', 'date' => '2026-05' ),
		46 => array( 'name' => 'Profile Manager',            'version' => '0.46.0', 'date' => '2026-04' ),
		45 => array( 'name' => 'Backup Manager',             'version' => '0.45.0', 'date' => '2026-04' ),
		44 => array( 'name' => 'Diagnostics Manager',        'version' => '0.44.0', 'date' => '2026-04' ),
		43 => array( 'name' => 'Cookie Manager',             'version' => '0.43.0', 'date' => '2026-03' ),
		42 => array( 'name' => 'SEO Manager',                'version' => '0.42.0', 'date' => '2026-03' ),
		41 => array( 'name' => 'Newsletter Manager',         'version' => '0.41.0', 'date' => '2026-03' ),
		40 => array( 'name' => 'Testimonials Manager',       'version' => '0.40.0', 'date' => '2026-02' ),
		39 => array( 'name' => 'Slider Manager',             'version' => '0.39.0', 'date' => '2026-02' ),
		38 => array( 'name' => 'Homepage Builder',           'version' => '0.38.0', 'date' => '2026-02' ),
		37 => array( 'name' => 'Featured Products',          'version' => '0.37.0', 'date' => '2026-01' ),
		36 => array( 'name' => 'Theme Settings',             'version' => '0.36.0', 'date' => '2026-01' ),
		35 => array( 'name' => 'Menu Manager',               'version' => '0.35.0', 'date' => '2026-01' ),
		34 => array( 'name' => 'Login Branding',             'version' => '0.34.0', 'date' => '2025-12' ),
		33 => array( 'name' => 'WooCommerce Enhancements',   'version' => '0.33.0', 'date' => '2025-12' ),
		32 => array( 'name' => 'Contact Page',               'version' => '0.32.0', 'date' => '2025-12' ),
		31 => array( 'name' => 'Author Archive',             'version' => '0.31.0', 'date' => '2025-11' ),
		30 => array( 'name' => 'Tag Archive',                'version' => '0.30.0', 'date' => '2025-11' ),
		29 => array( 'name' => 'Category Archive',           'version' => '0.29.0', 'date' => '2025-11' ),
		28 => array( 'name' => 'Archive Templates',          'version' => '0.28.0', 'date' => '2025-10' ),
		27 => array( 'name' => 'Search Results',             'version' => '0.27.0', 'date' => '2025-10' ),
		26 => array( 'name' => '404 Page',                   'version' => '0.26.0', 'date' => '2025-10' ),
		25 => array( 'name' => 'Single Post',                'version' => '0.25.0', 'date' => '2025-09' ),
		24 => array( 'name' => 'Blog Foundation',            'version' => '0.24.0', 'date' => '2025-09' ),
		23 => array( 'name' => 'Comments System',            'version' => '0.23.0', 'date' => '2025-09' ),
		22 => array( 'name' => 'My Account',                 'version' => '0.22.0', 'date' => '2025-08' ),
		21 => array( 'name' => 'WooCommerce Cart',           'version' => '0.21.0', 'date' => '2025-08' ),
		20 => array( 'name' => 'WooCommerce Foundation',     'version' => '0.20.0', 'date' => '2025-08' ),
		19 => array( 'name' => 'Hero Slider',                'version' => '0.19.0', 'date' => '2025-07' ),
		18 => array( 'name' => 'Feature Blocks',             'version' => '0.18.0', 'date' => '2025-07' ),
		17 => array( 'name' => 'Latest Posts Section',       'version' => '0.17.0', 'date' => '2025-07' ),
		16 => array( 'name' => 'Categories Section',         'version' => '0.16.0', 'date' => '2025-06' ),
		15 => array( 'name' => 'CTA Section',                'version' => '0.15.0', 'date' => '2025-06' ),
		14 => array( 'name' => 'Homepage Builder Foundation','version' => '0.14.0', 'date' => '2025-06' ),
		13 => array( 'name' => 'Footer Builder',             'version' => '0.13.0', 'date' => '2025-05' ),
		12 => array( 'name' => 'Production Release',         'version' => '0.12.0', 'date' => '2025-05' ),
		11 => array( 'name' => 'Custom Modules',             'version' => '0.11.0', 'date' => '2025-04' ),
		10 => array( 'name' => 'Simple Slider',              'version' => '0.10.0', 'date' => '2025-04' ),
		9  => array( 'name' => 'Gallery',                    'version' => '0.9.0',  'date' => '2025-03' ),
		8  => array( 'name' => 'Blog',                       'version' => '0.8.0',  'date' => '2025-03' ),
		7  => array( 'name' => 'WooCommerce Foundation',     'version' => '0.7.0',  'date' => '2025-02' ),
		6  => array( 'name' => 'Homepage Sections',          'version' => '0.6.0',  'date' => '2025-02' ),
		5  => array( 'name' => 'Hero Section',               'version' => '0.5.0',  'date' => '2025-02' ),
		4  => array( 'name' => 'Menu System',                'version' => '0.4.0',  'date' => '2025-01' ),
		3  => array( 'name' => 'Administration Foundation',  'version' => '0.3.0',  'date' => '2025-01' ),
		2  => array( 'name' => 'Layout Foundation',          'version' => '0.2.0',  'date' => '2025-01' ),
		1  => array( 'name' => 'Theme Skeleton',             'version' => '0.1.0',  'date' => '2024-12' ),
		0  => array( 'name' => 'INITIAL',                    'version' => '0.0.0',  'date' => '2024-12' ),
	);
}

/**
 * Return the list of framework modules with their active status.
 *
 * @return array<int, array{label: string, active: bool}>
 */
function jasanika_update_center_get_modules(): array {
	$dir = get_template_directory();

	return array(
		array(
			'label'  => __( 'Dashboard', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/dashboard.php' ),
		),
		array(
			'label'  => __( 'Menu Manager', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/menu-manager.php' ),
		),
		array(
			'label'  => __( 'Slider Manager', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/slider-manager.php' ),
		),
		array(
			'label'  => __( 'Testimonials Manager', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/testimonials-manager.php' ),
		),
		array(
			'label'  => __( 'Newsletter Manager', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/newsletter-manager.php' ),
		),
		array(
			'label'  => __( 'SEO Manager', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/seo-manager.php' ),
		),
		array(
			'label'  => __( 'Cookie Manager', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/cookie-manager.php' ),
		),
		array(
			'label'  => __( 'Backup Manager', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/backup-manager.php' ),
		),
		array(
			'label'  => __( 'Profile Manager', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/profile-manager.php' ),
		),
		array(
			'label'  => __( 'Maintenance Mode', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/maintenance-manager.php' ),
		),
		array(
			'label'  => __( 'Diagnostics Manager', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/diagnostics-manager.php' ),
		),
		array(
			'label'  => __( 'Theme Presets', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/theme-presets.php' ),
		),
		array(
			'label'  => __( 'Update Center', 'jasanika' ),
			'active' => file_exists( $dir . '/inc/admin/pages/update-center.php' ),
		),
	);
}

/**
 * Return system compatibility data.
 *
 * @return array<int, array{label: string, version: string, status: string, note: string}>
 */
function jasanika_update_center_get_compat(): array {
	global $wp_version;

	// WordPress: require >= 7.0.
	$wp_ok     = version_compare( $wp_version, '7.0', '>=' );
	$wp_review = ! $wp_ok && version_compare( $wp_version, '6.5', '>=' );

	// PHP: require >= 8.2.
	$php_version = PHP_VERSION;
	$php_ok      = version_compare( $php_version, '8.2', '>=' );
	$php_review  = ! $php_ok && version_compare( $php_version, '8.0', '>=' );

	// WooCommerce.
	$wc_active  = defined( 'WC_VERSION' );
	$wc_version = $wc_active ? WC_VERSION : __( 'Not installed', 'jasanika' );
	$wc_ok      = $wc_active && version_compare( WC_VERSION, '8.0', '>=' );
	$wc_review  = $wc_active && ! $wc_ok;

	return array(
		array(
			'label'   => __( 'WordPress Version', 'jasanika' ),
			'version' => $wp_version,
			'status'  => $wp_ok ? 'ok' : ( $wp_review ? 'warn' : 'warn' ),
			'note'    => $wp_ok ? __( '✓ Compatible', 'jasanika' ) : __( '⚠ Review Required', 'jasanika' ),
		),
		array(
			'label'   => __( 'PHP Version', 'jasanika' ),
			'version' => $php_version,
			'status'  => $php_ok ? 'ok' : ( $php_review ? 'warn' : 'warn' ),
			'note'    => $php_ok ? __( '✓ Compatible', 'jasanika' ) : __( '⚠ Review Required', 'jasanika' ),
		),
		array(
			'label'   => __( 'WooCommerce Version', 'jasanika' ),
			'version' => $wc_version,
			'status'  => $wc_ok ? 'ok' : ( $wc_review ? 'warn' : 'na' ),
			'note'    => $wc_ok ? __( '✓ Compatible', 'jasanika' ) : ( $wc_review ? __( '⚠ Review Required', 'jasanika' ) : __( '— Not installed', 'jasanika' ) ),
		),
	);
}

/**
 * Build the version report data array used for both TXT and JSON export.
 *
 * @return array<string, mixed>
 */
function jasanika_update_center_build_report_data(): array {
	global $wp_version;

	$theme     = wp_get_theme();
	$changelog = jasanika_update_center_get_changelog();
	$modules   = jasanika_update_center_get_modules();
	$compat    = jasanika_update_center_get_compat();
	$milestone = jasanika_update_center_version_to_milestone( $theme->get( 'Version' ) );

	$modules_export = array();
	foreach ( $modules as $module ) {
		$modules_export[] = array(
			'module' => $module['label'],
			'status' => $module['active'] ? 'active' : 'inactive',
		);
	}

	$compat_export = array();
	foreach ( $compat as $item ) {
		$compat_export[] = array(
			'component' => $item['label'],
			'version'   => $item['version'],
			'status'    => $item['note'],
		);
	}

	return array(
		'generated_at'      => gmdate( 'Y-m-d H:i:s' ) . ' UTC',
		'theme_name'        => $theme->get( 'Name' ),
		'theme_author'      => $theme->get( 'Author' ),
		'theme_uri'         => $theme->get( 'ThemeURI' ),
		'current_version'   => $theme->get( 'Version' ),
		'build_number'      => $milestone,
		'latest_milestone'  => sprintf( 'M%d – %s', $milestone, isset( $changelog[ $milestone ] ) ? $changelog[ $milestone ]['name'] : '' ),
		'modules'           => $modules_export,
		'compatibility'     => $compat_export,
	);
}

// ---------------------------------------------------------------------------
// Export Handlers
// ---------------------------------------------------------------------------

/**
 * Handle TXT export download.
 */
function jasanika_update_center_handle_export_txt(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'jasanika' ) );
	}

	check_admin_referer( 'jasanika_export_report' );

	$data     = jasanika_update_center_build_report_data();
	$filename = 'jasanika-version-report-' . gmdate( 'Y-m-d' ) . '.txt';

	$lines   = array();
	$lines[] = '================================';
	$lines[] = 'JASANIKA FRAMEWORK – VERSION REPORT';
	$lines[] = '================================';
	$lines[] = '';
	$lines[] = 'Generated: ' . $data['generated_at'];
	$lines[] = '';
	$lines[] = '--- THEME INFORMATION ---';
	$lines[] = 'Name:             ' . $data['theme_name'];
	$lines[] = 'Author:           ' . $data['theme_author'];
	$lines[] = 'URI:              ' . $data['theme_uri'];
	$lines[] = 'Current Version:  ' . $data['current_version'];
	$lines[] = 'Build Number:     ' . $data['build_number'];
	$lines[] = 'Latest Milestone: ' . $data['latest_milestone'];
	$lines[] = '';
	$lines[] = '--- MODULES ---';

	foreach ( $data['modules'] as $module ) {
		$status  = 'active' === $module['status'] ? '✓' : '✗';
		$lines[] = $status . ' ' . $module['module'];
	}

	$lines[] = '';
	$lines[] = '--- SYSTEM COMPATIBILITY ---';

	foreach ( $data['compatibility'] as $item ) {
		$lines[] = $item['component'] . ': ' . $item['version'] . ' – ' . $item['status'];
	}

	$lines[] = '';
	$lines[] = '================================';

	$content = implode( "\n", $lines );

	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Content-Length: ' . strlen( $content ) );
	header( 'Cache-Control: no-store, no-cache' );

	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}

/**
 * Handle JSON export download.
 */
function jasanika_update_center_handle_export_json(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'jasanika' ) );
	}

	check_admin_referer( 'jasanika_export_report' );

	$data     = jasanika_update_center_build_report_data();
	$filename = 'jasanika-version-report-' . gmdate( 'Y-m-d' ) . '.json';
	$content  = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

	if ( false === $content ) {
		wp_die( esc_html__( 'Failed to encode report data.', 'jasanika' ) );
	}

	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Content-Length: ' . strlen( $content ) );
	header( 'Cache-Control: no-store, no-cache' );

	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}

// ---------------------------------------------------------------------------
// Admin Dashboard Widget
// ---------------------------------------------------------------------------

/**
 * Register the Jasanika Version Information widget on the WP Dashboard.
 */
function jasanika_update_center_register_dashboard_widget(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_add_dashboard_widget(
		'jasanika_version_information',
		__( 'Jasanika Version Information', 'jasanika' ),
		'jasanika_update_center_render_dashboard_widget'
	);
}

/**
 * Render the Jasanika Version Information dashboard widget.
 */
function jasanika_update_center_render_dashboard_widget(): void {
	$theme     = wp_get_theme();
	$version   = $theme->get( 'Version' );
	$milestone = jasanika_update_center_version_to_milestone( $version );
	$changelog = jasanika_update_center_get_changelog();
	$name      = isset( $changelog[ $milestone ] ) ? $changelog[ $milestone ]['name'] : '';

	?>
	<div class="jasanika-version-widget">

		<div class="jasanika-version-widget__row">
			<span class="jasanika-version-widget__label"><?php esc_html_e( 'Current Version', 'jasanika' ); ?></span>
			<span class="jasanika-version-widget__value"><?php echo esc_html( $version ); ?></span>
		</div>

		<div class="jasanika-version-widget__row">
			<span class="jasanika-version-widget__label"><?php esc_html_e( 'Latest Milestone', 'jasanika' ); ?></span>
			<span class="jasanika-version-widget__value">
				<?php echo esc_html( sprintf( 'M%d – %s', $milestone, $name ) ); ?>
			</span>
		</div>

		<div class="jasanika-version-widget__row">
			<span class="jasanika-version-widget__label"><?php esc_html_e( 'Build Number', 'jasanika' ); ?></span>
			<span class="jasanika-version-widget__value"><?php echo esc_html( (string) $milestone ); ?></span>
		</div>

		<a
			href="<?php echo esc_url( admin_url( 'admin.php?page=jasanika-update-center' ) ); ?>"
			class="jasanika-version-widget__link"
		>
			<?php esc_html_e( '→ Open Update Center', 'jasanika' ); ?>
		</a>

	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Page Renderer
// ---------------------------------------------------------------------------

/**
 * Renders the Update Center admin page.
 */
function jasanika_admin_page_update_center(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}

	$theme     = wp_get_theme();
	$version   = $theme->get( 'Version' );
	$milestone = jasanika_update_center_version_to_milestone( $version );
	$changelog = jasanika_update_center_get_changelog();
	$modules   = jasanika_update_center_get_modules();
	$compat    = jasanika_update_center_get_compat();
	$notices   = get_option( 'jasanika_update_notices', array() );

	?>
	<div class="wrap jasanika-update-center">

		<!-- ================================================================
		     Header
		================================================================ -->
		<div class="jasanika-update-center__header">
			<span class="jasanika-update-center__icon dashicons dashicons-update"></span>
			<div>
				<h1 class="jasanika-update-center__title">
					<?php esc_html_e( 'Jasanika – Update Center', 'jasanika' ); ?>
				</h1>
				<p class="jasanika-update-center__subtitle">
					<?php esc_html_e( 'Version management and framework status overview.', 'jasanika' ); ?>
				</p>
			</div>
			<span class="jasanika-update-center__version-badge">
				<?php echo esc_html( $version ); ?>
			</span>
		</div>

		<!-- ================================================================
		     Update Notices
		================================================================ -->
		<?php if ( ! empty( $notices ) ) : ?>
			<div class="jasanika-uc-notices">
				<?php foreach ( $notices as $notice ) : ?>
					<?php
					$type = sanitize_key( $notice['type'] ?? 'milestone' );
					?>
					<div class="jasanika-uc-notice jasanika-uc-notice--<?php echo esc_attr( $type ); ?>">
						<span class="jasanika-uc-notice__icon dashicons
							<?php
							if ( 'milestone' === $type ) {
								echo 'dashicons-yes-alt';
							} elseif ( 'migration' === $type ) {
								echo 'dashicons-warning';
							} else {
								echo 'dashicons-admin-settings';
							}
							?>
						"></span>
						<div class="jasanika-uc-notice__body">
							<div class="jasanika-uc-notice__title">
								<?php echo esc_html( $notice['title'] ?? '' ); ?>
							</div>
							<div class="jasanika-uc-notice__text">
								<?php echo esc_html( $notice['message'] ?? '' ); ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- ================================================================
		     Version Overview
		================================================================ -->
		<div class="jasanika-uc-section">
			<h2 class="jasanika-uc-section__title"><?php esc_html_e( 'Version Overview', 'jasanika' ); ?></h2>
			<div class="jasanika-uc-version-grid">

				<div class="jasanika-uc-version-card">
					<div class="jasanika-uc-version-card__label">
						<?php esc_html_e( 'Current Theme Version', 'jasanika' ); ?>
					</div>
					<div class="jasanika-uc-version-card__value">
						<?php echo esc_html( $version ); ?>
					</div>
					<div class="jasanika-uc-version-card__sub">
						<?php esc_html_e( 'Active version', 'jasanika' ); ?>
					</div>
				</div>

				<div class="jasanika-uc-version-card">
					<div class="jasanika-uc-version-card__label">
						<?php esc_html_e( 'Installed Version', 'jasanika' ); ?>
					</div>
					<div class="jasanika-uc-version-card__value">
						<?php echo esc_html( $version ); ?>
					</div>
					<div class="jasanika-uc-version-card__sub">
						<?php esc_html_e( 'Last installed', 'jasanika' ); ?>
					</div>
				</div>

				<div class="jasanika-uc-version-card">
					<div class="jasanika-uc-version-card__label">
						<?php esc_html_e( 'Build Number', 'jasanika' ); ?>
					</div>
					<div class="jasanika-uc-version-card__value">
						<?php echo esc_html( (string) $milestone ); ?>
					</div>
					<div class="jasanika-uc-version-card__sub">
						M<?php echo esc_html( (string) $milestone ); ?>
					</div>
				</div>

				<div class="jasanika-uc-version-card">
					<div class="jasanika-uc-version-card__label">
						<?php esc_html_e( 'Release Date', 'jasanika' ); ?>
					</div>
					<div class="jasanika-uc-version-card__value">
						<?php
						$release_date = isset( $changelog[ $milestone ] ) ? $changelog[ $milestone ]['date'] : '—';
						echo esc_html( $release_date );
						?>
					</div>
					<div class="jasanika-uc-version-card__sub">
						<?php
						if ( isset( $changelog[ $milestone ] ) ) {
							echo esc_html( $changelog[ $milestone ]['name'] );
						}
						?>
					</div>
				</div>

			</div>
		</div>

		<!-- ================================================================
		     Theme Information
		================================================================ -->
		<div class="jasanika-uc-section">
			<h2 class="jasanika-uc-section__title"><?php esc_html_e( 'Theme Information', 'jasanika' ); ?></h2>
			<table class="jasanika-uc-info-table">
				<tbody>
					<tr>
						<td class="jasanika-uc-info-table__label"><?php esc_html_e( 'Theme Name', 'jasanika' ); ?></td>
						<td class="jasanika-uc-info-table__value"><?php echo esc_html( $theme->get( 'Name' ) ); ?></td>
					</tr>
					<tr>
						<td class="jasanika-uc-info-table__label"><?php esc_html_e( 'Theme Author', 'jasanika' ); ?></td>
						<td class="jasanika-uc-info-table__value"><?php echo esc_html( $theme->get( 'Author' ) ); ?></td>
					</tr>
					<tr>
						<td class="jasanika-uc-info-table__label"><?php esc_html_e( 'Theme URI', 'jasanika' ); ?></td>
						<td class="jasanika-uc-info-table__value">
							<a href="<?php echo esc_url( $theme->get( 'ThemeURI' ) ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $theme->get( 'ThemeURI' ) ); ?>
							</a>
						</td>
					</tr>
					<tr>
						<td class="jasanika-uc-info-table__label"><?php esc_html_e( 'Description', 'jasanika' ); ?></td>
						<td class="jasanika-uc-info-table__value"><?php echo esc_html( $theme->get( 'Description' ) ); ?></td>
					</tr>
					<tr>
						<td class="jasanika-uc-info-table__label"><?php esc_html_e( 'Version', 'jasanika' ); ?></td>
						<td class="jasanika-uc-info-table__value"><?php echo esc_html( $theme->get( 'Version' ) ); ?></td>
					</tr>
					<tr>
						<td class="jasanika-uc-info-table__label"><?php esc_html_e( 'Text Domain', 'jasanika' ); ?></td>
						<td class="jasanika-uc-info-table__value"><?php echo esc_html( $theme->get( 'TextDomain' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- ================================================================
		     Module Overview
		================================================================ -->
		<div class="jasanika-uc-section">
			<h2 class="jasanika-uc-section__title"><?php esc_html_e( 'Module Overview', 'jasanika' ); ?></h2>
			<div class="jasanika-uc-modules-grid">
				<?php foreach ( $modules as $module ) : ?>
					<?php $modifier = $module['active'] ? 'active' : 'inactive'; ?>
					<div class="jasanika-uc-module-card jasanika-uc-module-card--<?php echo esc_attr( $modifier ); ?>">
						<span class="jasanika-uc-module-card__status">
							<?php echo $module['active'] ? '✓' : '○'; ?>
						</span>
						<span class="jasanika-uc-module-card__label">
							<?php echo esc_html( $module['label'] ); ?>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- ================================================================
		     System Compatibility
		================================================================ -->
		<div class="jasanika-uc-section">
			<h2 class="jasanika-uc-section__title"><?php esc_html_e( 'System Compatibility', 'jasanika' ); ?></h2>
			<div class="jasanika-uc-compat-grid">
				<?php foreach ( $compat as $item ) : ?>
					<div class="jasanika-uc-compat-card">
						<div class="jasanika-uc-compat-card__label">
							<?php echo esc_html( $item['label'] ); ?>
						</div>
						<div class="jasanika-uc-compat-card__version">
							<?php echo esc_html( $item['version'] ); ?>
						</div>
						<span class="jasanika-uc-compat-badge jasanika-uc-compat-badge--<?php echo esc_attr( $item['status'] ); ?>">
							<?php echo esc_html( $item['note'] ); ?>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- ================================================================
		     Changelog Viewer
		================================================================ -->
		<div class="jasanika-uc-section">
			<h2 class="jasanika-uc-section__title"><?php esc_html_e( 'Changelog', 'jasanika' ); ?></h2>
			<div class="jasanika-uc-changelog">
				<?php foreach ( $changelog as $num => $entry ) : ?>
					<?php $is_current = ( $num === $milestone ); ?>
					<div class="jasanika-uc-changelog-item<?php echo $is_current ? ' jasanika-uc-changelog-item--current' : ''; ?>">
						<span class="jasanika-uc-changelog-item__milestone">M<?php echo esc_html( (string) $num ); ?></span>
						<span class="jasanika-uc-changelog-item__name"><?php echo esc_html( $entry['name'] ); ?></span>
						<span class="jasanika-uc-changelog-item__version"><?php echo esc_html( $entry['version'] ); ?></span>
						<?php if ( $is_current ) : ?>
							<span class="jasanika-uc-changelog-item__badge"><?php esc_html_e( 'Current', 'jasanika' ); ?></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- ================================================================
		     Release History
		================================================================ -->
		<div class="jasanika-uc-section">
			<h2 class="jasanika-uc-section__title"><?php esc_html_e( 'Release History', 'jasanika' ); ?></h2>
			<table class="jasanika-uc-release-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Version', 'jasanika' ); ?></th>
						<th><?php esc_html_e( 'Date', 'jasanika' ); ?></th>
						<th><?php esc_html_e( 'Milestone', 'jasanika' ); ?></th>
						<th><?php esc_html_e( 'Description', 'jasanika' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $changelog as $num => $entry ) : ?>
						<tr>
							<td><?php echo esc_html( $entry['version'] ); ?></td>
							<td><?php echo esc_html( $entry['date'] ); ?></td>
							<td>M<?php echo esc_html( (string) $num ); ?></td>
							<td><?php echo esc_html( $entry['name'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<!-- ================================================================
		     Export Version Report
		================================================================ -->
		<div class="jasanika-uc-section">
			<h2 class="jasanika-uc-section__title"><?php esc_html_e( 'Export Version Report', 'jasanika' ); ?></h2>
			<p class="jasanika-uc-export__description">
				<?php esc_html_e( 'Download a full version report containing theme info, module status and system compatibility.', 'jasanika' ); ?>
			</p>
			<div class="jasanika-uc-export">

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'jasanika_export_report' ); ?>
					<input type="hidden" name="action" value="jasanika_export_report_txt">
					<button type="submit" class="jasanika-uc-btn jasanika-uc-btn--txt">
						<span class="dashicons dashicons-media-text"></span>
						<?php esc_html_e( 'Export as TXT', 'jasanika' ); ?>
					</button>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'jasanika_export_report' ); ?>
					<input type="hidden" name="action" value="jasanika_export_report_json">
					<button type="submit" class="jasanika-uc-btn jasanika-uc-btn--json">
						<span class="dashicons dashicons-media-code"></span>
						<?php esc_html_e( 'Export as JSON', 'jasanika' ); ?>
					</button>
				</form>

			</div>
		</div>

	</div>
	<?php
}
