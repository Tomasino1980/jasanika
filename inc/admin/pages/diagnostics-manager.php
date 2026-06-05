<?php

/**
 * Jasanika Admin – Diagnostics Manager Page
 *
 * Provides a complete diagnostics center for administrators to quickly
 * identify configuration issues with the Jasanika theme.
 *
 * Sections:
 *  – System Information
 *  – Theme Health Checks
 *  – Menu Checks
 *  – Homepage Checks
 *  – WooCommerce Checks
 *  – Content Statistics
 *  – Security Checks
 *  – Performance Checks
 *  – Quick Fix Links
 *  – Export Diagnostic Report (TXT / JSON)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_enqueue_scripts', 'jasanika_diagnostics_enqueue' );
add_action( 'admin_post_jasanika_export_diagnostics', 'jasanika_handle_export_diagnostics' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue Diagnostics stylesheet only on the Diagnostics page.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_diagnostics_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-diagnostics' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'jasanika-diagnostics',
		get_template_directory_uri() . '/assets/css/admin/diagnostics.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}

// ---------------------------------------------------------------------------
// Data Collector
// ---------------------------------------------------------------------------

/**
 * Collect all diagnostic data and return it as a structured array.
 *
 * @return array<string, mixed>
 */
function jasanika_diagnostics_collect(): array {

	global $wpdb;

	// ── System Information ─────────────────────────────────────────────────

	$mysql_version = $wpdb->get_var( 'SELECT VERSION()' );

	$wc_version = defined( 'WC_VERSION' ) ? WC_VERSION : null;

	$system = array(
		'wordpress_version' => get_bloginfo( 'version' ),
		'php_version'       => PHP_VERSION,
		'theme_version'     => wp_get_theme()->get( 'Version' ),
		'woocommerce'       => $wc_version,
		'mysql_version'     => $mysql_version ? (string) $mysql_version : '',
	);

	// ── Theme Health Checks ────────────────────────────────────────────────

	$settings          = get_option( 'jasanika_settings', array() );
	$seo_settings      = get_option( 'jasanika_seo_settings', array() );
	$cookie_settings   = get_option( 'jasanika_cookie_consent_settings', array() );
	$homepage_sections = get_option( 'jasanika_settings', array() );

	$health = array(
		'logo'              => ! empty( $settings['logo_url'] ),
		'homepage_builder'  => ! empty( $homepage_sections ),
		'footer'            => ! empty( $settings['footer_layout'] ) || ! empty( get_option( 'jasanika_footer_builder' ) ),
		'slider'            => count( get_option( 'jasanika_slides', array() ) ) > 0,
		'seo'               => ! empty( $seo_settings ),
		'cookie_manager'    => ! empty( $cookie_settings ),
	);

	// ── Menu Checks ───────────────────────────────────────────────────────

	$locations  = get_nav_menu_locations();
	$menus = array(
		'primary' => ! empty( $locations['primary'] ) && (bool) wp_get_nav_menu_object( $locations['primary'] ),
		'footer'  => ! empty( $locations['footer'] )  && (bool) wp_get_nav_menu_object( $locations['footer'] ),
	);

	// ── Homepage Checks ───────────────────────────────────────────────────

	$front_page_id      = (int) get_option( 'page_on_front' );
	$show_on_front      = get_option( 'show_on_front' );
	$theme_dir          = get_template_directory();
	$front_template_ok  = file_exists( $theme_dir . '/front-page.php' );

	$hb_any_enabled = false;
	if ( function_exists( 'jasanika_homepage_sections_registry' ) ) {
		foreach ( jasanika_homepage_sections_registry() as $key => $defaults ) {
			$opt = $settings[ 'hb_' . $key . '_enabled' ] ?? null;
			$enabled = ( null !== $opt ) ? (bool) $opt : $defaults['default_enabled'];
			if ( $enabled ) {
				$hb_any_enabled = true;
				break;
			}
		}
	}

	$homepage = array(
		'assigned'          => 'page' === $show_on_front && $front_page_id > 0,
		'template_exists'   => $front_template_ok,
		'builder_active'    => $hb_any_enabled,
	);

	// ── WooCommerce Checks ────────────────────────────────────────────────

	$wc_installed       = defined( 'WC_VERSION' );
	$wc_shop_id         = $wc_installed ? (int) wc_get_page_id( 'shop' )      : 0;
	$wc_cart_id         = $wc_installed ? (int) wc_get_page_id( 'cart' )      : 0;
	$wc_checkout_id     = $wc_installed ? (int) wc_get_page_id( 'checkout' )  : 0;
	$wc_myaccount_id    = $wc_installed ? (int) wc_get_page_id( 'myaccount' ) : 0;

	$woocommerce = array(
		'installed'   => $wc_installed,
		'shop'        => $wc_installed && $wc_shop_id     > 0 && get_post( $wc_shop_id ),
		'cart'        => $wc_installed && $wc_cart_id     > 0 && get_post( $wc_cart_id ),
		'checkout'    => $wc_installed && $wc_checkout_id > 0 && get_post( $wc_checkout_id ),
		'my_account'  => $wc_installed && $wc_myaccount_id > 0 && get_post( $wc_myaccount_id ),
	);

	// ── Content Statistics ────────────────────────────────────────────────

	$posts_count        = (int) wp_count_posts( 'post' )->publish;
	$pages_count        = (int) wp_count_posts( 'page' )->publish;
	$products_count     = post_type_exists( 'product' ) ? (int) wp_count_posts( 'product' )->publish : 0;
	$testimonials_count = post_type_exists( 'jasanika_testimonial' )
		? (int) wp_count_posts( 'jasanika_testimonial' )->publish
		: 0;

	$subscribers_raw = get_option( 'jasanika_newsletter_subscribers', array() );
	$subscribers     = is_array( $subscribers_raw ) ? count( $subscribers_raw ) : 0;

	$content = array(
		'posts'        => $posts_count,
		'pages'        => $pages_count,
		'products'     => $products_count,
		'testimonials' => $testimonials_count,
		'subscribers'  => $subscribers,
	);

	// ── Security Checks ───────────────────────────────────────────────────

	$security = array(
		'debug_mode'      => defined( 'WP_DEBUG' ) && WP_DEBUG,
		'file_editing'    => ! ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ),
		'https'           => is_ssl(),
	);

	// ── Performance Information ───────────────────────────────────────────

	$performance = array(
		'memory_limit'     => ini_get( 'memory_limit' ),
		'upload_limit'     => ini_get( 'upload_max_filesize' ),
		'max_exec_time'    => ini_get( 'max_execution_time' ),
	);

	$modules = array(
	'registered' => function_exists( 'jasanika_get_registered_modules' ) ? jasanika_get_registered_modules() : array(),
	'loaded'     => function_exists( 'jasanika_get_loaded_modules' ) ? jasanika_get_loaded_modules() : array(),
);

return compact( 'system', 'health', 'menus', 'homepage', 'woocommerce', 'content', 'security', 'performance', 'modules' );
}

// ---------------------------------------------------------------------------
// Export Handler
// ---------------------------------------------------------------------------

/**
 * Handle the export diagnostic report request.
 * Supports 'json' and 'txt' formats via POST field 'jasanika_diag_format'.
 */
function jasanika_handle_export_diagnostics(): void {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'jasanika' ) );
	}

	check_admin_referer( 'jasanika_export_diagnostics' );

	$format = isset( $_POST['jasanika_diag_format'] ) && 'txt' === $_POST['jasanika_diag_format'] ? 'txt' : 'json';
	$data   = jasanika_diagnostics_collect();

	if ( 'json' === $format ) {
		$output   = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		$filename = 'jasanika-diagnostics-' . gmdate( 'Y-m-d' ) . '.json';
		header( 'Content-Type: application/json; charset=utf-8' );
	} else {
		$output   = jasanika_diagnostics_build_txt( $data );
		$filename = 'jasanika-diagnostics-' . gmdate( 'Y-m-d' ) . '.txt';
		header( 'Content-Type: text/plain; charset=utf-8' );
	}

	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Content-Length: ' . strlen( (string) $output ) );
	header( 'Cache-Control: no-cache, no-store, must-revalidate' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}

/**
 * Build a human-readable TXT report from diagnostic data.
 *
 * @param array<string, mixed> $data Diagnostic data.
 * @return string
 */
function jasanika_diagnostics_build_txt( array $data ): string {

	$ok  = 'OK';
	$nok = 'ATTENTION REQUIRED';
	$yes = 'Yes';
	$no  = 'No';

	$lines = array();

	$lines[] = '========================================';
	$lines[] = 'JASANIKA – DIAGNOSTIC REPORT';
	$lines[] = 'Generated: ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC';
	$lines[] = '========================================';
	$lines[] = '';

	// System.
	$lines[] = '--- System Information ---';
	$lines[] = 'WordPress Version : ' . $data['system']['wordpress_version'];
	$lines[] = 'PHP Version       : ' . $data['system']['php_version'];
	$lines[] = 'Theme Version     : ' . $data['system']['theme_version'];
	$lines[] = 'WooCommerce       : ' . ( $data['system']['woocommerce'] ?? 'Not installed' );
	$lines[] = 'MySQL Version     : ' . $data['system']['mysql_version'];
	$lines[] = '';

	// Health.
	$lines[] = '--- Theme Health Checks ---';
	foreach ( $data['health'] as $key => $status ) {
		$label   = ucwords( str_replace( '_', ' ', $key ) );
		$lines[] = $label . ' : ' . ( $status ? $ok : $nok );
	}
	$lines[] = '';

	// Menus.
	$lines[] = '--- Menu Checks ---';
	$lines[] = 'Primary Menu : ' . ( $data['menus']['primary'] ? $ok : $nok );
	$lines[] = 'Footer Menu  : ' . ( $data['menus']['footer']  ? $ok : $nok );
	$lines[] = '';

	// Homepage.
	$lines[] = '--- Homepage Checks ---';
	$lines[] = 'Homepage Assigned     : ' . ( $data['homepage']['assigned']        ? $yes : $no );
	$lines[] = 'Front Page Template   : ' . ( $data['homepage']['template_exists'] ? $yes : $no );
	$lines[] = 'Homepage Builder Active: ' . ( $data['homepage']['builder_active'] ? $yes : $no );
	$lines[] = '';

	// WooCommerce.
	$lines[] = '--- WooCommerce Checks ---';
	$lines[] = 'WooCommerce Installed : ' . ( $data['woocommerce']['installed']  ? $yes : $no );
	$lines[] = 'Shop Page Exists      : ' . ( $data['woocommerce']['shop']       ? $ok : $nok );
	$lines[] = 'Cart Page Exists      : ' . ( $data['woocommerce']['cart']       ? $ok : $nok );
	$lines[] = 'Checkout Page Exists  : ' . ( $data['woocommerce']['checkout']   ? $ok : $nok );
	$lines[] = 'My Account Page Exists: ' . ( $data['woocommerce']['my_account'] ? $ok : $nok );
	$lines[] = '';

	// Content.
	$lines[] = '--- Content Statistics ---';
	$lines[] = 'Posts        : ' . $data['content']['posts'];
	$lines[] = 'Pages        : ' . $data['content']['pages'];
	$lines[] = 'Products     : ' . $data['content']['products'];
	$lines[] = 'Testimonials : ' . $data['content']['testimonials'];
	$lines[] = 'Subscribers  : ' . $data['content']['subscribers'];
	$lines[] = '';

	// Security.
	$lines[] = '--- Security Checks ---';
	$lines[] = 'Debug Mode     : ' . ( $data['security']['debug_mode']   ? 'Enabled (!)' : 'Disabled' );
	$lines[] = 'File Editing   : ' . ( $data['security']['file_editing'] ? 'Enabled (!)'  : 'Disabled' );
	$lines[] = 'HTTPS          : ' . ( $data['security']['https']        ? $yes           : $no );
	$lines[] = '';

	// Performance.
	$lines[] = '--- Performance Checks ---';
	$lines[] = 'Memory Limit     : ' . $data['performance']['memory_limit'];
	$lines[] = 'Upload Limit     : ' . $data['performance']['upload_limit'];
	$lines[] = 'Max Exec Time    : ' . $data['performance']['max_exec_time'] . 's';
	$lines[] = '';
	$lines[] = '========================================';

	return implode( "\n", $lines );
}

// ---------------------------------------------------------------------------
// Page Renderer
// ---------------------------------------------------------------------------

/**
 * Render the Diagnostics Manager admin page.
 */
function jasanika_admin_page_diagnostics(): void {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}

	$data = jasanika_diagnostics_collect();
	?>
	<div class="wrap jasanika-diag">

		<!-- Header -->
		<div class="jasanika-diag__header">
			<span class="dashicons dashicons-heart jasanika-diag__header-icon"></span>
			<div>
				<h1 class="jasanika-diag__title"><?php esc_html_e( 'System Diagnostics', 'jasanika' ); ?></h1>
				<p class="jasanika-diag__subtitle"><?php esc_html_e( 'Overview of your Jasanika theme configuration and system health.', 'jasanika' ); ?></p>
			</div>
		</div>

		<div class="jasanika-diag__layout">

			<!-- ── Left column ──────────────────────────────────────────── -->
			<div class="jasanika-diag__col">

				<!-- System Information -->
				<div class="jasanika-diag__card">
					<h2 class="jasanika-diag__card-title">
						<span class="dashicons dashicons-info-outline"></span>
						<?php esc_html_e( 'System Information', 'jasanika' ); ?>
					</h2>
					<table class="jasanika-diag__table">
						<tr>
							<td><?php esc_html_e( 'WordPress Version', 'jasanika' ); ?></td>
							<td><span class="jasanika-diag__value"><?php echo esc_html( $data['system']['wordpress_version'] ); ?></span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'PHP Version', 'jasanika' ); ?></td>
							<td><span class="jasanika-diag__value"><?php echo esc_html( $data['system']['php_version'] ); ?></span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Theme Version', 'jasanika' ); ?></td>
							<td><span class="jasanika-diag__value"><?php echo esc_html( $data['system']['theme_version'] ); ?></span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'WooCommerce', 'jasanika' ); ?></td>
							<td>
								<?php if ( $data['system']['woocommerce'] ) : ?>
									<span class="jasanika-diag__value"><?php echo esc_html( $data['system']['woocommerce'] ); ?></span>
								<?php else : ?>
									<span class="jasanika-diag__badge jasanika-diag__badge--warn"><?php esc_html_e( 'Not installed', 'jasanika' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'MySQL Version', 'jasanika' ); ?></td>
							<td><span class="jasanika-diag__value"><?php echo esc_html( $data['system']['mysql_version'] ); ?></span></td>
						</tr>
					</table>
				</div>

				<!-- Theme Health Checks -->
				<div class="jasanika-diag__card">
					<h2 class="jasanika-diag__card-title">
						<span class="dashicons dashicons-yes-alt"></span>
						<?php esc_html_e( 'Theme Health Checks', 'jasanika' ); ?>
					</h2>
					<table class="jasanika-diag__table">
						<?php
						$health_labels = array(
							'logo'             => __( 'Logo configured', 'jasanika' ),
							'homepage_builder' => __( 'Homepage Builder configured', 'jasanika' ),
							'footer'           => __( 'Footer configured', 'jasanika' ),
							'slider'           => __( 'Slider configured', 'jasanika' ),
							'seo'              => __( 'SEO configured', 'jasanika' ),
							'cookie_manager'   => __( 'Cookie Manager configured', 'jasanika' ),
						);
						foreach ( $health_labels as $key => $label ) :
							$ok = ! empty( $data['health'][ $key ] );
							?>
							<tr>
								<td><?php echo esc_html( $label ); ?></td>
								<td><?php jasanika_diag_status_badge( $ok ); ?></td>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>

				<!-- Menu Checks -->
				<div class="jasanika-diag__card">
					<h2 class="jasanika-diag__card-title">
						<span class="dashicons dashicons-menu"></span>
						<?php esc_html_e( 'Menu Checks', 'jasanika' ); ?>
					</h2>
					<table class="jasanika-diag__table">
						<tr>
							<td><?php esc_html_e( 'Primary Menu assigned', 'jasanika' ); ?></td>
							<td><?php jasanika_diag_status_badge( $data['menus']['primary'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Footer Menu assigned', 'jasanika' ); ?></td>
							<td><?php jasanika_diag_status_badge( $data['menus']['footer'] ); ?></td>
						</tr>
					</table>
				</div>

				<!-- Homepage Checks -->
				<div class="jasanika-diag__card">
					<h2 class="jasanika-diag__card-title">
						<span class="dashicons dashicons-admin-home"></span>
						<?php esc_html_e( 'Homepage Checks', 'jasanika' ); ?>
					</h2>
					<table class="jasanika-diag__table">
						<tr>
							<td><?php esc_html_e( 'Homepage assigned', 'jasanika' ); ?></td>
							<td><?php jasanika_diag_status_badge( $data['homepage']['assigned'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Front Page template available', 'jasanika' ); ?></td>
							<td><?php jasanika_diag_status_badge( $data['homepage']['template_exists'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Homepage Builder active', 'jasanika' ); ?></td>
							<td><?php jasanika_diag_status_badge( $data['homepage']['builder_active'] ); ?></td>
						</tr>
					</table>
				</div>

			</div><!-- /.jasanika-diag__col -->

			<!-- ── Right column ─────────────────────────────────────────── -->
			<div class="jasanika-diag__col">

				<!-- WooCommerce Checks -->
				<div class="jasanika-diag__card">
					<h2 class="jasanika-diag__card-title">
						<span class="dashicons dashicons-cart"></span>
						<?php esc_html_e( 'WooCommerce Checks', 'jasanika' ); ?>
					</h2>
					<table class="jasanika-diag__table">
						<tr>
							<td><?php esc_html_e( 'WooCommerce installed', 'jasanika' ); ?></td>
							<td><?php jasanika_diag_status_badge( $data['woocommerce']['installed'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Shop page exists', 'jasanika' ); ?></td>
							<td><?php jasanika_diag_status_badge( $data['woocommerce']['shop'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Cart page exists', 'jasanika' ); ?></td>
							<td><?php jasanika_diag_status_badge( $data['woocommerce']['cart'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Checkout page exists', 'jasanika' ); ?></td>
							<td><?php jasanika_diag_status_badge( $data['woocommerce']['checkout'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'My Account page exists', 'jasanika' ); ?></td>
							<td><?php jasanika_diag_status_badge( $data['woocommerce']['my_account'] ); ?></td>
						</tr>
					</table>
				</div>

				<!-- Content Statistics -->
				<div class="jasanika-diag__card">
					<h2 class="jasanika-diag__card-title">
						<span class="dashicons dashicons-chart-bar"></span>
						<?php esc_html_e( 'Content Statistics', 'jasanika' ); ?>
					</h2>
					<div class="jasanika-diag__stats">
						<?php
						$stats = array(
							array( 'label' => __( 'Posts', 'jasanika' ),       'value' => $data['content']['posts'] ),
							array( 'label' => __( 'Pages', 'jasanika' ),       'value' => $data['content']['pages'] ),
							array( 'label' => __( 'Products', 'jasanika' ),    'value' => $data['content']['products'] ),
							array( 'label' => __( 'Testimonials', 'jasanika' ),'value' => $data['content']['testimonials'] ),
							array( 'label' => __( 'Subscribers', 'jasanika' ), 'value' => $data['content']['subscribers'] ),
						);
						foreach ( $stats as $stat ) :
							?>
							<div class="jasanika-diag__stat">
								<span class="jasanika-diag__stat-value"><?php echo esc_html( (string) $stat['value'] ); ?></span>
								<span class="jasanika-diag__stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Security Checks -->
				<div class="jasanika-diag__card">
					<h2 class="jasanika-diag__card-title">
						<span class="dashicons dashicons-shield"></span>
						<?php esc_html_e( 'Security Checks', 'jasanika' ); ?>
					</h2>
					<table class="jasanika-diag__table">
						<tr>
							<td><?php esc_html_e( 'Debug Mode', 'jasanika' ); ?></td>
							<td>
								<?php if ( $data['security']['debug_mode'] ) : ?>
									<span class="jasanika-diag__badge jasanika-diag__badge--warn">
										<span class="dashicons dashicons-warning"></span>
										<?php esc_html_e( 'Enabled', 'jasanika' ); ?>
									</span>
								<?php else : ?>
									<span class="jasanika-diag__badge jasanika-diag__badge--ok">
										<span class="dashicons dashicons-yes"></span>
										<?php esc_html_e( 'Disabled', 'jasanika' ); ?>
									</span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'File Editing', 'jasanika' ); ?></td>
							<td>
								<?php if ( $data['security']['file_editing'] ) : ?>
									<span class="jasanika-diag__badge jasanika-diag__badge--warn">
										<span class="dashicons dashicons-warning"></span>
										<?php esc_html_e( 'Enabled', 'jasanika' ); ?>
									</span>
								<?php else : ?>
									<span class="jasanika-diag__badge jasanika-diag__badge--ok">
										<span class="dashicons dashicons-yes"></span>
										<?php esc_html_e( 'Disabled', 'jasanika' ); ?>
									</span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'HTTPS', 'jasanika' ); ?></td>
							<td><?php jasanika_diag_status_badge( $data['security']['https'] ); ?></td>
						</tr>
					</table>
				</div>

				<!-- Performance Checks -->
				<div class="jasanika-diag__card">
					<h2 class="jasanika-diag__card-title">
						<span class="dashicons dashicons-performance"></span>
						<?php esc_html_e( 'Performance Checks', 'jasanika' ); ?>
					</h2>
					<table class="jasanika-diag__table">
						<tr>
							<td><?php esc_html_e( 'Memory Limit', 'jasanika' ); ?></td>
							<td><span class="jasanika-diag__value"><?php echo esc_html( $data['performance']['memory_limit'] ); ?></span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Upload Limit', 'jasanika' ); ?></td>
							<td><span class="jasanika-diag__value"><?php echo esc_html( $data['performance']['upload_limit'] ); ?></span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Max Execution Time', 'jasanika' ); ?></td>
							<td><span class="jasanika-diag__value"><?php echo esc_html( $data['performance']['max_exec_time'] ); ?>s</span></td>
						</tr>
					</table>
				</div>

				<!-- Modules -->
				<div class="jasanika-diag__card">
					<h2 class="jasanika-diag__card-title">
						<span class="dashicons dashicons-networking"></span>
						<?php esc_html_e( 'Modules', 'jasanika' ); ?>
					</h2>
					<table class="jasanika-diag__table">
						<tr>
							<th><?php esc_html_e( 'ID', 'jasanika' ); ?></th>
							<th><?php esc_html_e( 'Name', 'jasanika' ); ?></th>
							<th><?php esc_html_e( 'Version', 'jasanika' ); ?></th>
							<th><?php esc_html_e( 'Status', 'jasanika' ); ?></th>
						</tr>
						<?php
						$mods = $data['modules']['registered'] ?? array();
						foreach ( $mods as $m ) :
							$status = ! empty( $m['loaded'] ) ? esc_html__( 'Loaded', 'jasanika' ) : ( ! empty( $m['enabled'] ) ? esc_html__( 'Registered', 'jasanika' ) : esc_html__( 'Disabled', 'jasanika' ) );
							?>
							<tr>
								<td><?php echo esc_html( $m['id'] ?? '' ); ?></td>
								<td><?php echo esc_html( $m['name'] ?? '' ); ?></td>
								<td><?php echo esc_html( $m['version'] ?? '' ); ?></td>
								<td><?php echo $status; ?></td>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>

				<!-- Quick Fix Links -->
				<div class="jasanika-diag__card">
					<h2 class="jasanika-diag__card-title">
						<span class="dashicons dashicons-admin-tools"></span>
						<?php esc_html_e( 'Quick Fix Links', 'jasanika' ); ?>
					</h2>
					<div class="jasanika-diag__quick-links">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=jasanika-theme-settings' ) ); ?>" class="jasanika-diag__btn">
							<span class="dashicons dashicons-admin-settings"></span>
							<?php esc_html_e( 'Open Theme Settings', 'jasanika' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=jasanika-menu-manager' ) ); ?>" class="jasanika-diag__btn">
							<span class="dashicons dashicons-menu"></span>
							<?php esc_html_e( 'Open Menu Manager', 'jasanika' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=jasanika-seo-manager' ) ); ?>" class="jasanika-diag__btn">
							<span class="dashicons dashicons-search"></span>
							<?php esc_html_e( 'Open SEO Manager', 'jasanika' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=jasanika-cookie-manager' ) ); ?>" class="jasanika-diag__btn">
							<span class="dashicons dashicons-privacy"></span>
							<?php esc_html_e( 'Open Cookie Manager', 'jasanika' ); ?>
						</a>
					</div>
				</div>

				<!-- Export Diagnostic Report -->
				<div class="jasanika-diag__card">
					<h2 class="jasanika-diag__card-title">
						<span class="dashicons dashicons-download"></span>
						<?php esc_html_e( 'Export Diagnostic Report', 'jasanika' ); ?>
					</h2>
					<p class="jasanika-diag__export-desc">
						<?php esc_html_e( 'Download a full diagnostic report in JSON or TXT format.', 'jasanika' ); ?>
					</p>
					<div class="jasanika-diag__export-actions">

						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php wp_nonce_field( 'jasanika_export_diagnostics' ); ?>
							<input type="hidden" name="action" value="jasanika_export_diagnostics">
							<input type="hidden" name="jasanika_diag_format" value="json">
							<button type="submit" class="jasanika-diag__btn jasanika-diag__btn--export">
								<span class="dashicons dashicons-media-code"></span>
								<?php esc_html_e( 'Export JSON', 'jasanika' ); ?>
							</button>
						</form>

						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php wp_nonce_field( 'jasanika_export_diagnostics' ); ?>
							<input type="hidden" name="action" value="jasanika_export_diagnostics">
							<input type="hidden" name="jasanika_diag_format" value="txt">
							<button type="submit" class="jasanika-diag__btn jasanika-diag__btn--export">
								<span class="dashicons dashicons-media-text"></span>
								<?php esc_html_e( 'Export TXT', 'jasanika' ); ?>
							</button>
						</form>

					</div>
				</div>

			</div><!-- /.jasanika-diag__col -->

		</div><!-- /.jasanika-diag__layout -->

	</div><!-- /.wrap.jasanika-diag -->
	<?php
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Echo a status badge (OK or Attention Required).
 *
 * @param bool $ok True for OK, false for Attention Required.
 */
function jasanika_diag_status_badge( bool $ok ): void {
	if ( $ok ) {
		echo '<span class="jasanika-diag__badge jasanika-diag__badge--ok"><span class="dashicons dashicons-yes"></span>' . esc_html__( 'OK', 'jasanika' ) . '</span>';
	} else {
		echo '<span class="jasanika-diag__badge jasanika-diag__badge--warn"><span class="dashicons dashicons-warning"></span>' . esc_html__( 'Attention Required', 'jasanika' ) . '</span>';
	}
}
