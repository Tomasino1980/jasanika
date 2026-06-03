<?php

/**
 * Jasanika Admin – Admin Bootstrap
 *
 * Registers the Jasanika top-level menu and all sub-pages in WordPress Admin.
 * Routes each menu entry to its dedicated page file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/admin/dashboard-widgets.php';
require_once get_template_directory() . '/inc/admin/pages/dashboard.php';
require_once get_template_directory() . '/inc/admin/pages/theme-settings.php';
require_once get_template_directory() . '/inc/admin/pages/color-settings.php';
require_once get_template_directory() . '/inc/admin/pages/logo-settings.php';
require_once get_template_directory() . '/inc/admin/pages/homepage-builder.php';
require_once get_template_directory() . '/inc/admin/pages/menu-manager.php';
require_once get_template_directory() . '/inc/admin/pages/slider-manager.php';
require_once get_template_directory() . '/inc/admin/pages/testimonials-manager.php';
require_once get_template_directory() . '/inc/admin/pages/newsletter-manager.php';
require_once get_template_directory() . '/inc/admin/pages/seo-manager.php';
require_once get_template_directory() . '/inc/admin/pages/cookie-manager.php';
require_once get_template_directory() . '/inc/admin/pages/backup-manager.php';
require_once get_template_directory() . '/inc/admin/pages/diagnostics-manager.php';
require_once get_template_directory() . '/inc/admin/pages/profile-manager.php';
require_once get_template_directory() . '/inc/admin/pages/maintenance-manager.php';
require_once get_template_directory() . '/inc/admin/pages/theme-presets.php';
require_once get_template_directory() . '/inc/admin/pages/update-center.php';

add_action( 'admin_menu', 'jasanika_register_admin_menu' );

/**
 * Registers the Jasanika top-level menu and its sub-pages.
 */
function jasanika_register_admin_menu(): void {

	// Top-level menu – renders the Dashboard page.
	add_menu_page(
		__( 'Jasanika', 'jasanika' ),
		__( 'Jasanika', 'jasanika' ),
		'manage_options',
		'jasanika',
		'jasanika_admin_page_dashboard',
		'dashicons-store',
		60
	);

	// Dashboard sub-page (mirrors the top-level entry).
	add_submenu_page(
		'jasanika',
		__( 'Dashboard', 'jasanika' ),
		__( 'Dashboard', 'jasanika' ),
		'manage_options',
		'jasanika',
		'jasanika_admin_page_dashboard'
	);

	// Theme Settings sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Theme Settings', 'jasanika' ),
		__( 'Theme Settings', 'jasanika' ),
		'manage_options',
		'jasanika-theme-settings',
		'jasanika_admin_page_theme_settings'
	);

	// Color Settings sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Color Settings', 'jasanika' ),
		__( 'Color Settings', 'jasanika' ),
		'manage_options',
		'jasanika-color-settings',
		'jasanika_admin_page_color_settings'
	);

	// Logo Settings sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Logo Settings', 'jasanika' ),
		__( 'Logo Settings', 'jasanika' ),
		'manage_options',
		'jasanika-logo-settings',
		'jasanika_admin_page_logo_settings'
	);

	// Homepage Builder sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Homepage Builder', 'jasanika' ),
		__( 'Homepage Builder', 'jasanika' ),
		'manage_options',
		'jasanika-homepage-builder',
		'jasanika_admin_page_homepage_builder'
	);

	// Menu Manager sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Menu Manager', 'jasanika' ),
		__( 'Menu Manager', 'jasanika' ),
		'manage_options',
		'jasanika-menu-manager',
		'jasanika_admin_page_menu_manager'
	);

	// Slider Manager sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Slider Manager', 'jasanika' ),
		__( 'Slider Manager', 'jasanika' ),
		'manage_options',
		'jasanika-slider-manager',
		'jasanika_admin_page_slider_manager'
	);

	// Testimonials Manager sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Testimonials Manager', 'jasanika' ),
		__( 'Testimonials Manager', 'jasanika' ),
		'manage_options',
		'jasanika-testimonials-manager',
		'jasanika_admin_page_testimonials_manager'
	);

	// Newsletter Manager sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Newsletter Manager', 'jasanika' ),
		__( 'Newsletter Manager', 'jasanika' ),
		'manage_options',
		'jasanika-newsletter-manager',
		'jasanika_admin_page_newsletter_manager'
	);

	// SEO Manager sub-page.
	add_submenu_page(
		'jasanika',
		__( 'SEO Manager', 'jasanika' ),
		__( 'SEO Manager', 'jasanika' ),
		'manage_options',
		'jasanika-seo-manager',
		'jasanika_admin_page_seo_manager'
	);

	// Cookie Manager sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Cookie Manager', 'jasanika' ),
		__( 'Cookie Manager', 'jasanika' ),
		'manage_options',
		'jasanika-cookie-manager',
		'jasanika_admin_page_cookie_manager'
	);

	// Backup Manager sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Backup Manager', 'jasanika' ),
		__( 'Backup Manager', 'jasanika' ),
		'manage_options',
		'jasanika-backup-manager',
		'jasanika_admin_page_backup_manager'
	);

	// Diagnostics sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Diagnostics', 'jasanika' ),
		__( 'Diagnostics', 'jasanika' ),
		'manage_options',
		'jasanika-diagnostics',
		'jasanika_admin_page_diagnostics'
	);

	// Profile Manager sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Profile Manager', 'jasanika' ),
		__( 'Profile Manager', 'jasanika' ),
		'manage_options',
		'jasanika-profile-manager',
		'jasanika_admin_page_profile_manager'
	);

	// Maintenance Mode sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Maintenance Mode', 'jasanika' ),
		__( 'Maintenance Mode', 'jasanika' ),
		'manage_options',
		'jasanika-maintenance-manager',
		'jasanika_admin_page_maintenance_manager'
	);

	// Theme Presets sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Theme Presets', 'jasanika' ),
		__( 'Theme Presets', 'jasanika' ),
		'manage_options',
		'jasanika-theme-presets',
		'jasanika_admin_page_theme_presets'
	);

	// Update Center sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Update Center', 'jasanika' ),
		__( 'Update Center', 'jasanika' ),
		'manage_options',
		'jasanika-update-center',
		'jasanika_admin_page_update_center'
	);
}
