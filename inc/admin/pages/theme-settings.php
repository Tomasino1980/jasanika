<?php

/**
 * Jasanika Admin – Theme Settings Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Theme Settings admin page.
 */
function jasanika_admin_page_theme_settings(): void {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Jasanika – Theme Settings', 'jasanika' ); ?></h1>
		<p><?php esc_html_e( 'Global configuration settings for the Jasanika theme.', 'jasanika' ); ?></p>
		<p><em><?php esc_html_e( 'Coming in future milestone.', 'jasanika' ); ?></em></p>
	</div>
	<?php
}
