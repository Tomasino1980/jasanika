<?php

/**
 * Jasanika Theme
 *
 * Core theme bootstrap file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
require_once get_template_directory() . '/inc/seo.php';
require_once get_template_directory() . '/inc/seo-meta-box.php';
require_once get_template_directory() . '/inc/cookie-consent.php';
require_once get_template_directory() . '/inc/backup-manager.php';
require_once get_template_directory() . '/inc/profile-manager.php';
require_once get_template_directory() . '/inc/maintenance.php';

if ( is_admin() ) {
	require_once get_template_directory() . '/inc/admin/admin.php';
}
