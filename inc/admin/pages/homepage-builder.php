<?php

/**
 * Jasanika Admin – Homepage Builder Page
 *
 * Centralises all homepage-related configuration:
 *  – Section Builder  (enable / order / background per section)
 *  – Hero Content     (heading, description, CTA button)
 *  – Feature Blocks   (three feature cards)
 *  – CTA Section
 *  – Categories / Latest Posts
 *  – Featured Products
 *  – Newsletter display settings
 *
 * Settings are stored inside the shared jasanika_settings option.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'jasanika_homepage_builder_admin_init' );
add_action( 'admin_enqueue_scripts', 'jasanika_homepage_builder_admin_enqueue' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue Homepage Builder assets only on the Homepage Builder admin page.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_homepage_builder_admin_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-homepage-builder' !== $hook ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'jasanika-homepage-builder-admin',
		get_template_directory_uri() . '/assets/css/admin/homepage-builder-admin.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'jasanika-homepage-builder-bg',
		get_template_directory_uri() . '/assets/css/admin/homepage-builder-bg.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'jasanika-homepage-builder-admin',
		get_template_directory_uri() . '/assets/js/admin/homepage-builder-admin.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

	wp_enqueue_script(
		'jasanika-homepage-builder-bg',
		get_template_directory_uri() . '/assets/js/admin/homepage-builder-bg.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

	wp_enqueue_script(
		'jasanika-media-uploader',
		get_template_directory_uri() . '/assets/js/admin/media-uploader.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);
}

// ---------------------------------------------------------------------------
// Settings Registration
// ---------------------------------------------------------------------------

/**
 * Register Homepage Builder fields under jasanika_homepage_builder_group.
 */
function jasanika_homepage_builder_admin_init(): void {
	register_setting(
		'jasanika_homepage_builder_group',
		'jasanika_settings',
		array( 'sanitize_callback' => 'jasanika_sanitize_homepage_builder_settings' )
	);

	// Content fields are rendered inline in the page.
	// Section builder (enable/order/bg) fields are also rendered inline.
}

// ---------------------------------------------------------------------------
// Sanitize
// ---------------------------------------------------------------------------

/**
 * Sanitize Homepage Builder settings input.
 * Merges updated values into the full jasanika_settings option.
 *
 * @param mixed $input Raw POST input.
 * @return array Full merged settings array.
 */
function jasanika_sanitize_homepage_builder_settings( mixed $input ): array {
	if ( ! is_array( $input ) ) {
		$input = array();
	}

	$existing = get_option( 'jasanika_settings', array() );

	// Hero content.
	$existing['hero_heading']     = sanitize_text_field( $input['hero_heading']     ?? '' );
	$existing['hero_description'] = sanitize_textarea_field( $input['hero_description'] ?? '' );
	$existing['hero_button_text'] = sanitize_text_field( $input['hero_button_text'] ?? '' );
	$existing['hero_button_url']  = esc_url_raw( $input['hero_button_url']          ?? '' );

	// Feature Blocks 1–3.
	for ( $i = 1; $i <= 3; $i++ ) {
		$existing[ "feature_{$i}_title" ]       = sanitize_text_field( $input[ "feature_{$i}_title" ]       ?? '' );
		$existing[ "feature_{$i}_description" ] = sanitize_textarea_field( $input[ "feature_{$i}_description" ] ?? '' );
		$existing[ "feature_{$i}_button_text" ] = sanitize_text_field( $input[ "feature_{$i}_button_text" ] ?? '' );
		$existing[ "feature_{$i}_button_url" ]  = esc_url_raw( $input[ "feature_{$i}_button_url" ]          ?? '' );
	}

	// CTA Section.
	$existing['cta_title']       = sanitize_text_field( $input['cta_title']       ?? '' );
	$existing['cta_description'] = sanitize_textarea_field( $input['cta_description'] ?? '' );
	$existing['cta_button_text'] = sanitize_text_field( $input['cta_button_text'] ?? '' );
	$existing['cta_button_url']  = esc_url_raw( $input['cta_button_url']          ?? '' );

	// Categories section.
	$existing['categories_section_title'] = sanitize_text_field( $input['categories_section_title'] ?? '' );

	// Latest Posts section.
	$existing['latest_posts_section_title'] = sanitize_text_field( $input['latest_posts_section_title'] ?? '' );
	$latest_posts_count                     = absint( $input['latest_posts_count'] ?? 3 );
	$existing['latest_posts_count']         = min( 12, max( 1, $latest_posts_count ) );

	// Featured Products.
	$existing['featured_products_title']       = sanitize_text_field( $input['featured_products_title']       ?? '' );
	$existing['featured_products_description'] = sanitize_textarea_field( $input['featured_products_description'] ?? '' );
	$featured_count                            = absint( $input['featured_products_count'] ?? 4 );
	$existing['featured_products_count']       = min( 12, max( 1, $featured_count ) );

	// Newsletter display settings.
	$existing['newsletter_title']        = sanitize_text_field( $input['newsletter_title']        ?? '' );
	$existing['newsletter_description']  = sanitize_textarea_field( $input['newsletter_description']  ?? '' );
	$existing['newsletter_success']      = sanitize_text_field( $input['newsletter_success']      ?? '' );
	$existing['newsletter_privacy_text'] = sanitize_text_field( $input['newsletter_privacy_text'] ?? '' );

	// Section Builder – enabled / order.
	foreach ( array_keys( jasanika_homepage_sections_registry() ) as $key ) {
		$enabled_key = 'hb_' . $key . '_enabled';
		$order_key   = 'hb_' . $key . '_order';

		$existing[ $enabled_key ] = isset( $input[ $enabled_key ] ) ? 1 : 0;

		$order = absint( $input[ $order_key ] ?? 0 );
		$existing[ $order_key ] = max( 1, min( 99, $order ) );
	}

	// Section Builder – background settings.
	$valid_bg_types     = array( 'none', 'color', 'image', 'color_image' );
	$valid_bg_fits      = array( 'cover', 'contain', 'stretch', 'original', 'repeat' );
	$valid_bg_positions = array( 'center', 'top', 'bottom', 'left', 'right', 'top_left', 'top_right', 'bottom_left', 'bottom_right' );

	foreach ( array_keys( jasanika_homepage_sections_registry() ) as $key ) {
		$prefix = 'hb_' . $key . '_bg_';

		$bg_type = sanitize_key( $input[ $prefix . 'type' ] ?? 'color' );
		$existing[ $prefix . 'type' ] = in_array( $bg_type, $valid_bg_types, true ) ? $bg_type : 'color';

		$image_id = absint( $input[ $prefix . 'image_id' ] ?? 0 );
		if ( $image_id > 0 && ! wp_attachment_is_image( $image_id ) ) {
			$image_id = 0;
		}
		$existing[ $prefix . 'image_id' ] = $image_id;

		$bg_fit = sanitize_key( $input[ $prefix . 'fit' ] ?? 'cover' );
		$existing[ $prefix . 'fit' ] = in_array( $bg_fit, $valid_bg_fits, true ) ? $bg_fit : 'cover';

		$bg_pos = sanitize_key( $input[ $prefix . 'position' ] ?? 'center' );
		$existing[ $prefix . 'position' ] = in_array( $bg_pos, $valid_bg_positions, true ) ? $bg_pos : 'center';

		$existing[ $prefix . 'repeat' ] = isset( $input[ $prefix . 'repeat' ] ) ? 1 : 0;

		$overlay_color = jasanika_sanitize_hex_color( $input[ $prefix . 'overlay_color' ] ?? '' );
		$existing[ $prefix . 'overlay_color' ] = '' !== $overlay_color ? $overlay_color : '#000000';

		$opacity = absint( $input[ $prefix . 'overlay_opacity' ] ?? 50 );
		$existing[ $prefix . 'overlay_opacity' ] = min( 100, max( 0, $opacity ) );
	}

	return $existing;
}

// ---------------------------------------------------------------------------
// Page Render
// ---------------------------------------------------------------------------

/**
 * Render the Homepage Builder admin page.
 */
function jasanika_admin_page_homepage_builder(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}

	$settings = get_option( 'jasanika_settings', array() );
	?>
	<div class="wrap jasanika-hb-admin-wrap">

		<div class="jasanika-admin-header">
			<span class="dashicons dashicons-layout"></span>
			<div>
				<h1><?php esc_html_e( 'Homepage Builder', 'jasanika' ); ?></h1>
				<p><?php esc_html_e( 'Manage homepage sections: enable/disable, reorder and customise content.', 'jasanika' ); ?></p>
			</div>
		</div>

		<!-- Tab navigation -->
		<div class="jasanika-hb-tabs">
			<button type="button" class="jasanika-hb-tab-btn active" data-tab="builder"><?php esc_html_e( 'Section Builder', 'jasanika' ); ?></button>
			<button type="button" class="jasanika-hb-tab-btn" data-tab="hero"><?php esc_html_e( 'Hero Content', 'jasanika' ); ?></button>
			<button type="button" class="jasanika-hb-tab-btn" data-tab="blocks"><?php esc_html_e( 'Feature Blocks', 'jasanika' ); ?></button>
			<button type="button" class="jasanika-hb-tab-btn" data-tab="cta"><?php esc_html_e( 'CTA Section', 'jasanika' ); ?></button>
			<button type="button" class="jasanika-hb-tab-btn" data-tab="other"><?php esc_html_e( 'Other Sections', 'jasanika' ); ?></button>
		</div>

		<form method="post" action="options.php">
			<?php settings_fields( 'jasanika_homepage_builder_group' ); ?>

			<!-- Panel: Section Builder -->
			<div id="jshb-panel-builder" class="jasanika-hb-panel active">
				<?php jasanika_hb_admin_render_section_builder( $settings ); ?>
			</div>

			<!-- Panel: Hero Content -->
			<div id="jshb-panel-hero" class="jasanika-hb-panel">
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Hero Heading', 'jasanika' ); ?></th>
						<td>
							<?php jasanika_settings_field_text( array( 'key' => 'hero_heading', 'placeholder' => __( 'Vítejte na Jasanika', 'jasanika' ) ) ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Hero Description', 'jasanika' ); ?></th>
						<td>
							<?php jasanika_settings_field_textarea( array( 'key' => 'hero_description', 'placeholder' => __( 'Ručně tvořený WordPress obchod a blog.', 'jasanika' ) ) ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Hero Button Text', 'jasanika' ); ?></th>
						<td>
							<?php jasanika_settings_field_text( array( 'key' => 'hero_button_text', 'placeholder' => __( 'Zjistit více', 'jasanika' ) ) ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Hero Button URL', 'jasanika' ); ?></th>
						<td>
							<?php jasanika_settings_field_url( array( 'key' => 'hero_button_url' ) ); ?>
						</td>
					</tr>
				</table>
			</div>

			<!-- Panel: Feature Blocks -->
			<div id="jshb-panel-blocks" class="jasanika-hb-panel">
				<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<h3><?php printf( esc_html__( 'Feature Block %d', 'jasanika' ), $i ); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Title', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_text( array( 'key' => "feature_{$i}_title" ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Description', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_textarea( array( 'key' => "feature_{$i}_description" ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Button Text', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_text( array( 'key' => "feature_{$i}_button_text" ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Button URL', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_url( array( 'key' => "feature_{$i}_button_url" ) ); ?></td>
					</tr>
				</table>
				<?php endfor; ?>
			</div>

			<!-- Panel: CTA Section -->
			<div id="jshb-panel-cta" class="jasanika-hb-panel">
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'CTA Heading', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_text( array( 'key' => 'cta_title', 'placeholder' => __( 'Máte vlastní nápad?', 'jasanika' ) ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'CTA Description', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_textarea( array( 'key' => 'cta_description' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'CTA Button Text', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_text( array( 'key' => 'cta_button_text', 'placeholder' => __( 'Kontaktujte nás', 'jasanika' ) ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'CTA Button URL', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_url( array( 'key' => 'cta_button_url' ) ); ?></td>
					</tr>
				</table>
			</div>

			<!-- Panel: Other Sections -->
			<div id="jshb-panel-other" class="jasanika-hb-panel">
				<h3><?php esc_html_e( 'Categories Section', 'jasanika' ); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Section Title', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_text( array( 'key' => 'categories_section_title', 'placeholder' => __( 'Procházet kategorie', 'jasanika' ) ) ); ?></td>
					</tr>
				</table>

				<h3><?php esc_html_e( 'Latest Posts Section', 'jasanika' ); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Section Title', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_text( array( 'key' => 'latest_posts_section_title', 'placeholder' => __( 'Nejnovější články', 'jasanika' ) ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Number of Posts', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_number( array( 'key' => 'latest_posts_count', 'min' => 1, 'max' => 12 ) ); ?></td>
					</tr>
				</table>

				<h3><?php esc_html_e( 'Featured Products Section', 'jasanika' ); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Section Title', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_text( array( 'key' => 'featured_products_title', 'placeholder' => __( 'Featured Products', 'jasanika' ) ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Section Description', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_textarea( array( 'key' => 'featured_products_description' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Number of Products', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_number( array( 'key' => 'featured_products_count', 'min' => 1, 'max' => 12 ) ); ?></td>
					</tr>
				</table>

				<h3><?php esc_html_e( 'Newsletter Section', 'jasanika' ); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Section Title', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_text( array( 'key' => 'newsletter_title', 'placeholder' => __( 'Newsletter', 'jasanika' ) ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Section Description', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_textarea( array( 'key' => 'newsletter_description' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Success Message', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_text( array( 'key' => 'newsletter_success', 'placeholder' => __( 'Thank you for subscribing.', 'jasanika' ) ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Privacy Checkbox Text', 'jasanika' ); ?></th>
						<td><?php jasanika_settings_field_text( array( 'key' => 'newsletter_privacy_text', 'placeholder' => __( 'I agree to the Privacy Policy.', 'jasanika' ) ) ); ?></td>
					</tr>
				</table>
			</div>

			<?php submit_button( __( 'Save Homepage Builder', 'jasanika' ) ); ?>

		</form>

	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Section Builder UI
// ---------------------------------------------------------------------------

/**
 * Render the Homepage Section Builder table.
 *
 * Reuses the same table markup originally located in theme-settings.php.
 *
 * @param array $settings Current jasanika_settings option array.
 */
function jasanika_hb_admin_render_section_builder( array $settings ): void {
	$registry = jasanika_homepage_sections_registry();

	$bg_type_labels = array(
		'none'        => __( 'None', 'jasanika' ),
		'color'       => __( 'Color', 'jasanika' ),
		'image'       => __( 'Image', 'jasanika' ),
		'color_image' => __( 'Color + Image Overlay', 'jasanika' ),
	);

	$fit_options = array(
		'cover'    => __( 'Cover', 'jasanika' ),
		'contain'  => __( 'Contain', 'jasanika' ),
		'stretch'  => __( 'Stretch', 'jasanika' ),
		'original' => __( 'Original Size', 'jasanika' ),
		'repeat'   => __( 'Repeat', 'jasanika' ),
	);

	$position_options = array(
		'center'       => __( 'Center', 'jasanika' ),
		'top'          => __( 'Top', 'jasanika' ),
		'bottom'       => __( 'Bottom', 'jasanika' ),
		'left'         => __( 'Left', 'jasanika' ),
		'right'        => __( 'Right', 'jasanika' ),
		'top_left'     => __( 'Top Left', 'jasanika' ),
		'top_right'    => __( 'Top Right', 'jasanika' ),
		'bottom_left'  => __( 'Bottom Left', 'jasanika' ),
		'bottom_right' => __( 'Bottom Right', 'jasanika' ),
	);
	?>
	<p class="description">
		<?php esc_html_e( 'Enable or disable each homepage section, set its display order and configure its background.', 'jasanika' ); ?>
	</p>
	<table class="widefat striped jasanika-homepage-builder-table" style="margin-top:12px;">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Section Name', 'jasanika' ); ?></th>
				<th><?php esc_html_e( 'Enabled', 'jasanika' ); ?></th>
				<th><?php esc_html_e( 'Sort Order', 'jasanika' ); ?></th>
				<th><?php esc_html_e( 'Background', 'jasanika' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $registry as $key => $section ) :
				$enabled_key = 'hb_' . $key . '_enabled';
				$order_key   = 'hb_' . $key . '_order';
				$prefix      = 'hb_' . $key . '_bg_';

				$enabled = isset( $settings[ $enabled_key ] )
					? (bool) $settings[ $enabled_key ]
					: $section['default_enabled'];

				$order = ( isset( $settings[ $order_key ] ) && '' !== $settings[ $order_key ] )
					? (int) $settings[ $order_key ]
					: $section['default_order'];

				$bg_type            = $settings[ $prefix . 'type' ]            ?? 'color';
				$bg_image_id        = absint( $settings[ $prefix . 'image_id' ] ?? 0 );
				$bg_fit             = $settings[ $prefix . 'fit' ]             ?? 'cover';
				$bg_position        = $settings[ $prefix . 'position' ]        ?? 'center';
				$bg_repeat          = ! empty( $settings[ $prefix . 'repeat' ] );
				$bg_overlay_color   = $settings[ $prefix . 'overlay_color' ]   ?? '#000000';
				$bg_overlay_opacity = min( 100, max( 0, absint( $settings[ $prefix . 'overlay_opacity' ] ?? 50 ) ) );

				$bg_preview_url = ( $bg_image_id > 0 && wp_attachment_is_image( $bg_image_id ) )
					? wp_get_attachment_image_url( $bg_image_id, 'thumbnail' )
					: '';

				$id_field_id        = 'jbg_' . $key . '_image_id';
				$preview_id         = 'jbg_' . $key . '_preview';
				$remove_btn_id      = 'jbg_' . $key . '_remove_btn';
				$overlay_text_id    = 'jbg_' . $key . '_overlay_color';
				$opacity_display_id = 'jbg_' . $key . '_opacity_val';

				$details_open = ( 'image' === $bg_type || 'color_image' === $bg_type ) ? ' open' : '';

				$badge_class = in_array( $bg_type, array( 'image', 'color_image' ), true )
					? 'jbg-type-badge jbg-type-badge--' . $bg_type
					: 'jbg-type-badge';
			?>
			<tr>
				<td><strong><?php echo esc_html( $section['label'] ); ?></strong></td>
				<td>
					<label>
						<input
							type="checkbox"
							name="jasanika_settings[<?php echo esc_attr( $enabled_key ); ?>]"
							value="1"
							<?php checked( $enabled ); ?>
						>
						<?php esc_html_e( 'Yes', 'jasanika' ); ?>
					</label>
				</td>
				<td>
					<input
						type="number"
						name="jasanika_settings[<?php echo esc_attr( $order_key ); ?>]"
						value="<?php echo esc_attr( (string) $order ); ?>"
						min="1"
						max="99"
						class="small-text"
					>
				</td>
				<td>
					<details class="jasanika-bg-settings"<?php echo $details_open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe constant string. ?>>
						<summary>
							<?php esc_html_e( 'Background Settings', 'jasanika' ); ?>
							<span class="<?php echo esc_attr( $badge_class ); ?>">
								<?php echo esc_html( $bg_type_labels[ $bg_type ] ?? $bg_type ); ?>
							</span>
						</summary>

						<div class="jasanika-bg-fields">

							<div class="jbg-field">
								<span class="jbg-field-label"><?php esc_html_e( 'Background Type', 'jasanika' ); ?></span>
								<div class="jbg-radio-group">
									<?php foreach ( $bg_type_labels as $type_val => $type_label ) : ?>
										<label>
											<input
												type="radio"
												class="jbg-type-radio"
												name="jasanika_settings[<?php echo esc_attr( $prefix . 'type' ); ?>]"
												value="<?php echo esc_attr( $type_val ); ?>"
												<?php checked( $bg_type, $type_val ); ?>
											>
											<?php echo esc_html( $type_label ); ?>
										</label>
									<?php endforeach; ?>
								</div>
							</div>

							<hr class="jbg-divider">

							<div class="jbg-field jbg-image-row">
								<span class="jbg-field-label"><?php esc_html_e( 'Background Image', 'jasanika' ); ?></span>
								<input
									type="hidden"
									id="<?php echo esc_attr( $id_field_id ); ?>"
									name="jasanika_settings[<?php echo esc_attr( $prefix . 'image_id' ); ?>]"
									value="<?php echo esc_attr( (string) $bg_image_id ); ?>"
								>
								<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
									<button
										type="button"
										class="button jasanika-bg-select-btn"
										data-id-target="<?php echo esc_attr( $id_field_id ); ?>"
										data-preview="<?php echo esc_attr( $preview_id ); ?>"
										data-remove-btn="<?php echo esc_attr( $remove_btn_id ); ?>"
										data-title="<?php esc_attr_e( 'Select Background Image', 'jasanika' ); ?>"
									>
										<?php esc_html_e( 'Select Image', 'jasanika' ); ?>
									</button>
									<button
										type="button"
										id="<?php echo esc_attr( $remove_btn_id ); ?>"
										class="button jasanika-bg-remove-btn"
										data-id-target="<?php echo esc_attr( $id_field_id ); ?>"
										data-preview="<?php echo esc_attr( $preview_id ); ?>"
										style="<?php echo $bg_preview_url ? '' : 'display:none;'; ?>"
									>
										<?php esc_html_e( 'Remove Image', 'jasanika' ); ?>
									</button>
								</div>
								<img
									id="<?php echo esc_attr( $preview_id ); ?>"
									src="<?php echo $bg_preview_url ? esc_url( $bg_preview_url ) : ''; ?>"
									class="jbg-preview-img"
									alt=""
									style="<?php echo $bg_preview_url ? '' : 'display:none;'; ?>"
								>
							</div>

							<div class="jbg-field jbg-fit-row">
								<label for="<?php echo esc_attr( 'jbg_' . $key . '_fit' ); ?>" class="jbg-field-label"><?php esc_html_e( 'Image Fit', 'jasanika' ); ?></label>
								<select id="<?php echo esc_attr( 'jbg_' . $key . '_fit' ); ?>" name="jasanika_settings[<?php echo esc_attr( $prefix . 'fit' ); ?>]" class="jbg-select">
									<?php foreach ( $fit_options as $fit_val => $fit_label ) : ?>
										<option value="<?php echo esc_attr( $fit_val ); ?>" <?php selected( $bg_fit, $fit_val ); ?>><?php echo esc_html( $fit_label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="jbg-field jbg-position-row">
								<label for="<?php echo esc_attr( 'jbg_' . $key . '_position' ); ?>" class="jbg-field-label"><?php esc_html_e( 'Image Position', 'jasanika' ); ?></label>
								<select id="<?php echo esc_attr( 'jbg_' . $key . '_position' ); ?>" name="jasanika_settings[<?php echo esc_attr( $prefix . 'position' ); ?>]" class="jbg-select">
									<?php foreach ( $position_options as $pos_val => $pos_label ) : ?>
										<option value="<?php echo esc_attr( $pos_val ); ?>" <?php selected( $bg_position, $pos_val ); ?>><?php echo esc_html( $pos_label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="jbg-field jbg-repeat-row">
								<label>
									<input type="checkbox" name="jasanika_settings[<?php echo esc_attr( $prefix . 'repeat' ); ?>]" value="1" <?php checked( $bg_repeat ); ?>>
									<?php esc_html_e( 'Repeat Image', 'jasanika' ); ?>
								</label>
							</div>

							<hr class="jbg-divider">

							<div class="jbg-field jbg-overlay-row">
								<span class="jbg-field-label"><?php esc_html_e( 'Overlay Color', 'jasanika' ); ?></span>
								<div class="jbg-color-wrap">
									<input
										type="color"
										class="jbg-overlay-color-native"
										value="<?php echo esc_attr( $bg_overlay_color ); ?>"
										data-text-target="<?php echo esc_attr( $overlay_text_id ); ?>"
									>
									<input
										type="text"
										id="<?php echo esc_attr( $overlay_text_id ); ?>"
										name="jasanika_settings[<?php echo esc_attr( $prefix . 'overlay_color' ); ?>]"
										value="<?php echo esc_attr( $bg_overlay_color ); ?>"
										class="small-text"
										pattern="^#[0-9a-fA-F]{6}$"
										maxlength="7"
										style="font-family:monospace;"
									>
								</div>
							</div>

							<div class="jbg-field jbg-overlay-row">
								<span class="jbg-field-label"><?php esc_html_e( 'Overlay Opacity', 'jasanika' ); ?></span>
								<div class="jbg-opacity-wrap">
									<input
										type="range"
										class="jbg-opacity-range"
										name="jasanika_settings[<?php echo esc_attr( $prefix . 'overlay_opacity' ); ?>]"
										min="0"
										max="100"
										value="<?php echo esc_attr( (string) $bg_overlay_opacity ); ?>"
										data-display="<?php echo esc_attr( $opacity_display_id ); ?>"
									>
									<span id="<?php echo esc_attr( $opacity_display_id ); ?>" class="jbg-opacity-display">
										<?php echo esc_html( $bg_overlay_opacity . '%' ); ?>
									</span>
								</div>
							</div>

						</div><!-- .jasanika-bg-fields -->

					</details>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}
