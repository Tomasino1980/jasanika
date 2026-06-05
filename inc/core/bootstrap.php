<?php

/**
 * Jasanika Core Bootstrap
 *
 * Central bootstrap for modular architecture foundation (M58).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Core registry and loader
require_once get_template_directory() . '/inc/core/module-registry.php';
require_once get_template_directory() . '/inc/core/module-loader.php';

// Legacy includes (kept to preserve backward compatibility)
require_once get_template_directory() . '/inc/theme-support.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/menus.php';
require_once get_template_directory() . '/inc/cleanup.php';
require_once get_template_directory() . '/inc/comments.php';
require_once get_template_directory() . '/inc/theme-options.php';
require_once get_template_directory() . '/inc/theme-presets.php';
require_once get_template_directory() . '/inc/woocommerce.php';
require_once get_template_directory() . '/inc/slider.php';
require_once get_template_directory() . '/inc/testimonials.php';
require_once get_template_directory() . '/inc/featured-products.php';
require_once get_template_directory() . '/inc/newsletter.php';
require_once get_template_directory() . '/inc/admin/login-branding.php';
require_once get_template_directory() . '/inc/homepage-builder.php';
require_once get_template_directory() . '/inc/homepage-bg.php';
require_once get_template_directory() . '/inc/seo.php';
require_once get_template_directory() . '/inc/seo-meta-box.php';
require_once get_template_directory() . '/inc/cookie-consent.php';
require_once get_template_directory() . '/inc/backup-manager.php';
require_once get_template_directory() . '/inc/profile-manager.php';
require_once get_template_directory() . '/inc/maintenance.php';

if ( is_admin() ) {
	require_once get_template_directory() . '/inc/admin/admin.php';
}

// ---------------------------------------------------------------------------
// Register initial modules (only registration, no code movement)
// ---------------------------------------------------------------------------

jasanika_register_module( 'dashboard', array(
	'name'        => 'Dashboard',
	'version'     => '0.58.0',
	'description' => 'Admin dashboard integrations and widgets.',
	'enabled'     => true,
) );

jasanika_register_module( 'theme-settings', array(
	'name'        => 'Theme Settings',
	'version'     => '0.58.0',
	'description' => 'Site information and theme configuration.',
	'enabled'     => true,
) );

jasanika_register_module( 'homepage-builder', array(
	'name'        => 'Homepage Builder',
	'version'     => '0.58.0',
	'description' => 'Homepage sections registry and builder.',
	'enabled'     => true,
) );

jasanika_register_module( 'slider-manager', array(
	'name'        => 'Slider Manager',
	'version'     => '0.58.0',
	'description' => 'Slide management and front-end slider.',
	'enabled'     => true,
) );

jasanika_register_module( 'seo-manager', array(
	'name'        => 'SEO Manager',
	'version'     => '0.58.0',
	'description' => 'SEO enhancements and meta box integration.',
	'enabled'     => true,
) );

jasanika_register_module( 'cookie-manager', array(
	'name'        => 'Cookie Manager',
	'version'     => '0.58.0',
	'description' => 'Cookie consent and related UI.',
	'enabled'     => true,
) );

jasanika_register_module( 'newsletter-manager', array(
	'name'        => 'Newsletter Manager',
	'version'     => '0.58.0',
	'description' => 'Newsletter subscriber handling.',
	'enabled'     => true,
) );

jasanika_register_module( 'theme-presets', array(
	'name'        => 'Theme Presets',
	'version'     => '0.58.0',
	'description' => 'Preset color and layout configurations.',
	'enabled'     => true,
) );

jasanika_register_module( 'maintenance-mode', array(
	'name'        => 'Maintenance Mode',
	'version'     => '0.58.0',
	'description' => 'Maintenance mode handling for the site.',
	'enabled'     => true,
) );

// Mark modules as loaded for diagnostics — actual lazy-loading will be implemented later.
jasanika_load_modules();
