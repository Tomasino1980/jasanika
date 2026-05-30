<?php

/**
 * Jasanika Admin – Dashboard Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Dashboard admin page.
 */
function jasanika_admin_page_dashboard(): void {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Jasanika – Dashboard', 'jasanika' ); ?></h1>
		<p><?php esc_html_e( 'Central overview of the Jasanika theme administration.', 'jasanika' ); ?></p>
		<p><em><?php esc_html_e( 'Coming in future milestone.', 'jasanika' ); ?></em></p>
	</div>
	<?php
}
