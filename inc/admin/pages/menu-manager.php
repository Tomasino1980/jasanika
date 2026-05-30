<?php

/**
 * Jasanika Admin – Menu Manager Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Menu Manager admin page.
 */
function jasanika_admin_page_menu_manager(): void {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Jasanika – Menu Manager', 'jasanika' ); ?></h1>
		<p><?php esc_html_e( 'Manage the site navigation menus for the Jasanika theme.', 'jasanika' ); ?></p>
		<p><em><?php esc_html_e( 'Coming in future milestone.', 'jasanika' ); ?></em></p>
	</div>
	<?php
}
