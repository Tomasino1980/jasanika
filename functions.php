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
require_once get_template_directory() . '/inc/woocommerce.php';
require_once get_template_directory() . '/inc/slider.php';
require_once get_template_directory() . '/inc/admin/login-branding.php';

if ( is_admin() ) {
	require_once get_template_directory() . '/inc/admin/admin.php';
}