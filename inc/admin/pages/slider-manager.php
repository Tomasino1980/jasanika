<?php

/**
 * Jasanika Admin – Slider Manager Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Slider Manager admin page.
 */
function jasanika_admin_page_slider_manager(): void {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Jasanika – Slider Manager', 'jasanika' ); ?></h1>
		<p><?php esc_html_e( 'Manage homepage slider slides for the Jasanika theme.', 'jasanika' ); ?></p>
		<p><em><?php esc_html_e( 'Coming in future milestone.', 'jasanika' ); ?></em></p>
	</div>
	<?php
}
