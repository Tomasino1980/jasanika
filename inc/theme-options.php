<?php

/**
 * Jasanika – Theme Options
 *
 * Helper functions for accessing theme settings stored via the Settings API.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a single theme option value.
 *
 * @param string $key     Option key within the jasanika_settings array.
 * @param mixed  $default Default value when the option is not set or empty.
 * @return mixed
 */
function jasanika_get_option( string $key, mixed $default = '' ): mixed {
	$options = get_option( 'jasanika_settings', array() );

	if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
		return $options[ $key ];
	}

	return $default;
}

// ---------------------------------------------------------------------------
// Branding
// ---------------------------------------------------------------------------

function jasanika_get_company_name(): string {
	return (string) jasanika_get_option( 'company_name', get_bloginfo( 'name' ) );
}

function jasanika_get_company_slogan(): string {
	return (string) jasanika_get_option( 'company_slogan', get_bloginfo( 'description' ) );
}

function jasanika_get_logo_url(): string {
	return (string) jasanika_get_option( 'logo_url', '' );
}

function jasanika_get_favicon_url(): string {
	return (string) jasanika_get_option( 'favicon_url', '' );
}

// ---------------------------------------------------------------------------
// Contact Information
// ---------------------------------------------------------------------------

function jasanika_get_phone(): string {
	return (string) jasanika_get_option( 'phone', '' );
}

function jasanika_get_email(): string {
	return (string) jasanika_get_option( 'email', '' );
}

function jasanika_get_address_street(): string {
	return (string) jasanika_get_option( 'address_street', '' );
}

function jasanika_get_address_city(): string {
	return (string) jasanika_get_option( 'address_city', '' );
}

function jasanika_get_address_zip(): string {
	return (string) jasanika_get_option( 'address_zip', '' );
}

// ---------------------------------------------------------------------------
// Social Networks
// ---------------------------------------------------------------------------

function jasanika_get_facebook_url(): string {
	return (string) jasanika_get_option( 'facebook_url', '' );
}

function jasanika_get_instagram_url(): string {
	return (string) jasanika_get_option( 'instagram_url', '' );
}

function jasanika_get_youtube_url(): string {
	return (string) jasanika_get_option( 'youtube_url', '' );
}

function jasanika_get_linkedin_url(): string {
	return (string) jasanika_get_option( 'linkedin_url', '' );
}

// ---------------------------------------------------------------------------
// Footer
// ---------------------------------------------------------------------------

function jasanika_get_copyright_text(): string {
	$default = sprintf( '© %s %s', gmdate( 'Y' ), get_bloginfo( 'name' ) );
	return (string) jasanika_get_option( 'copyright_text', $default );
}

function jasanika_get_footer_note(): string {
	return (string) jasanika_get_option( 'footer_note', '' );
}

// ---------------------------------------------------------------------------
// Homepage – Hero
// ---------------------------------------------------------------------------

function jasanika_get_hero_heading(): string {
	return (string) jasanika_get_option( 'hero_heading', __( 'Vítejte na Jasanika', 'jasanika' ) );
}

function jasanika_get_hero_description(): string {
	return (string) jasanika_get_option( 'hero_description', __( 'Ručně tvořený WordPress obchod a blog.', 'jasanika' ) );
}

function jasanika_get_hero_button_text(): string {
	return (string) jasanika_get_option( 'hero_button_text', __( 'Zjistit více', 'jasanika' ) );
}

function jasanika_get_hero_button_url(): string {
	$default = get_permalink( get_option( 'page_for_posts' ) );
	$default = $default ?: home_url( '/' );
	return (string) jasanika_get_option( 'hero_button_url', $default );
}

// ---------------------------------------------------------------------------
// Homepage – Feature Blocks
// ---------------------------------------------------------------------------

function jasanika_get_feature_block( int $n ): array {
	$defaults = array(
		1 => array(
			'title'       => __( 'Handmade Products', 'jasanika' ),
			'description' => __( 'Discover custom handmade creations.', 'jasanika' ),
			'button_text' => __( 'Explore', 'jasanika' ),
			'button_url'  => '#',
		),
		2 => array(
			'title'       => __( 'Blog Articles', 'jasanika' ),
			'description' => __( 'Read tutorials and project stories.', 'jasanika' ),
			'button_text' => __( 'Read More', 'jasanika' ),
			'button_url'  => '#',
		),
		3 => array(
			'title'       => __( 'Custom Orders', 'jasanika' ),
			'description' => __( 'Request personalized products.', 'jasanika' ),
			'button_text' => __( 'Get in Touch', 'jasanika' ),
			'button_url'  => '#',
		),
	);

	$default = $defaults[ $n ] ?? $defaults[1];

	return array(
		'title'       => (string) jasanika_get_option( "feature_{$n}_title",       $default['title'] ),
		'description' => (string) jasanika_get_option( "feature_{$n}_description", $default['description'] ),
		'button_text' => (string) jasanika_get_option( "feature_{$n}_button_text", $default['button_text'] ),
		'button_url'  => (string) jasanika_get_option( "feature_{$n}_button_url",  $default['button_url'] ),
	);
}

// ---------------------------------------------------------------------------
// Homepage – CTA
// ---------------------------------------------------------------------------

function jasanika_get_cta_title(): string {
	return (string) jasanika_get_option( 'cta_title', __( 'Máte vlastní nápad?', 'jasanika' ) );
}

function jasanika_get_cta_description(): string {
	return (string) jasanika_get_option( 'cta_description', __( 'Vyrábíme zakázkové výrobky podle vašich představ. Kontaktujte nás a společně najdeme řešení.', 'jasanika' ) );
}

function jasanika_get_cta_button_text(): string {
	return (string) jasanika_get_option( 'cta_button_text', __( 'Kontaktujte nás', 'jasanika' ) );
}

function jasanika_get_cta_button_url(): string {
	return (string) jasanika_get_option( 'cta_button_url', home_url( '/kontakt' ) );
}

// ---------------------------------------------------------------------------
// Homepage – Categories
// ---------------------------------------------------------------------------

function jasanika_get_categories_section_title(): string {
	return (string) jasanika_get_option( 'categories_section_title', __( 'Procházet kategorie', 'jasanika' ) );
}

// ---------------------------------------------------------------------------
// Homepage – Latest Posts
// ---------------------------------------------------------------------------

function jasanika_get_latest_posts_section_title(): string {
	return (string) jasanika_get_option( 'latest_posts_section_title', __( 'Nejnovější články', 'jasanika' ) );
}

function jasanika_get_latest_posts_count(): int {
	$count = (int) jasanika_get_option( 'latest_posts_count', 3 );
	return min( max( $count, 1 ), 12 );
}
