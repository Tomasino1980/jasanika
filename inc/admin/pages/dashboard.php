<?php

/**
 * Jasanika Admin – Dashboard Page
 *
 * Renders the Jasanika → Dashboard overview page.
 * Sections: Statistics, Recent Posts, Recent Orders, System Status,
 *           Configuration Status, Quick Actions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_enqueue_scripts', 'jasanika_dashboard_enqueue' );

/**
 * Enqueue dashboard stylesheet only on the Jasanika Dashboard page.
 *
 * @param string $hook Current admin page hook suffix.
 */
function jasanika_dashboard_enqueue( string $hook ): void {
	if ( 'toplevel_page_jasanika' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'jasanika-dashboard',
		get_template_directory_uri() . '/assets/css/admin/dashboard.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}

/**
 * Renders the Dashboard admin page.
 */
function jasanika_admin_page_dashboard(): void {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}

	?>
	<div class="wrap jasanika-dashboard">

		<!-- Header -->
		<div class="jasanika-dashboard__header">
			<span class="jasanika-dashboard__icon dashicons dashicons-store"></span>
			<div>
				<h1 class="jasanika-dashboard__title"><?php esc_html_e( 'Jasanika Dashboard', 'jasanika' ); ?></h1>
				<p class="jasanika-dashboard__subtitle"><?php esc_html_e( 'Central overview of the Jasanika theme.', 'jasanika' ); ?></p>
			</div>
		</div>

		<!-- Statistics Row -->
		<div class="jasanika-dashboard__section">
			<h2 class="jasanika-dashboard__section-title"><?php esc_html_e( 'Statistics', 'jasanika' ); ?></h2>
			<?php jasanika_dashboard_widget_stats(); ?>
		</div>

		<!-- Activity Row: Recent Posts + Recent Orders -->
		<div class="jasanika-dashboard__section">
			<h2 class="jasanika-dashboard__section-title"><?php esc_html_e( 'Recent Activity', 'jasanika' ); ?></h2>
			<div class="jasanika-activity-grid">
				<?php jasanika_dashboard_widget_recent_posts(); ?>
				<?php jasanika_dashboard_widget_recent_orders(); ?>
			</div>
		</div>

		<!-- System Status -->
		<div class="jasanika-dashboard__section">
			<h2 class="jasanika-dashboard__section-title"><?php esc_html_e( 'System Status', 'jasanika' ); ?></h2>
			<?php jasanika_dashboard_widget_system_status(); ?>
		</div>

		<!-- Configuration Status -->
		<div class="jasanika-dashboard__section">
			<h2 class="jasanika-dashboard__section-title"><?php esc_html_e( 'Configuration Status', 'jasanika' ); ?></h2>
			<?php jasanika_dashboard_widget_config_status(); ?>
		</div>

		<!-- Quick Actions -->
		<div class="jasanika-dashboard__section">
			<h2 class="jasanika-dashboard__section-title"><?php esc_html_e( 'Quick Actions', 'jasanika' ); ?></h2>
			<?php jasanika_dashboard_widget_quick_actions(); ?>
		</div>

	</div>
	<?php
}
