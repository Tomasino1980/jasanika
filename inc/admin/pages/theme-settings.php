<?php

/**
 * Jasanika Admin – Theme Settings Page
 *
 * Registers and renders the Theme Settings admin page using the WordPress Settings API.
 * Sections: Branding, Contact Information, Social Networks, Footer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'jasanika_theme_settings_init' );
add_action( 'admin_enqueue_scripts', 'jasanika_theme_settings_enqueue' );

// ---------------------------------------------------------------------------
// Enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue the WordPress media uploader on the Theme Settings page.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_theme_settings_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-theme-settings' !== $hook ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_script(
		'jasanika-media-uploader',
		get_template_directory_uri() . '/assets/js/admin/media-uploader.js',
		array(),
		'0.35.0',
		true
	);
}

// ---------------------------------------------------------------------------
// Settings Registration
// ---------------------------------------------------------------------------

/**
 * Register the settings group, sections and fields.
 */
function jasanika_theme_settings_init(): void {

	register_setting(
		'jasanika_settings_group',
		'jasanika_settings',
		array( 'sanitize_callback' => 'jasanika_sanitize_settings' )
	);

	// --- Branding Section ---------------------------------------------------

	add_settings_section(
		'jasanika_section_branding',
		__( 'Branding', 'jasanika' ),
		'__return_false',
		'jasanika-theme-settings'
	);

	add_settings_field(
		'jasanika_company_name',
		__( 'Company Name', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array( 'key' => 'company_name' )
	);

	add_settings_field(
		'jasanika_company_slogan',
		__( 'Company Slogan', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array( 'key' => 'company_slogan' )
	);

	add_settings_field(
		'jasanika_company_description',
		__( 'Company Description', 'jasanika' ),
		'jasanika_settings_field_textarea',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array(
			'key'         => 'company_description',
			'placeholder' => __( 'Short description of your company', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_logo_url',
		__( 'Header Logo', 'jasanika' ),
		'jasanika_settings_field_media',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array(
			'key'         => 'logo_url',
			'media_title' => __( 'Select Header Logo', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_footer_logo_url',
		__( 'Footer Logo', 'jasanika' ),
		'jasanika_settings_field_media',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array(
			'key'         => 'footer_logo_url',
			'media_title' => __( 'Select Footer Logo', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_favicon_url',
		__( 'Favicon', 'jasanika' ),
		'jasanika_settings_field_media',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array(
			'key'         => 'favicon_url',
			'media_title' => __( 'Select Favicon', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_brand_primary',
		__( 'Primary Color', 'jasanika' ),
		'jasanika_settings_field_color',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array(
			'key'     => 'brand_primary',
			'default' => '#b78acb',
		)
	);

	add_settings_field(
		'jasanika_brand_secondary',
		__( 'Secondary Color', 'jasanika' ),
		'jasanika_settings_field_color',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array(
			'key'     => 'brand_secondary',
			'default' => '#24212b',
		)
	);

	add_settings_field(
		'jasanika_brand_accent',
		__( 'Accent Color', 'jasanika' ),
		'jasanika_settings_field_color',
		'jasanika-theme-settings',
		'jasanika_section_branding',
		array(
			'key'     => 'brand_accent',
			'default' => '#f1c95d',
		)
	);

	// --- Contact Information Section ----------------------------------------

	add_settings_section(
		'jasanika_section_contact',
		__( 'Contact Information', 'jasanika' ),
		'__return_false',
		'jasanika-theme-settings'
	);

	add_settings_field(
		'jasanika_phone',
		__( 'Phone', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_contact',
		array( 'key' => 'phone' )
	);

	add_settings_field(
		'jasanika_email',
		__( 'Email', 'jasanika' ),
		'jasanika_settings_field_email',
		'jasanika-theme-settings',
		'jasanika_section_contact',
		array( 'key' => 'email' )
	);

	add_settings_field(
		'jasanika_address_street',
		__( 'Street Address', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_contact',
		array( 'key' => 'address_street' )
	);

	add_settings_field(
		'jasanika_address_city',
		__( 'City', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_contact',
		array( 'key' => 'address_city' )
	);

	add_settings_field(
		'jasanika_address_zip',
		__( 'ZIP Code', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_contact',
		array( 'key' => 'address_zip' )
	);

	// --- Social Networks Section ---------------------------------------------

	add_settings_section(
		'jasanika_section_social',
		__( 'Social Networks', 'jasanika' ),
		'__return_false',
		'jasanika-theme-settings'
	);

	add_settings_field(
		'jasanika_facebook_url',
		__( 'Facebook URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_social',
		array( 'key' => 'facebook_url' )
	);

	add_settings_field(
		'jasanika_instagram_url',
		__( 'Instagram URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_social',
		array( 'key' => 'instagram_url' )
	);

	add_settings_field(
		'jasanika_youtube_url',
		__( 'YouTube URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_social',
		array( 'key' => 'youtube_url' )
	);

	add_settings_field(
		'jasanika_linkedin_url',
		__( 'LinkedIn URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_social',
		array( 'key' => 'linkedin_url' )
	);

	// --- Footer Section ------------------------------------------------------

	add_settings_section(
		'jasanika_section_footer',
		__( 'Footer', 'jasanika' ),
		'__return_false',
		'jasanika-theme-settings'
	);

	add_settings_field(
		'jasanika_copyright_text',
		__( 'Copyright Text', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_footer',
		array(
			'key'         => 'copyright_text',
			'placeholder' => sprintf( '© %s Company Name', gmdate( 'Y' ) ),
		)
	);

	add_settings_field(
		'jasanika_footer_note',
		__( 'Footer Note', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_footer',
		array(
			'key'         => 'footer_note',
			'placeholder' => __( 'Handmade with love', 'jasanika' ),
		)
	);

	// --- Homepage Content Section --------------------------------------------

	add_settings_section(
		'jasanika_section_homepage',
		__( 'Homepage Content', 'jasanika' ),
		'__return_false',
		'jasanika-theme-settings'
	);

	// Hero defaults.
	add_settings_field(
		'jasanika_hero_heading',
		__( 'Hero Heading', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'hero_heading',
			'placeholder' => __( 'Vítejte na Jasanika', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_hero_description',
		__( 'Hero Description', 'jasanika' ),
		'jasanika_settings_field_textarea',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'hero_description',
			'placeholder' => __( 'Ručně tvořený WordPress obchod a blog.', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_hero_button_text',
		__( 'Hero Button Text', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'hero_button_text',
			'placeholder' => __( 'Zjistit více', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_hero_button_url',
		__( 'Hero Button URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array( 'key' => 'hero_button_url' )
	);

	// Feature Block 1.
	add_settings_field(
		'jasanika_feature_1_title',
		__( 'Feature Block 1 – Title', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'feature_1_title',
			'placeholder' => __( 'Handmade Products', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_feature_1_description',
		__( 'Feature Block 1 – Description', 'jasanika' ),
		'jasanika_settings_field_textarea',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'feature_1_description',
			'placeholder' => __( 'Discover custom handmade creations.', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_feature_1_button_text',
		__( 'Feature Block 1 – Button Text', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'feature_1_button_text',
			'placeholder' => __( 'Explore', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_feature_1_button_url',
		__( 'Feature Block 1 – Button URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array( 'key' => 'feature_1_button_url' )
	);

	// Feature Block 2.
	add_settings_field(
		'jasanika_feature_2_title',
		__( 'Feature Block 2 – Title', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'feature_2_title',
			'placeholder' => __( 'Blog Articles', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_feature_2_description',
		__( 'Feature Block 2 – Description', 'jasanika' ),
		'jasanika_settings_field_textarea',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'feature_2_description',
			'placeholder' => __( 'Read tutorials and project stories.', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_feature_2_button_text',
		__( 'Feature Block 2 – Button Text', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'feature_2_button_text',
			'placeholder' => __( 'Read More', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_feature_2_button_url',
		__( 'Feature Block 2 – Button URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array( 'key' => 'feature_2_button_url' )
	);

	// Feature Block 3.
	add_settings_field(
		'jasanika_feature_3_title',
		__( 'Feature Block 3 – Title', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'feature_3_title',
			'placeholder' => __( 'Custom Orders', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_feature_3_description',
		__( 'Feature Block 3 – Description', 'jasanika' ),
		'jasanika_settings_field_textarea',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'feature_3_description',
			'placeholder' => __( 'Request personalized products.', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_feature_3_button_text',
		__( 'Feature Block 3 – Button Text', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'feature_3_button_text',
			'placeholder' => __( 'Get in Touch', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_feature_3_button_url',
		__( 'Feature Block 3 – Button URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array( 'key' => 'feature_3_button_url' )
	);

	// CTA Section.
	add_settings_field(
		'jasanika_cta_title',
		__( 'CTA Heading', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'cta_title',
			'placeholder' => __( 'Máte vlastní nápad?', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_cta_description',
		__( 'CTA Description', 'jasanika' ),
		'jasanika_settings_field_textarea',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'cta_description',
			'placeholder' => __( 'Vyrábíme zakázkové výrobky podle vašich představ. Kontaktujte nás a společně najdeme řešení.', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_cta_button_text',
		__( 'CTA Button Text', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'cta_button_text',
			'placeholder' => __( 'Kontaktujte nás', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_cta_button_url',
		__( 'CTA Button URL', 'jasanika' ),
		'jasanika_settings_field_url',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array( 'key' => 'cta_button_url' )
	);

	// Categories section.
	add_settings_field(
		'jasanika_categories_section_title',
		__( 'Categories Section Title', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'categories_section_title',
			'placeholder' => __( 'Procházet kategorie', 'jasanika' ),
		)
	);

	// Latest posts section.
	add_settings_field(
		'jasanika_latest_posts_section_title',
		__( 'Latest Posts Section Title', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key'         => 'latest_posts_section_title',
			'placeholder' => __( 'Nejnovější články', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_latest_posts_count',
		__( 'Number of Posts', 'jasanika' ),
		'jasanika_settings_field_number',
		'jasanika-theme-settings',
		'jasanika_section_homepage',
		array(
			'key' => 'latest_posts_count',
			'min' => 1,
			'max' => 12,
		)
	);

	// --- Featured Products Section ------------------------------------------

	add_settings_section(
		'jasanika_section_featured_products',
		__( 'Featured Products', 'jasanika' ),
		'__return_false',
		'jasanika-theme-settings'
	);

	add_settings_field(
		'jasanika_featured_products_title',
		__( 'Section Title', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_featured_products',
		array(
			'key'         => 'featured_products_title',
			'placeholder' => __( 'Featured Products', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_featured_products_description',
		__( 'Section Description', 'jasanika' ),
		'jasanika_settings_field_textarea',
		'jasanika-theme-settings',
		'jasanika_section_featured_products',
		array(
			'key'         => 'featured_products_description',
			'placeholder' => __( 'Explore our latest handcrafted creations.', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_featured_products_count',
		__( 'Number of Products', 'jasanika' ),
		'jasanika_settings_field_number',
		'jasanika-theme-settings',
		'jasanika_section_featured_products',
		array(
			'key' => 'featured_products_count',
			'min' => 1,
			'max' => 12,
		)
	);

	// --- Homepage Builder Section -------------------------------------------

	add_settings_section(
		'jasanika_section_homepage_builder',
		__( 'Homepage Builder', 'jasanika' ),
		'jasanika_settings_section_homepage_builder_cb',
		'jasanika-theme-settings'
	);

	// --- Footer Builder Section ----------------------------------------------

	add_settings_section(
		'jasanika_section_footer_builder',
		__( 'Footer Builder', 'jasanika' ),
		'__return_false',
		'jasanika-theme-settings'
	);

	// Footer Columns 1–4.
	for ( $i = 1; $i <= 4; $i++ ) {
		add_settings_field(
			"jasanika_footer_col_{$i}_title",
			/* translators: %d: column number */
			sprintf( __( 'Footer Column %d – Title', 'jasanika' ), $i ),
			'jasanika_settings_field_text',
			'jasanika-theme-settings',
			'jasanika_section_footer_builder',
			array( 'key' => "footer_col_{$i}_title" )
		);

		add_settings_field(
			"jasanika_footer_col_{$i}_content",
			/* translators: %d: column number */
			sprintf( __( 'Footer Column %d – Content', 'jasanika' ), $i ),
			'jasanika_settings_field_kses_textarea',
			'jasanika-theme-settings',
			'jasanika_section_footer_builder',
			array(
				'key'         => "footer_col_{$i}_content",
				'description' => __( 'Supports plain text, HTML links and basic formatting.', 'jasanika' ),
			)
		);
	}

	// Footer Contact Block.
	add_settings_field(
		'jasanika_footer_contact_company',
		__( 'Footer Contact – Company Name', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_footer_builder',
		array(
			'key'         => 'footer_contact_company',
			'placeholder' => __( 'Leave empty to use Company Name from Branding', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_footer_contact_phone',
		__( 'Footer Contact – Phone', 'jasanika' ),
		'jasanika_settings_field_text',
		'jasanika-theme-settings',
		'jasanika_section_footer_builder',
		array(
			'key'         => 'footer_contact_phone',
			'placeholder' => __( 'Leave empty to use Phone from Contact Information', 'jasanika' ),
		)
	);

	add_settings_field(
		'jasanika_footer_contact_email',
		__( 'Footer Contact – Email', 'jasanika' ),
		'jasanika_settings_field_email',
		'jasanika-theme-settings',
		'jasanika_section_footer_builder',
		array( 'key' => 'footer_contact_email' )
	);

	add_settings_field(
		'jasanika_footer_contact_address',
		__( 'Footer Contact – Address', 'jasanika' ),
		'jasanika_settings_field_textarea',
		'jasanika-theme-settings',
		'jasanika_section_footer_builder',
		array(
			'key'         => 'footer_contact_address',
			'placeholder' => __( 'Leave empty to use Address from Contact Information', 'jasanika' ),
		)
	);
}

// ---------------------------------------------------------------------------
// Sanitize
// ---------------------------------------------------------------------------

/**
 * Sanitize all settings before saving.
 *
 * @param mixed $input Raw input array.
 * @return array Sanitized values.
 */
function jasanika_sanitize_settings( mixed $input ): array {
	if ( ! is_array( $input ) ) {
		return array();
	}

	$sanitized = array();

	// Branding.
	$sanitized['company_name']        = sanitize_text_field( $input['company_name']        ?? '' );
	$sanitized['company_slogan']      = sanitize_text_field( $input['company_slogan']      ?? '' );
	$sanitized['company_description'] = sanitize_textarea_field( $input['company_description'] ?? '' );
	$sanitized['logo_url']            = esc_url_raw( $input['logo_url']            ?? '' );
	$sanitized['footer_logo_url']     = esc_url_raw( $input['footer_logo_url']     ?? '' );
	$sanitized['favicon_url']         = esc_url_raw( $input['favicon_url']         ?? '' );
	$sanitized['brand_primary']       = jasanika_sanitize_hex_color( $input['brand_primary']   ?? '' );
	$sanitized['brand_secondary']     = jasanika_sanitize_hex_color( $input['brand_secondary'] ?? '' );
	$sanitized['brand_accent']        = jasanika_sanitize_hex_color( $input['brand_accent']    ?? '' );

	// Contact.
	$sanitized['phone']          = sanitize_text_field( $input['phone']          ?? '' );
	$sanitized['email']          = sanitize_email( $input['email']               ?? '' );
	$sanitized['address_street'] = sanitize_text_field( $input['address_street'] ?? '' );
	$sanitized['address_city']   = sanitize_text_field( $input['address_city']   ?? '' );
	$sanitized['address_zip']    = sanitize_text_field( $input['address_zip']    ?? '' );

	// Social.
	$sanitized['facebook_url']   = esc_url_raw( $input['facebook_url']   ?? '' );
	$sanitized['instagram_url']  = esc_url_raw( $input['instagram_url']  ?? '' );
	$sanitized['youtube_url']    = esc_url_raw( $input['youtube_url']    ?? '' );
	$sanitized['linkedin_url']   = esc_url_raw( $input['linkedin_url']   ?? '' );

	// Footer.
	$sanitized['copyright_text'] = sanitize_text_field( $input['copyright_text'] ?? '' );
	$sanitized['footer_note']    = sanitize_text_field( $input['footer_note']    ?? '' );

	// Homepage – Hero.
	$sanitized['hero_heading']     = sanitize_text_field( $input['hero_heading']     ?? '' );
	$sanitized['hero_description'] = sanitize_textarea_field( $input['hero_description'] ?? '' );
	$sanitized['hero_button_text'] = sanitize_text_field( $input['hero_button_text'] ?? '' );
	$sanitized['hero_button_url']  = esc_url_raw( $input['hero_button_url']  ?? '' );

	// Homepage – Feature Block 1.
	$sanitized['feature_1_title']       = sanitize_text_field( $input['feature_1_title']       ?? '' );
	$sanitized['feature_1_description'] = sanitize_textarea_field( $input['feature_1_description'] ?? '' );
	$sanitized['feature_1_button_text'] = sanitize_text_field( $input['feature_1_button_text'] ?? '' );
	$sanitized['feature_1_button_url']  = esc_url_raw( $input['feature_1_button_url']  ?? '' );

	// Homepage – Feature Block 2.
	$sanitized['feature_2_title']       = sanitize_text_field( $input['feature_2_title']       ?? '' );
	$sanitized['feature_2_description'] = sanitize_textarea_field( $input['feature_2_description'] ?? '' );
	$sanitized['feature_2_button_text'] = sanitize_text_field( $input['feature_2_button_text'] ?? '' );
	$sanitized['feature_2_button_url']  = esc_url_raw( $input['feature_2_button_url']  ?? '' );

	// Homepage – Feature Block 3.
	$sanitized['feature_3_title']       = sanitize_text_field( $input['feature_3_title']       ?? '' );
	$sanitized['feature_3_description'] = sanitize_textarea_field( $input['feature_3_description'] ?? '' );
	$sanitized['feature_3_button_text'] = sanitize_text_field( $input['feature_3_button_text'] ?? '' );
	$sanitized['feature_3_button_url']  = esc_url_raw( $input['feature_3_button_url']  ?? '' );

	// Homepage – CTA.
	$sanitized['cta_title']       = sanitize_text_field( $input['cta_title']       ?? '' );
	$sanitized['cta_description'] = sanitize_textarea_field( $input['cta_description'] ?? '' );
	$sanitized['cta_button_text'] = sanitize_text_field( $input['cta_button_text'] ?? '' );
	$sanitized['cta_button_url']  = esc_url_raw( $input['cta_button_url']  ?? '' );

	// Homepage – Categories.
	$sanitized['categories_section_title'] = sanitize_text_field( $input['categories_section_title'] ?? '' );

	// Homepage – Latest Posts.
	$sanitized['latest_posts_section_title'] = sanitize_text_field( $input['latest_posts_section_title'] ?? '' );
	$latest_posts_count                      = absint( $input['latest_posts_count'] ?? 3 );
	$sanitized['latest_posts_count']         = min( max( $latest_posts_count, 1 ), 12 );

	// Featured Products.
	$sanitized['featured_products_title']       = sanitize_text_field( $input['featured_products_title'] ?? '' );
	$sanitized['featured_products_description'] = sanitize_textarea_field( $input['featured_products_description'] ?? '' );
	$featured_products_count                    = absint( $input['featured_products_count'] ?? 4 );
	$sanitized['featured_products_count']       = min( max( $featured_products_count, 1 ), 12 );

	// Footer Builder – Columns.
	$footer_allowed = jasanika_footer_allowed_html();
	for ( $i = 1; $i <= 4; $i++ ) {
		$sanitized[ "footer_col_{$i}_title" ]   = sanitize_text_field( $input[ "footer_col_{$i}_title" ]   ?? '' );
		$sanitized[ "footer_col_{$i}_content" ] = wp_kses( $input[ "footer_col_{$i}_content" ] ?? '', $footer_allowed );
	}

	// Footer Builder – Contact Block.
	$sanitized['footer_contact_company'] = sanitize_text_field( $input['footer_contact_company']    ?? '' );
	$sanitized['footer_contact_phone']   = sanitize_text_field( $input['footer_contact_phone']      ?? '' );
	$sanitized['footer_contact_email']   = sanitize_email( $input['footer_contact_email']           ?? '' );
	$sanitized['footer_contact_address'] = sanitize_textarea_field( $input['footer_contact_address'] ?? '' );

	// Homepage Builder – section enabled / order.
	foreach ( array_keys( jasanika_homepage_sections_registry() ) as $key ) {
		$enabled_key = 'hb_' . $key . '_enabled';
		$order_key   = 'hb_' . $key . '_order';

		$sanitized[ $enabled_key ] = isset( $input[ $enabled_key ] ) ? 1 : 0;

		$order = absint( $input[ $order_key ] ?? 0 );
		$sanitized[ $order_key ] = max( 1, min( 99, $order ) );
	}

	return $sanitized;
}

// ---------------------------------------------------------------------------
// Field Renderers
// ---------------------------------------------------------------------------

/**
 * Sanitize a HEX colour value.
 * Returns an empty string if the value is not a valid 6-digit HEX colour.
 *
 * @param string $color Raw input.
 * @return string Sanitized #rrggbb string or empty string.
 */
function jasanika_sanitize_hex_color( string $color ): string {
	$color = trim( $color );

	if ( '' === $color ) {
		return '';
	}

	if ( preg_match( '/^#[0-9a-fA-F]{6}$/', $color ) ) {
		return strtolower( $color );
	}

	return '';
}

/**
 * Render a plain text input field.
 *
 * @param array $args Field arguments: key, placeholder (optional).
 */
function jasanika_settings_field_text( array $args ): void {
	$options     = get_option( 'jasanika_settings', array() );
	$value       = $options[ $args['key'] ] ?? '';
	$placeholder = $args['placeholder'] ?? '';

	printf(
		'<input type="text" id="jasanika_%1$s" name="jasanika_settings[%1$s]" value="%2$s" placeholder="%3$s" class="regular-text">',
		esc_attr( $args['key'] ),
		esc_attr( $value ),
		esc_attr( $placeholder )
	);
}

/**
 * Render an email input field.
 *
 * @param array $args Field arguments: key.
 */
function jasanika_settings_field_email( array $args ): void {
	$options = get_option( 'jasanika_settings', array() );
	$value   = $options[ $args['key'] ] ?? '';

	printf(
		'<input type="email" id="jasanika_%1$s" name="jasanika_settings[%1$s]" value="%2$s" class="regular-text">',
		esc_attr( $args['key'] ),
		esc_attr( $value )
	);
}

/**
 * Render a URL input field.
 *
 * @param array $args Field arguments: key.
 */
function jasanika_settings_field_url( array $args ): void {
	$options = get_option( 'jasanika_settings', array() );
	$value   = $options[ $args['key'] ] ?? '';

	printf(
		'<input type="url" id="jasanika_%1$s" name="jasanika_settings[%1$s]" value="%2$s" class="regular-text">',
		esc_attr( $args['key'] ),
		esc_url( $value )
	);
}

/**
 * Render a media uploader field (text input + Select Image button + Remove button + preview).
 *
 * @param array $args Field arguments: key, media_title (optional).
 */
function jasanika_settings_field_media( array $args ): void {
	$options     = get_option( 'jasanika_settings', array() );
	$value       = $options[ $args['key'] ] ?? '';
	$field_id    = 'jasanika_' . $args['key'];
	$preview_id  = 'jasanika_preview_' . $args['key'];
	$remove_id   = 'jasanika_remove_' . $args['key'];
	$media_title = $args['media_title'] ?? __( 'Select Image', 'jasanika' );
	$has_image   = ! empty( $value );
	?>
	<input
		type="text"
		id="<?php echo esc_attr( $field_id ); ?>"
		name="jasanika_settings[<?php echo esc_attr( $args['key'] ); ?>]"
		value="<?php echo esc_attr( $value ); ?>"
		class="regular-text"
	>
	<button
		type="button"
		class="button jasanika-media-upload-btn"
		data-target="<?php echo esc_attr( $field_id ); ?>"
		data-preview="<?php echo esc_attr( $preview_id ); ?>"
		data-title="<?php echo esc_attr( $media_title ); ?>"
	>
		<?php esc_html_e( 'Select Image', 'jasanika' ); ?>
	</button>
	<button
		type="button"
		id="<?php echo esc_attr( $remove_id ); ?>"
		class="button jasanika-media-remove-btn"
		data-remove="<?php echo esc_attr( $field_id ); ?>"
		data-preview="<?php echo esc_attr( $preview_id ); ?>"
		style="<?php echo $has_image ? '' : 'display:none;'; ?>"
	>
		<?php esc_html_e( 'Remove Image', 'jasanika' ); ?>
	</button>
	<br>
	<img
		id="<?php echo esc_attr( $preview_id ); ?>"
		src="<?php echo esc_url( $value ); ?>"
		style="max-width:150px;margin-top:8px;<?php echo $has_image ? '' : 'display:none;'; ?>"
		alt=""
	>
	<?php
}

// ---------------------------------------------------------------------------
// Homepage Builder Section Callback
// ---------------------------------------------------------------------------

/**
 * Renders the Homepage Builder table in the Theme Settings admin page.
 *
 * Displays a table-like interface listing each registered homepage section
 * with its Enabled checkbox and Sort Order number input. Fields are saved
 * as part of the jasanika_settings option group.
 */
function jasanika_settings_section_homepage_builder_cb(): void {
	$registry = jasanika_homepage_sections_registry();
	$settings = get_option( 'jasanika_settings', array() );
	?>
	<p class="description">
		<?php esc_html_e( 'Enable or disable each homepage section and set its display order.', 'jasanika' ); ?>
	</p>
	<table class="widefat striped jasanika-homepage-builder-table" style="margin-top:12px;">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Section Name', 'jasanika' ); ?></th>
				<th><?php esc_html_e( 'Enabled', 'jasanika' ); ?></th>
				<th><?php esc_html_e( 'Sort Order', 'jasanika' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $registry as $key => $section ) :
				$enabled_key = 'hb_' . $key . '_enabled';
				$order_key   = 'hb_' . $key . '_order';

				$enabled = isset( $settings[ $enabled_key ] )
					? (bool) $settings[ $enabled_key ]
					: $section['default_enabled'];

				$order = ( isset( $settings[ $order_key ] ) && '' !== $settings[ $order_key ] )
					? (int) $settings[ $order_key ]
					: $section['default_order'];
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
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

// ---------------------------------------------------------------------------
// Page Render
// ---------------------------------------------------------------------------

/**
 * Render a textarea field for HTML content sanitized with wp_kses().
 *
 * @param array $args Field arguments: key, description (optional).
 */
function jasanika_settings_field_kses_textarea( array $args ): void {
	$options     = get_option( 'jasanika_settings', array() );
	$value       = $options[ $args['key'] ] ?? '';
	$description = $args['description'] ?? '';

	printf(
		'<textarea id="jasanika_%1$s" name="jasanika_settings[%1$s]" class="large-text" rows="5">%2$s</textarea>',
		esc_attr( $args['key'] ),
		esc_textarea( $value )
	);

	if ( $description ) {
		echo '<p class="description">' . esc_html( $description ) . '</p>';
	}
}

/**
 * Render a textarea field.
 *
 * @param array $args Field arguments: key, placeholder (optional).
 */
function jasanika_settings_field_textarea( array $args ): void {
	$options     = get_option( 'jasanika_settings', array() );
	$value       = $options[ $args['key'] ] ?? '';
	$placeholder = $args['placeholder'] ?? '';

	printf(
		'<textarea id="jasanika_%1$s" name="jasanika_settings[%1$s]" placeholder="%3$s" class="regular-text" rows="3">%2$s</textarea>',
		esc_attr( $args['key'] ),
		esc_textarea( $value ),
		esc_attr( $placeholder )
	);
}

/**
 * Render a number input field.
 *
 * @param array $args Field arguments: key, min (optional), max (optional).
 */
function jasanika_settings_field_number( array $args ): void {
	$options = get_option( 'jasanika_settings', array() );
	$value   = isset( $options[ $args['key'] ] ) && '' !== $options[ $args['key'] ]
		? (int) $options[ $args['key'] ]
		: 3;
	$min     = isset( $args['min'] ) ? (int) $args['min'] : 1;
	$max     = isset( $args['max'] ) ? (int) $args['max'] : 12;

	printf(
		'<input type="number" id="jasanika_%1$s" name="jasanika_settings[%1$s]" value="%2$d" min="%3$d" max="%4$d" class="small-text">',
		esc_attr( $args['key'] ),
		$value,
		$min,
		$max
	);
}

/**
 * Render a colour input field (native colour picker + HEX text input pair).
 *
 * @param array $args Field arguments: key, default (optional).
 */
function jasanika_settings_field_color( array $args ): void {
	$options   = get_option( 'jasanika_settings', array() );
	$value     = $options[ $args['key'] ] ?? '';
	$default   = $args['default'] ?? '#000000';
	$field_id  = 'jasanika_' . $args['key'];
	$native_id = 'jasanika_color_native_' . $args['key'];

	// Use default as initial native picker value when field is empty.
	$native_value = ( $value && preg_match( '/^#[0-9a-fA-F]{6}$/', $value ) ) ? $value : $default;
	?>
	<div class="jasanika-color-wrap" style="display:flex;align-items:center;gap:8px;">
		<input
			type="color"
			id="<?php echo esc_attr( $native_id ); ?>"
			class="jasanika-color-native"
			value="<?php echo esc_attr( $native_value ); ?>"
			data-text-target="<?php echo esc_attr( $field_id ); ?>"
		>
		<input
			type="text"
			id="<?php echo esc_attr( $field_id ); ?>"
			name="jasanika_settings[<?php echo esc_attr( $args['key'] ); ?>]"
			value="<?php echo esc_attr( $value ); ?>"
			placeholder="<?php echo esc_attr( $default ); ?>"
			class="regular-text jasanika-color-text"
			pattern="^#[0-9a-fA-F]{6}$"
			maxlength="7"
			style="font-family:monospace;"
		>
	</div>
	<p class="description"><?php esc_html_e( 'Enter a HEX colour, e.g. #c89af5', 'jasanika' ); ?></p>
	<?php
}

/**
 * Renders the Theme Settings admin page.
 */
function jasanika_admin_page_theme_settings(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Jasanika – Theme Settings', 'jasanika' ); ?></h1>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'jasanika_settings_group' );
			do_settings_sections( 'jasanika-theme-settings' );
			submit_button( __( 'Save Settings', 'jasanika' ) );
			?>
		</form>
	</div>
	<?php
}
