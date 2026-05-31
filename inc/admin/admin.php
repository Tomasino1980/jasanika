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
require_once get_template_directory() . '/inc/admin/pages/menu-manager.php';
require_once get_template_directory() . '/inc/admin/pages/slider-manager.php';
require_once get_template_directory() . '/inc/admin/pages/theme-settings.php';

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

	// Theme Settings sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Theme Settings', 'jasanika' ),
		__( 'Theme Settings', 'jasanika' ),
		'manage_options',
		'jasanika-theme-settings',
		'jasanika_admin_page_theme_settings'
	);
}
