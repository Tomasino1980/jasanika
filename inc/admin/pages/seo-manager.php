<?php

/**
 * Jasanika Admin – SEO Manager Page
 *
 * Registers and renders the SEO Manager admin page.
 * Settings are grouped in four sections:
 *   – Global SEO     (title template, description, keywords, robots)
 *   – Homepage SEO   (title, description, keywords overrides)
 *   – Open Graph     (og:title, og:description, og:image)
 *   – Twitter Cards  (twitter:title, twitter:description, twitter:image)
 *
 * All values are stored in the jasanika_seo_settings option.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'jasanika_seo_settings_init' );
add_action( 'admin_enqueue_scripts', 'jasanika_seo_manager_enqueue' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue the SEO Manager stylesheet and media uploader on the SEO Manager page.
 *
 * Also performs a one-time rewrite flush so the sitemap becomes available
 * immediately after the SEO Manager is first opened.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_seo_manager_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-seo-manager' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'jasanika-seo-manager',
		get_template_directory_uri() . '/assets/css/admin/seo-manager.css',
		array(),
		'0.41.0'
	);

	wp_enqueue_media();

	wp_enqueue_script(
		'jasanika-seo-manager',
		get_template_directory_uri() . '/assets/js/admin/media-uploader.js',
		array(),
		'0.41.0',
		true
	);

	// One-time rewrite flush to activate the sitemap rewrite rule.
	if ( ! get_option( 'jasanika_seo_sitemap_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'jasanika_seo_sitemap_flushed', '1' );
	}
}

// ---------------------------------------------------------------------------
// Settings Registration
// ---------------------------------------------------------------------------

/**
 * Register the SEO settings group, sections and fields.
 */
function jasanika_seo_settings_init(): void {

	register_setting(
		'jasanika_seo_settings_group',
		'jasanika_seo_settings',
		array( 'sanitize_callback' => 'jasanika_seo_sanitize_settings' )
	);

	// ---- Global SEO --------------------------------------------------------

	add_settings_section(
		'jasanika_seo_section_global',
		__( 'Global SEO Settings', 'jasanika' ),
		'jasanika_seo_section_global_description',
		'jasanika-seo-manager'
	);

	add_settings_field(
		'jasanika_seo_title_template',
		__( 'Site Title Template', 'jasanika' ),
		'jasanika_seo_field_title_template',
		'jasanika-seo-manager',
		'jasanika_seo_section_global'
	);

	add_settings_field(
		'jasanika_seo_meta_description',
		__( 'Meta Description', 'jasanika' ),
		'jasanika_seo_field_meta_description',
		'jasanika-seo-manager',
		'jasanika_seo_section_global'
	);

	add_settings_field(
		'jasanika_seo_meta_keywords',
		__( 'Meta Keywords', 'jasanika' ),
		'jasanika_seo_field_meta_keywords',
		'jasanika-seo-manager',
		'jasanika_seo_section_global'
	);

	add_settings_field(
		'jasanika_seo_robots_index',
		__( 'Robots Index', 'jasanika' ),
		'jasanika_seo_field_robots_index',
		'jasanika-seo-manager',
		'jasanika_seo_section_global'
	);

	add_settings_field(
		'jasanika_seo_robots_follow',
		__( 'Robots Follow', 'jasanika' ),
		'jasanika_seo_field_robots_follow',
		'jasanika-seo-manager',
		'jasanika_seo_section_global'
	);

	// ---- Homepage SEO ------------------------------------------------------

	add_settings_section(
		'jasanika_seo_section_homepage',
		__( 'Homepage SEO', 'jasanika' ),
		'jasanika_seo_section_homepage_description',
		'jasanika-seo-manager'
	);

	add_settings_field(
		'jasanika_seo_homepage_title',
		__( 'Homepage Title', 'jasanika' ),
		'jasanika_seo_field_homepage_title',
		'jasanika-seo-manager',
		'jasanika_seo_section_homepage'
	);

	add_settings_field(
		'jasanika_seo_homepage_description',
		__( 'Homepage Description', 'jasanika' ),
		'jasanika_seo_field_homepage_description',
		'jasanika-seo-manager',
		'jasanika_seo_section_homepage'
	);

	add_settings_field(
		'jasanika_seo_homepage_keywords',
		__( 'Homepage Keywords', 'jasanika' ),
		'jasanika_seo_field_homepage_keywords',
		'jasanika-seo-manager',
		'jasanika_seo_section_homepage'
	);

	// ---- Open Graph --------------------------------------------------------

	add_settings_section(
		'jasanika_seo_section_og',
		__( 'Open Graph', 'jasanika' ),
		'jasanika_seo_section_og_description',
		'jasanika-seo-manager'
	);

	add_settings_field(
		'jasanika_seo_og_title',
		__( 'OG Title', 'jasanika' ),
		'jasanika_seo_field_og_title',
		'jasanika-seo-manager',
		'jasanika_seo_section_og'
	);

	add_settings_field(
		'jasanika_seo_og_description',
		__( 'OG Description', 'jasanika' ),
		'jasanika_seo_field_og_description',
		'jasanika-seo-manager',
		'jasanika_seo_section_og'
	);

	add_settings_field(
		'jasanika_seo_og_image',
		__( 'OG Image', 'jasanika' ),
		'jasanika_seo_field_og_image',
		'jasanika-seo-manager',
		'jasanika_seo_section_og'
	);

	// ---- Twitter Cards -----------------------------------------------------

	add_settings_section(
		'jasanika_seo_section_twitter',
		__( 'Twitter Cards', 'jasanika' ),
		'jasanika_seo_section_twitter_description',
		'jasanika-seo-manager'
	);

	add_settings_field(
		'jasanika_seo_twitter_title',
		__( 'Twitter Title', 'jasanika' ),
		'jasanika_seo_field_twitter_title',
		'jasanika-seo-manager',
		'jasanika_seo_section_twitter'
	);

	add_settings_field(
		'jasanika_seo_twitter_description',
		__( 'Twitter Description', 'jasanika' ),
		'jasanika_seo_field_twitter_description',
		'jasanika-seo-manager',
		'jasanika_seo_section_twitter'
	);

	add_settings_field(
		'jasanika_seo_twitter_image',
		__( 'Twitter Image', 'jasanika' ),
		'jasanika_seo_field_twitter_image',
		'jasanika-seo-manager',
		'jasanika_seo_section_twitter'
	);
}

// ---------------------------------------------------------------------------
// Section Descriptions
// ---------------------------------------------------------------------------

/** Render Global SEO section description. */
function jasanika_seo_section_global_description(): void {
	echo '<p class="jasanika-seo__section-desc">'
		. esc_html__( 'Default SEO values applied to all pages. Use {page_title} and {site_name} as placeholders in the title template.', 'jasanika' )
		. '</p>';
}

/** Render Homepage SEO section description. */
function jasanika_seo_section_homepage_description(): void {
	echo '<p class="jasanika-seo__section-desc">'
		. esc_html__( 'Override the global SEO values specifically for the homepage.', 'jasanika' )
		. '</p>';
}

/** Render Open Graph section description. */
function jasanika_seo_section_og_description(): void {
	echo '<p class="jasanika-seo__section-desc">'
		. esc_html__( 'Default Open Graph values used when pages are shared on social media. Leave empty to fall back to SEO title and description.', 'jasanika' )
		. '</p>';
}

/** Render Twitter Cards section description. */
function jasanika_seo_section_twitter_description(): void {
	echo '<p class="jasanika-seo__section-desc">'
		. esc_html__( 'Default Twitter Card values. Leave empty to fall back to SEO title and description.', 'jasanika' )
		. '</p>';
}

// ---------------------------------------------------------------------------
// Field Renderers – Global SEO
// ---------------------------------------------------------------------------

/** Render the Site Title Template field. */
function jasanika_seo_field_title_template(): void {
	$settings = jasanika_seo_get_settings();
	?>
	<input
		type="text"
		name="jasanika_seo_settings[title_template]"
		id="jasanika_seo_title_template"
		class="jasanika-seo__input regular-text"
		value="<?php echo esc_attr( $settings['title_template'] ); ?>"
	>
	<p class="jasanika-seo__hint">
		<?php esc_html_e( 'Placeholders: {page_title} and {site_name}. Example: {page_title} | {site_name}', 'jasanika' ); ?>
	</p>
	<?php
}

/** Render the Meta Description field. */
function jasanika_seo_field_meta_description(): void {
	$settings = jasanika_seo_get_settings();
	?>
	<textarea
		name="jasanika_seo_settings[meta_description]"
		id="jasanika_seo_meta_description"
		class="jasanika-seo__textarea large-text"
		rows="3"
	><?php echo esc_textarea( $settings['meta_description'] ); ?></textarea>
	<p class="jasanika-seo__hint">
		<?php esc_html_e( 'Default meta description for all pages. Recommended: 120–160 characters.', 'jasanika' ); ?>
	</p>
	<?php
}

/** Render the Meta Keywords field. */
function jasanika_seo_field_meta_keywords(): void {
	$settings = jasanika_seo_get_settings();
	?>
	<input
		type="text"
		name="jasanika_seo_settings[meta_keywords]"
		id="jasanika_seo_meta_keywords"
		class="jasanika-seo__input large-text"
		value="<?php echo esc_attr( $settings['meta_keywords'] ); ?>"
		placeholder="keyword1, keyword2, keyword3"
	>
	<p class="jasanika-seo__hint">
		<?php esc_html_e( 'Comma-separated list of default keywords.', 'jasanika' ); ?>
	</p>
	<?php
}

/** Render the Robots Index field. */
function jasanika_seo_field_robots_index(): void {
	$settings = jasanika_seo_get_settings();
	$checked  = ! empty( $settings['robots_index'] );
	?>
	<label class="jasanika-seo__toggle">
		<input
			type="checkbox"
			name="jasanika_seo_settings[robots_index]"
			id="jasanika_seo_robots_index"
			value="1"
			<?php checked( $checked ); ?>
		>
		<span class="jasanika-seo__toggle-label">
			<?php esc_html_e( 'Allow search engines to index pages (index)', 'jasanika' ); ?>
		</span>
	</label>
	<?php
}

/** Render the Robots Follow field. */
function jasanika_seo_field_robots_follow(): void {
	$settings = jasanika_seo_get_settings();
	$checked  = ! empty( $settings['robots_follow'] );
	?>
	<label class="jasanika-seo__toggle">
		<input
			type="checkbox"
			name="jasanika_seo_settings[robots_follow]"
			id="jasanika_seo_robots_follow"
			value="1"
			<?php checked( $checked ); ?>
		>
		<span class="jasanika-seo__toggle-label">
			<?php esc_html_e( 'Allow search engines to follow links (follow)', 'jasanika' ); ?>
		</span>
	</label>
	<?php
}

// ---------------------------------------------------------------------------
// Field Renderers – Homepage SEO
// ---------------------------------------------------------------------------

/** Render the Homepage Title field. */
function jasanika_seo_field_homepage_title(): void {
	$settings = jasanika_seo_get_settings();
	?>
	<input
		type="text"
		name="jasanika_seo_settings[homepage_title]"
		id="jasanika_seo_homepage_title"
		class="jasanika-seo__input large-text"
		value="<?php echo esc_attr( $settings['homepage_title'] ); ?>"
		placeholder="<?php esc_attr_e( 'Leave empty to use the title template', 'jasanika' ); ?>"
	>
	<?php
}

/** Render the Homepage Description field. */
function jasanika_seo_field_homepage_description(): void {
	$settings = jasanika_seo_get_settings();
	?>
	<textarea
		name="jasanika_seo_settings[homepage_description]"
		id="jasanika_seo_homepage_description"
		class="jasanika-seo__textarea large-text"
		rows="3"
		placeholder="<?php esc_attr_e( 'Leave empty to use the global meta description', 'jasanika' ); ?>"
	><?php echo esc_textarea( $settings['homepage_description'] ); ?></textarea>
	<?php
}

/** Render the Homepage Keywords field. */
function jasanika_seo_field_homepage_keywords(): void {
	$settings = jasanika_seo_get_settings();
	?>
	<input
		type="text"
		name="jasanika_seo_settings[homepage_keywords]"
		id="jasanika_seo_homepage_keywords"
		class="jasanika-seo__input large-text"
		value="<?php echo esc_attr( $settings['homepage_keywords'] ); ?>"
		placeholder="<?php esc_attr_e( 'Leave empty to use global keywords', 'jasanika' ); ?>"
	>
	<?php
}

// ---------------------------------------------------------------------------
// Field Renderers – Open Graph
// ---------------------------------------------------------------------------

/** Render the OG Title field. */
function jasanika_seo_field_og_title(): void {
	$settings = jasanika_seo_get_settings();
	?>
	<input
		type="text"
		name="jasanika_seo_settings[og_title]"
		id="jasanika_seo_og_title"
		class="jasanika-seo__input large-text"
		value="<?php echo esc_attr( $settings['og_title'] ); ?>"
		placeholder="<?php esc_attr_e( 'Leave empty to use SEO title', 'jasanika' ); ?>"
	>
	<?php
}

/** Render the OG Description field. */
function jasanika_seo_field_og_description(): void {
	$settings = jasanika_seo_get_settings();
	?>
	<textarea
		name="jasanika_seo_settings[og_description]"
		id="jasanika_seo_og_description"
		class="jasanika-seo__textarea large-text"
		rows="3"
		placeholder="<?php esc_attr_e( 'Leave empty to use meta description', 'jasanika' ); ?>"
	><?php echo esc_textarea( $settings['og_description'] ); ?></textarea>
	<?php
}

/** Render the OG Image field. */
function jasanika_seo_field_og_image(): void {
	$settings = jasanika_seo_get_settings();
	$image    = $settings['og_image'];
	?>
	<div class="jasanika-seo__media-field">
		<input
			type="text"
			name="jasanika_seo_settings[og_image]"
			id="jasanika_seo_og_image"
			class="jasanika-seo__input large-text jasanika-media-url"
			value="<?php echo esc_attr( $image ); ?>"
			placeholder="<?php esc_attr_e( 'Image URL', 'jasanika' ); ?>"
		>
		<button
			type="button"
			class="button jasanika-media-upload"
			data-target="#jasanika_seo_og_image"
			data-preview="#jasanika-og-image-preview"
		>
			<?php esc_html_e( 'Select Image', 'jasanika' ); ?>
		</button>
	</div>
	<?php if ( $image ) : ?>
		<div class="jasanika-seo__media-preview" id="jasanika-og-image-preview">
			<img src="<?php echo esc_url( $image ); ?>" alt="">
		</div>
	<?php else : ?>
		<div class="jasanika-seo__media-preview" id="jasanika-og-image-preview" style="display:none;"></div>
	<?php endif; ?>
	<p class="jasanika-seo__hint">
		<?php esc_html_e( 'Recommended size: 1200×630 px. Falls back to post thumbnail on single posts.', 'jasanika' ); ?>
	</p>
	<?php
}

// ---------------------------------------------------------------------------
// Field Renderers – Twitter Cards
// ---------------------------------------------------------------------------

/** Render the Twitter Title field. */
function jasanika_seo_field_twitter_title(): void {
	$settings = jasanika_seo_get_settings();
	?>
	<input
		type="text"
		name="jasanika_seo_settings[twitter_title]"
		id="jasanika_seo_twitter_title"
		class="jasanika-seo__input large-text"
		value="<?php echo esc_attr( $settings['twitter_title'] ); ?>"
		placeholder="<?php esc_attr_e( 'Leave empty to use SEO title', 'jasanika' ); ?>"
	>
	<?php
}

/** Render the Twitter Description field. */
function jasanika_seo_field_twitter_description(): void {
	$settings = jasanika_seo_get_settings();
	?>
	<textarea
		name="jasanika_seo_settings[twitter_description]"
		id="jasanika_seo_twitter_description"
		class="jasanika-seo__textarea large-text"
		rows="3"
		placeholder="<?php esc_attr_e( 'Leave empty to use meta description', 'jasanika' ); ?>"
	><?php echo esc_textarea( $settings['twitter_description'] ); ?></textarea>
	<?php
}

/** Render the Twitter Image field. */
function jasanika_seo_field_twitter_image(): void {
	$settings = jasanika_seo_get_settings();
	$image    = $settings['twitter_image'];
	?>
	<div class="jasanika-seo__media-field">
		<input
			type="text"
			name="jasanika_seo_settings[twitter_image]"
			id="jasanika_seo_twitter_image"
			class="jasanika-seo__input large-text jasanika-media-url"
			value="<?php echo esc_attr( $image ); ?>"
			placeholder="<?php esc_attr_e( 'Image URL', 'jasanika' ); ?>"
		>
		<button
			type="button"
			class="button jasanika-media-upload"
			data-target="#jasanika_seo_twitter_image"
			data-preview="#jasanika-twitter-image-preview"
		>
			<?php esc_html_e( 'Select Image', 'jasanika' ); ?>
		</button>
	</div>
	<?php if ( $image ) : ?>
		<div class="jasanika-seo__media-preview" id="jasanika-twitter-image-preview">
			<img src="<?php echo esc_url( $image ); ?>" alt="">
		</div>
	<?php else : ?>
		<div class="jasanika-seo__media-preview" id="jasanika-twitter-image-preview" style="display:none;"></div>
	<?php endif; ?>
	<p class="jasanika-seo__hint">
		<?php esc_html_e( 'Recommended size: 1200×628 px. Falls back to post thumbnail on single posts.', 'jasanika' ); ?>
	</p>
	<?php
}

// ---------------------------------------------------------------------------
// Sanitization
// ---------------------------------------------------------------------------

/**
 * Sanitize all SEO settings before saving.
 *
 * @param mixed $input Raw input array.
 * @return array<string, string>
 */
function jasanika_seo_sanitize_settings( $input ): array {
	if ( ! is_array( $input ) ) {
		return array();
	}

	$sanitized = array();

	$text_fields = array(
		'title_template',
		'meta_keywords',
		'homepage_title',
		'homepage_keywords',
		'og_title',
		'twitter_title',
	);

	$textarea_fields = array(
		'meta_description',
		'homepage_description',
		'og_description',
		'twitter_description',
	);

	$url_fields = array(
		'og_image',
		'twitter_image',
	);

	foreach ( $text_fields as $field ) {
		$sanitized[ $field ] = isset( $input[ $field ] )
			? sanitize_text_field( wp_unslash( $input[ $field ] ) )
			: '';
	}

	foreach ( $textarea_fields as $field ) {
		$sanitized[ $field ] = isset( $input[ $field ] )
			? sanitize_textarea_field( wp_unslash( $input[ $field ] ) )
			: '';
	}

	foreach ( $url_fields as $field ) {
		$sanitized[ $field ] = isset( $input[ $field ] )
			? esc_url_raw( wp_unslash( $input[ $field ] ) )
			: '';
	}

	// Checkbox fields (present = 1, absent = '').
	$sanitized['robots_index']  = ! empty( $input['robots_index'] ) ? '1' : '';
	$sanitized['robots_follow'] = ! empty( $input['robots_follow'] ) ? '1' : '';

	return $sanitized;
}

// ---------------------------------------------------------------------------
// Page Renderer
// ---------------------------------------------------------------------------

/**
 * Render the SEO Manager admin page.
 */
function jasanika_admin_page_seo_manager(): void {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'jasanika' ) );
	}

	$sitemap_url = home_url( '/sitemap.xml' );

	?>
	<div class="wrap jasanika-seo">

		<!-- Header -->
		<div class="jasanika-seo__header">
			<span class="jasanika-seo__header-icon dashicons dashicons-search"></span>
			<div>
				<h1 class="jasanika-seo__title"><?php esc_html_e( 'SEO Manager', 'jasanika' ); ?></h1>
				<p class="jasanika-seo__subtitle">
					<?php esc_html_e( 'Built-in SEO management for Jasanika. No third-party plugins required.', 'jasanika' ); ?>
				</p>
			</div>
		</div>

		<!-- Sitemap notice -->
		<div class="jasanika-seo__sitemap-notice">
			<span class="dashicons dashicons-location-alt"></span>
			<?php
			printf(
				/* translators: %s sitemap URL */
				esc_html__( 'Sitemap: %s', 'jasanika' ),
				'<a href="' . esc_url( $sitemap_url ) . '" target="_blank">' . esc_html( $sitemap_url ) . '</a>'
			);
			?>
		</div>

		<?php settings_errors( 'jasanika_seo_settings' ); ?>

		<form method="post" action="options.php" class="jasanika-seo__form">
			<?php
			settings_fields( 'jasanika_seo_settings_group' );
			do_settings_sections( 'jasanika-seo-manager' );
			submit_button( __( 'Save SEO Settings', 'jasanika' ), 'primary jasanika-seo__submit' );
			?>
		</form>

	</div>
	<?php
}
