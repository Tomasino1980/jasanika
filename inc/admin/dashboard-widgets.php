<?php

/**
 * Jasanika Admin – Dashboard Widgets
 *
 * Reusable rendering functions for each section of the Jasanika Dashboard page.
 * Each function outputs a self-contained dashboard section.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Statistics Cards
// ---------------------------------------------------------------------------

/**
 * Render the statistics overview row.
 *
 * Displays total counts for Posts, Pages, Products and Orders.
 */
function jasanika_dashboard_widget_stats(): void {

	$post_counts    = wp_count_posts( 'post' );
	$page_counts    = wp_count_posts( 'page' );
	$total_posts    = isset( $post_counts->publish ) ? (int) $post_counts->publish : 0;
	$total_pages    = isset( $page_counts->publish ) ? (int) $page_counts->publish : 0;

	$total_products = 0;
	$total_orders   = 0;

	if ( jasanika_is_woocommerce_active() ) {
		$product_counts  = wp_count_posts( 'product' );
		$total_products  = isset( $product_counts->publish ) ? (int) $product_counts->publish : 0;
		$total_orders    = jasanika_dashboard_count_orders();
	}

	$stats = array(
		array(
			'icon'  => 'dashicons-admin-post',
			'value' => $total_posts,
			'label' => __( 'Posts', 'jasanika' ),
		),
		array(
			'icon'  => 'dashicons-admin-page',
			'value' => $total_pages,
			'label' => __( 'Pages', 'jasanika' ),
		),
		array(
			'icon'  => 'dashicons-cart',
			'value' => $total_products,
			'label' => __( 'Products', 'jasanika' ),
		),
		array(
			'icon'  => 'dashicons-clipboard',
			'value' => $total_orders,
			'label' => __( 'Orders', 'jasanika' ),
		),
	);

	echo '<div class="jasanika-stats-grid">';

	foreach ( $stats as $stat ) {
		echo '<div class="jasanika-stat-card">';
		echo '<span class="jasanika-stat-card__icon dashicons ' . esc_attr( $stat['icon'] ) . '"></span>';
		echo '<div class="jasanika-stat-card__body">';
		echo '<div class="jasanika-stat-card__number">' . esc_html( number_format_i18n( $stat['value'] ) ) . '</div>';
		echo '<div class="jasanika-stat-card__label">' . esc_html( $stat['label'] ) . '</div>';
		echo '</div>';
		echo '</div>';
	}

	echo '</div>';
}

// ---------------------------------------------------------------------------
// Recent Posts
// ---------------------------------------------------------------------------

/**
 * Render the Recent Posts activity panel.
 *
 * Displays up to 5 most recently published posts with title and date.
 */
function jasanika_dashboard_widget_recent_posts(): void {

	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 5,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	echo '<div class="jasanika-panel">';
	echo '<div class="jasanika-panel__header">';
	echo '<span class="jasanika-panel__icon dashicons dashicons-admin-post"></span>';
	echo '<h3 class="jasanika-panel__title">' . esc_html__( 'Recent Posts', 'jasanika' ) . '</h3>';
	echo '</div>';
	echo '<div class="jasanika-panel__body">';

	if ( $query->have_posts() ) {
		echo '<ul class="jasanika-activity-list">';

		while ( $query->have_posts() ) {
			$query->the_post();
			$edit_url = get_edit_post_link( get_the_ID() );
			echo '<li class="jasanika-activity-list__item">';
			echo '<span class="jasanika-activity-list__primary">';
			echo '<a href="' . esc_url( (string) $edit_url ) . '">' . esc_html( get_the_title() ) . '</a>';
			echo '</span>';
			echo '<span class="jasanika-activity-list__meta">' . esc_html( get_the_date() ) . '</span>';
			echo '</li>';
		}

		echo '</ul>';
	} else {
		echo '<p class="jasanika-activity-list__empty">' . esc_html__( 'No posts found.', 'jasanika' ) . '</p>';
	}

	wp_reset_postdata();

	echo '</div>';
	echo '</div>';
}

// ---------------------------------------------------------------------------
// Recent Orders
// ---------------------------------------------------------------------------

/**
 * Render the Recent Orders activity panel.
 *
 * Displays up to 5 most recent WooCommerce orders with number, date, status and total.
 * Gracefully degrades when WooCommerce is inactive.
 */
function jasanika_dashboard_widget_recent_orders(): void {

	echo '<div class="jasanika-panel">';
	echo '<div class="jasanika-panel__header">';
	echo '<span class="jasanika-panel__icon dashicons dashicons-clipboard"></span>';
	echo '<h3 class="jasanika-panel__title">' . esc_html__( 'Recent Orders', 'jasanika' ) . '</h3>';
	echo '</div>';
	echo '<div class="jasanika-panel__body">';

	if ( ! jasanika_is_woocommerce_active() ) {
		echo '<p class="jasanika-activity-list__empty">' . esc_html__( 'WooCommerce is not active.', 'jasanika' ) . '</p>';
		echo '</div></div>';
		return;
	}

	$orders = wc_get_orders(
		array(
			'limit'   => 5,
			'orderby' => 'date',
			'order'   => 'DESC',
			'status'  => array_keys( wc_get_order_statuses() ),
		)
	);

	if ( ! empty( $orders ) ) {
		echo '<ul class="jasanika-activity-list">';

		foreach ( $orders as $order ) {
			if ( ! $order instanceof WC_Order ) {
				continue;
			}

			$order_id     = $order->get_id();
			$order_number = $order->get_order_number();
			$edit_url     = get_edit_post_link( $order_id );
			$status       = $order->get_status();
			$status_label = wc_get_order_status_name( $status );
			$total        = $order->get_formatted_order_total();
			$date         = $order->get_date_created();
			$date_str     = $date ? $date->date_i18n( get_option( 'date_format' ) ) : '—';

			echo '<li class="jasanika-activity-list__item">';

			echo '<span class="jasanika-activity-list__primary">';
			echo '<a href="' . esc_url( (string) $edit_url ) . '">#' . esc_html( (string) $order_number ) . '</a>';
			echo '</span>';

			echo '<span class="jasanika-activity-list__meta" style="display:flex;align-items:center;gap:8px;">';
			echo '<span class="jasanika-order-status jasanika-order-status--' . esc_attr( $status ) . '">' . esc_html( $status_label ) . '</span>';
			echo '<span>' . wp_kses( $total, array( 'span' => array( 'class' => array() ), 'bdi' => array() ) ) . '</span>';
			echo '<span>' . esc_html( $date_str ) . '</span>';
			echo '</span>';

			echo '</li>';
		}

		echo '</ul>';
	} else {
		echo '<p class="jasanika-activity-list__empty">' . esc_html__( 'No orders found.', 'jasanika' ) . '</p>';
	}

	echo '</div>';
	echo '</div>';
}

// ---------------------------------------------------------------------------
// System Status
// ---------------------------------------------------------------------------

/**
 * Render the System Status panel.
 *
 * Displays WordPress version, WooCommerce version and active theme version.
 */
function jasanika_dashboard_widget_system_status(): void {

	global $wp_version;

	$theme_version = wp_get_theme()->get( 'Version' );

	$wc_version = defined( 'WC_VERSION' ) ? WC_VERSION : __( 'Not installed', 'jasanika' );

	$rows = array(
		array(
			'label' => __( 'WordPress Version', 'jasanika' ),
			'value' => $wp_version,
		),
		array(
			'label' => __( 'WooCommerce Version', 'jasanika' ),
			'value' => $wc_version,
		),
		array(
			'label' => __( 'Active Theme Version', 'jasanika' ),
			'value' => $theme_version,
		),
	);

	echo '<div class="jasanika-panel">';
	echo '<div class="jasanika-panel__header">';
	echo '<span class="jasanika-panel__icon dashicons dashicons-info-outline"></span>';
	echo '<h3 class="jasanika-panel__title">' . esc_html__( 'System Status', 'jasanika' ) . '</h3>';
	echo '</div>';
	echo '<div class="jasanika-panel__body">';
	echo '<table class="jasanika-status-table">';

	foreach ( $rows as $row ) {
		echo '<tr>';
		echo '<td class="jasanika-status-table__label">' . esc_html( $row['label'] ) . '</td>';
		echo '<td class="jasanika-status-table__value">' . esc_html( (string) $row['value'] ) . '</td>';
		echo '</tr>';
	}

	echo '</table>';
	echo '</div>';
	echo '</div>';
}

// ---------------------------------------------------------------------------
// Configuration Status
// ---------------------------------------------------------------------------

/**
 * Render the Jasanika Configuration Status section.
 *
 * Checks whether key theme areas have been configured and displays
 * a ✓ Configured or ⚠ Needs Attention indicator for each.
 */
function jasanika_dashboard_widget_config_status(): void {

	$settings = get_option( 'jasanika_settings', array() );

	$checks = array(
		array(
			'label' => __( 'Theme Settings', 'jasanika' ),
			'ok'    => ! empty( $settings ),
		),
		array(
			'label' => __( 'Logo', 'jasanika' ),
			'ok'    => '' !== jasanika_get_logo_url(),
		),
		array(
			'label' => __( 'Slider', 'jasanika' ),
			'ok'    => ! empty( jasanika_get_slides() ),
		),
		array(
			'label' => __( 'Footer', 'jasanika' ),
			'ok'    => jasanika_dashboard_is_footer_configured( $settings ),
		),
		array(
			'label' => __( 'Menu', 'jasanika' ),
			'ok'    => has_nav_menu( 'primary' ),
		),
	);

	echo '<div class="jasanika-config-grid">';

	foreach ( $checks as $check ) {
		$modifier = $check['ok'] ? 'ok' : 'warn';
		$icon     = $check['ok'] ? '✓' : '⚠';
		$note     = $check['ok'] ? __( 'Configured', 'jasanika' ) : __( 'Needs Attention', 'jasanika' );

		echo '<div class="jasanika-config-card jasanika-config-card--' . esc_attr( $modifier ) . '">';
		echo '<div class="jasanika-config-card__status">' . esc_html( $icon ) . '</div>';
		echo '<span class="jasanika-config-card__label">' . esc_html( $check['label'] ) . '</span>';
		echo '<span class="jasanika-config-card__note">' . esc_html( $note ) . '</span>';
		echo '</div>';
	}

	echo '</div>';
}

// ---------------------------------------------------------------------------
// Quick Actions
// ---------------------------------------------------------------------------

/**
 * Render the Quick Actions row.
 *
 * Displays shortcut buttons to the most common admin operations.
 */
function jasanika_dashboard_widget_quick_actions(): void {

	$actions = array(
		array(
			'label'   => __( 'Add New Post', 'jasanika' ),
			'url'     => admin_url( 'post-new.php' ),
			'icon'    => 'dashicons-plus-alt',
			'variant' => 'primary',
		),
		array(
			'label'   => __( 'Add New Product', 'jasanika' ),
			'url'     => admin_url( 'post-new.php?post_type=product' ),
			'icon'    => 'dashicons-plus-alt',
			'variant' => 'primary',
		),
		array(
			'label'   => __( 'Slider Manager', 'jasanika' ),
			'url'     => admin_url( 'admin.php?page=jasanika-slider-manager' ),
			'icon'    => 'dashicons-images-alt2',
			'variant' => 'secondary',
		),
		array(
			'label'   => __( 'Theme Settings', 'jasanika' ),
			'url'     => admin_url( 'admin.php?page=jasanika-theme-settings' ),
			'icon'    => 'dashicons-admin-settings',
			'variant' => 'secondary',
		),
		array(
			'label'   => __( 'Menu Manager', 'jasanika' ),
			'url'     => admin_url( 'admin.php?page=jasanika-menu-manager' ),
			'icon'    => 'dashicons-menu',
			'variant' => 'secondary',
		),
	);

	echo '<div class="jasanika-quick-actions">';

	foreach ( $actions as $action ) {
		echo '<a href="' . esc_url( $action['url'] ) . '" class="jasanika-btn jasanika-btn--' . esc_attr( $action['variant'] ) . '">';
		echo '<span class="dashicons ' . esc_attr( $action['icon'] ) . '"></span>';
		echo esc_html( $action['label'] );
		echo '</a>';
	}

	echo '</div>';
}

// ---------------------------------------------------------------------------
// Private helpers
// ---------------------------------------------------------------------------

/**
 * Check whether WooCommerce is active.
 *
 * @return bool
 */
function jasanika_is_woocommerce_active(): bool {
	return class_exists( 'WooCommerce' );
}

/**
 * Count all WooCommerce orders.
 *
 * @return int
 */
function jasanika_dashboard_count_orders(): int {
	$counts = (array) wp_count_posts( 'shop_order' );
	$total  = 0;

	foreach ( $counts as $status => $count ) {
		// Exclude auto-draft and inherit pseudo-statuses.
		if ( in_array( $status, array( 'auto-draft', 'inherit', 'trash' ), true ) ) {
			continue;
		}
		$total += (int) $count;
	}

	return $total;
}

/**
 * Determine whether the footer section has been configured.
 *
 * Checks for a non-empty footer_note or a saved copyright_text in jasanika_settings.
 *
 * @param array $settings The jasanika_settings option array.
 * @return bool
 */
function jasanika_dashboard_is_footer_configured( array $settings ): bool {
	if ( ! empty( $settings['footer_note'] ) ) {
		return true;
	}

	if ( ! empty( $settings['copyright_text'] ) ) {
		return true;
	}

	// Check footer builder columns.
	$footer_builder = get_option( 'jasanika_footer_builder', array() );
	return ! empty( $footer_builder );
}
