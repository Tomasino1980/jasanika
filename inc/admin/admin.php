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
require_once get_template_directory() . '/inc/admin/pages/testimonials-manager.php';
require_once get_template_directory() . '/inc/admin/pages/newsletter-manager.php';
require_once get_template_directory() . '/inc/admin/pages/theme-settings.php';
require_once get_template_directory() . '/inc/admin/pages/seo-manager.php';
require_once get_template_directory() . '/inc/admin/pages/cookie-manager.php';

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

	// Theme Settings sub-page.
	add_submenu_page(
		'jasanika',
		__( 'Theme Settings', 'jasanika' ),
		__( 'Theme Settings', 'jasanika' ),
		'manage_options',
		'jasanika-theme-settings',
		'jasanika_admin_page_theme_settings'
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
}
