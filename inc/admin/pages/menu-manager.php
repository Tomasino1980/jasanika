<?php

/**
 * Jasanika Admin – Menu Manager Page
 *
 * Renders the Menu Manager dashboard integrated with native WordPress Menus.
 * Sections: Overview Cards, Location Status, Menu Items, Quick Actions, Diagnostics.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_enqueue_scripts', 'jasanika_menu_manager_enqueue' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue the Menu Manager stylesheet only on the Menu Manager page.
 *
 * @param string $hook Current admin page hook suffix.
 */
function jasanika_menu_manager_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-menu-manager' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'jasanika-menu-manager',
		get_template_directory_uri() . '/assets/css/admin/menu-manager.css',
		array(),
		'0.44.0'
	);
}

// ---------------------------------------------------------------------------
// Data Helpers
// ---------------------------------------------------------------------------

/**
 * Returns all registered nav menu locations with their assigned menu objects.
 *
 * @return array<string, array{ label: string, menu: WP_Term|false }>
 */
function jasanika_menu_manager_get_locations(): array {
	$registered = get_registered_nav_menus();
	$assigned   = get_nav_menu_locations();
	$locations  = array();

	foreach ( $registered as $slug => $label ) {
		$menu = false;
		if ( ! empty( $assigned[ $slug ] ) ) {
			$menu = wp_get_nav_menu_object( $assigned[ $slug ] );
		}
		$locations[ $slug ] = array(
			'label' => $label,
			'menu'  => $menu,
		);
	}

	return $locations;
}

/**
 * Returns all registered nav menus (WP_Term objects).
 *
 * @return WP_Term[]
 */
function jasanika_menu_manager_get_all_menus(): array {
	$menus = wp_get_nav_menus();
	return is_array( $menus ) ? $menus : array();
}

/**
 * Returns the number of items in a given nav menu.
 *
 * @param int $menu_id Menu term ID.
 * @return int
 */
function jasanika_menu_manager_count_items( int $menu_id ): int {
	$items = wp_get_nav_menu_items( $menu_id );
	return is_array( $items ) ? count( $items ) : 0;
}

// ---------------------------------------------------------------------------
// Page Renderer
// ---------------------------------------------------------------------------

/**
 * Renders the Menu Manager admin page.
 */
function jasanika_admin_page_menu_manager(): void {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}

	$locations   = jasanika_menu_manager_get_locations();
	$all_menus   = jasanika_menu_manager_get_all_menus();
	$assigned    = array_filter( $locations, fn( $loc ) => false !== $loc['menu'] );
	$unassigned  = count( $locations ) - count( $assigned );
	$total_items = 0;

	foreach ( $all_menus as $menu ) {
		$total_items += jasanika_menu_manager_count_items( (int) $menu->term_id );
	}

	$primary_ok = isset( $locations['primary'] ) && false !== $locations['primary']['menu'];
	$footer_ok  = isset( $locations['footer'] ) && false !== $locations['footer']['menu'];

	$nav_menus_url      = esc_url( admin_url( 'nav-menus.php' ) );
	$new_menu_url       = esc_url( admin_url( 'nav-menus.php?action=edit&menu=0' ) );
	$locations_url      = esc_url( admin_url( 'nav-menus.php?action=locations' ) );

	?>
	<div class="wrap jasanika-menu">

		<!-- Header -->
		<div class="jasanika-menu__header">
			<span class="jasanika-menu__header-icon dashicons dashicons-menu"></span>
			<div>
				<h1 class="jasanika-menu__title"><?php esc_html_e( 'Menu Manager', 'jasanika' ); ?></h1>
				<p class="jasanika-menu__subtitle">
					<?php esc_html_e( 'Overview of all registered menu locations and assigned WordPress menus.', 'jasanika' ); ?>
				</p>
			</div>
		</div>

		<!-- Statistics Cards -->
		<div class="jasanika-menu__section">
			<h2 class="jasanika-menu__section-title"><?php esc_html_e( 'Menu Statistics', 'jasanika' ); ?></h2>
			<div class="jasanika-menu__stats-grid">

				<div class="jasanika-menu__stat-card">
					<span class="jasanika-menu__stat-icon dashicons dashicons-menu-alt"></span>
					<div class="jasanika-menu__stat-body">
						<span class="jasanika-menu__stat-value"><?php echo esc_html( (string) count( $all_menus ) ); ?></span>
						<span class="jasanika-menu__stat-label"><?php esc_html_e( 'Total Menus', 'jasanika' ); ?></span>
					</div>
				</div>

				<div class="jasanika-menu__stat-card">
					<span class="jasanika-menu__stat-icon dashicons dashicons-yes-alt"></span>
					<div class="jasanika-menu__stat-body">
						<span class="jasanika-menu__stat-value jasanika-menu__stat-value--ok"><?php echo esc_html( (string) count( $assigned ) ); ?></span>
						<span class="jasanika-menu__stat-label"><?php esc_html_e( 'Assigned Menus', 'jasanika' ); ?></span>
					</div>
				</div>

				<div class="jasanika-menu__stat-card">
					<span class="jasanika-menu__stat-icon dashicons dashicons-warning"></span>
					<div class="jasanika-menu__stat-body">
						<span class="jasanika-menu__stat-value <?php echo $unassigned > 0 ? 'jasanika-menu__stat-value--warn' : 'jasanika-menu__stat-value--ok'; ?>">
							<?php echo esc_html( (string) $unassigned ); ?>
						</span>
						<span class="jasanika-menu__stat-label"><?php esc_html_e( 'Unassigned Locations', 'jasanika' ); ?></span>
					</div>
				</div>

				<div class="jasanika-menu__stat-card">
					<span class="jasanika-menu__stat-icon dashicons dashicons-list-view"></span>
					<div class="jasanika-menu__stat-body">
						<span class="jasanika-menu__stat-value"><?php echo esc_html( (string) $total_items ); ?></span>
						<span class="jasanika-menu__stat-label"><?php esc_html_e( 'Total Menu Items', 'jasanika' ); ?></span>
					</div>
				</div>

			</div>
		</div>

		<!-- Two-column layout: Locations + Quick Actions -->
		<div class="jasanika-menu__two-col">

			<!-- Menu Locations -->
			<div class="jasanika-menu__section">
				<h2 class="jasanika-menu__section-title"><?php esc_html_e( 'Menu Locations', 'jasanika' ); ?></h2>
				<div class="jasanika-menu__card">

					<?php if ( empty( $locations ) ) : ?>
						<p class="jasanika-menu__empty"><?php esc_html_e( 'No menu locations registered.', 'jasanika' ); ?></p>
					<?php else : ?>
						<table class="jasanika-menu__table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Location', 'jasanika' ); ?></th>
									<th><?php esc_html_e( 'Assigned Menu', 'jasanika' ); ?></th>
									<th><?php esc_html_e( 'Status', 'jasanika' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $locations as $slug => $data ) : ?>
									<tr>
										<td class="jasanika-menu__location-name"><?php echo esc_html( $data['label'] ); ?></td>
										<td class="jasanika-menu__assigned-name">
											<?php if ( $data['menu'] instanceof WP_Term ) : ?>
												<?php echo esc_html( $data['menu']->name ); ?>
											<?php else : ?>
												<span class="jasanika-menu__none"><?php esc_html_e( '— None —', 'jasanika' ); ?></span>
											<?php endif; ?>
										</td>
										<td>
											<?php if ( $data['menu'] instanceof WP_Term ) : ?>
												<span class="jasanika-menu__badge jasanika-menu__badge--ok">
													<span class="dashicons dashicons-yes"></span>
													<?php esc_html_e( 'Assigned', 'jasanika' ); ?>
												</span>
											<?php else : ?>
												<span class="jasanika-menu__badge jasanika-menu__badge--warn">
													<span class="dashicons dashicons-warning"></span>
													<?php esc_html_e( 'Not Assigned', 'jasanika' ); ?>
												</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>

				</div>
			</div>

			<!-- Quick Actions -->
			<div class="jasanika-menu__section">
				<h2 class="jasanika-menu__section-title"><?php esc_html_e( 'Quick Actions', 'jasanika' ); ?></h2>
				<div class="jasanika-menu__card jasanika-menu__actions">

					<a href="<?php echo $nav_menus_url; ?>" class="jasanika-menu__action-btn">
						<span class="dashicons dashicons-edit"></span>
						<?php esc_html_e( 'Edit Menus', 'jasanika' ); ?>
					</a>

					<a href="<?php echo $new_menu_url; ?>" class="jasanika-menu__action-btn">
						<span class="dashicons dashicons-plus-alt"></span>
						<?php esc_html_e( 'Create New Menu', 'jasanika' ); ?>
					</a>

					<a href="<?php echo $locations_url; ?>" class="jasanika-menu__action-btn">
						<span class="dashicons dashicons-location"></span>
						<?php esc_html_e( 'Manage Locations', 'jasanika' ); ?>
					</a>

				</div>
			</div>

		</div>

		<!-- Menu Items Overview -->
		<div class="jasanika-menu__section">
			<h2 class="jasanika-menu__section-title"><?php esc_html_e( 'Menu Items Overview', 'jasanika' ); ?></h2>
			<div class="jasanika-menu__card">

				<?php if ( empty( $all_menus ) ) : ?>
					<p class="jasanika-menu__empty"><?php esc_html_e( 'No menus found. Create your first menu in Appearance → Menus.', 'jasanika' ); ?></p>
				<?php else : ?>
					<table class="jasanika-menu__table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Menu Name', 'jasanika' ); ?></th>
								<th><?php esc_html_e( 'Items', 'jasanika' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $all_menus as $menu ) : ?>
								<?php $count = jasanika_menu_manager_count_items( (int) $menu->term_id ); ?>
								<tr>
									<td class="jasanika-menu__location-name"><?php echo esc_html( $menu->name ); ?></td>
									<td>
										<span class="jasanika-menu__item-count">
											<?php
											echo esc_html(
												sprintf(
													/* translators: %d number of menu items */
													_n( '%d item', '%d items', $count, 'jasanika' ),
													$count
												)
											);
											?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>

			</div>
		</div>

		<!-- Diagnostics + Theme Integration -->
		<div class="jasanika-menu__two-col">

			<!-- Diagnostics -->
			<div class="jasanika-menu__section">
				<h2 class="jasanika-menu__section-title"><?php esc_html_e( 'Diagnostics', 'jasanika' ); ?></h2>
				<div class="jasanika-menu__card">

					<div class="jasanika-menu__diag-row <?php echo $primary_ok ? 'jasanika-menu__diag-row--ok' : 'jasanika-menu__diag-row--warn'; ?>">
						<span class="dashicons <?php echo $primary_ok ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
						<span class="jasanika-menu__diag-label">
							<?php
							if ( $primary_ok ) {
								esc_html_e( 'Primary Menu is assigned', 'jasanika' );
							} else {
								esc_html_e( 'No Primary Menu Assigned', 'jasanika' );
							}
							?>
						</span>
					</div>

					<div class="jasanika-menu__diag-row <?php echo $footer_ok ? 'jasanika-menu__diag-row--ok' : 'jasanika-menu__diag-row--warn'; ?>">
						<span class="dashicons <?php echo $footer_ok ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
						<span class="jasanika-menu__diag-label">
							<?php
							if ( $footer_ok ) {
								esc_html_e( 'Footer Menu is assigned', 'jasanika' );
							} else {
								esc_html_e( 'No Footer Menu Assigned', 'jasanika' );
							}
							?>
						</span>
					</div>

				</div>
			</div>

			<!-- Theme Integration Check -->
			<div class="jasanika-menu__section">
				<h2 class="jasanika-menu__section-title"><?php esc_html_e( 'Theme Integration', 'jasanika' ); ?></h2>
				<div class="jasanika-menu__card">

					<?php
					$theme_locations = get_registered_nav_menus();
					$check_slugs     = array(
						'primary' => __( 'Primary Menu registered', 'jasanika' ),
						'footer'  => __( 'Footer Menu registered', 'jasanika' ),
					);
					?>

					<?php foreach ( $check_slugs as $slug => $check_label ) : ?>
						<?php $registered = array_key_exists( $slug, $theme_locations ); ?>
						<div class="jasanika-menu__diag-row <?php echo $registered ? 'jasanika-menu__diag-row--ok' : 'jasanika-menu__diag-row--warn'; ?>">
							<span class="dashicons <?php echo $registered ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
							<span class="jasanika-menu__diag-label"><?php echo esc_html( $check_label ); ?></span>
						</div>
					<?php endforeach; ?>

				</div>
			</div>

		</div>

	</div>
	<?php
}
