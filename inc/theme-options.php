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

function jasanika_get_company_description(): string {
	return (string) jasanika_get_option( 'company_description', '' );
}

function jasanika_get_logo_url(): string {
	return (string) jasanika_get_option( 'logo_url', '' );
}

function jasanika_get_footer_logo_url(): string {
	return (string) jasanika_get_option( 'footer_logo_url', '' );
}

function jasanika_get_favicon_url(): string {
	return (string) jasanika_get_option( 'favicon_url', '' );
}

/**
 * Return the header logo as an HTML img element, or the company name as a span fallback.
 *
 * The returned string is already properly escaped.
 */
function jasanika_get_logo(): string {
	$url = jasanika_get_logo_url();

	if ( $url ) {
		return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( jasanika_get_company_name() ) . '" class="site-branding__logo">';
	}

	return '<span class="site-branding__name">' . esc_html( jasanika_get_company_name() ) . '</span>';
}

/**
 * Return the footer logo as an HTML img element, or an empty string when none is configured.
 *
 * The returned string is already properly escaped.
 */
function jasanika_get_footer_logo(): string {
	$url = jasanika_get_footer_logo_url();

	if ( ! $url ) {
		return '';
	}

	return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( jasanika_get_company_name() ) . '" class="footer-branding__logo">';
}

/**
 * Return a validated brand colour HEX value.
 *
 * @param string $key     Colour key: primary | secondary | accent.
 * @param string $default Fallback HEX value when the option is empty or invalid.
 * @return string Validated #rrggbb value, or $default.
 */
function jasanika_get_brand_color( string $key, string $default = '' ): string {
	$color = (string) jasanika_get_option( 'brand_' . $key, $default );

	if ( $color && preg_match( '/^#[0-9a-fA-F]{6}$/', $color ) ) {
		return $color;
	}

	return $default;
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

// ---------------------------------------------------------------------------
// Newsletter
// ---------------------------------------------------------------------------

function jasanika_get_newsletter_title(): string {
	return (string) jasanika_get_option( 'newsletter_title', __( 'Newsletter', 'jasanika' ) );
}

function jasanika_get_newsletter_description(): string {
	return (string) jasanika_get_option( 'newsletter_description', __( 'Subscribe to receive updates and special offers.', 'jasanika' ) );
}

function jasanika_get_newsletter_success(): string {
	return (string) jasanika_get_option( 'newsletter_success', __( 'Thank you for subscribing.', 'jasanika' ) );
}

function jasanika_get_newsletter_privacy_text(): string {
	return (string) jasanika_get_option( 'newsletter_privacy_text', __( 'I agree to the Privacy Policy.', 'jasanika' ) );
}

// ---------------------------------------------------------------------------
// Footer Builder
// ---------------------------------------------------------------------------

/**
 * Return the allowed HTML tags for footer column content (wp_kses).
 *
 * @return array<string, array<string, bool>>
 */
function jasanika_footer_allowed_html(): array {
	return array(
		'a'      => array(
			'href'   => true,
			'title'  => true,
			'target' => true,
			'rel'    => true,
		),
		'strong' => array(),
		'em'     => array(),
		'br'     => array(),
		'p'      => array( 'class' => true ),
		'span'   => array( 'class' => true ),
		'ul'     => array( 'class' => true ),
		'ol'     => array( 'class' => true ),
		'li'     => array( 'class' => true ),
	);
}

/**
 * Get footer column data by column number.
 *
 * @param int $n Column number 1–4.
 * @return array{ title: string, content: string }
 */
function jasanika_get_footer_column( int $n ): array {
	$n = max( 1, min( 4, $n ) );

	return array(
		'title'   => (string) jasanika_get_option( "footer_col_{$n}_title",   '' ),
		'content' => (string) jasanika_get_option( "footer_col_{$n}_content", '' ),
	);
}

/**
 * Get footer contact block data.
 *
 * Falls back to main Theme Settings contact fields when dedicated
 * footer contact fields are empty.
 *
 * @return array{ company: string, phone: string, email: string, address: string }
 */
function jasanika_get_footer_contact(): array {
	$company = (string) jasanika_get_option( 'footer_contact_company', '' );
	if ( ! $company ) {
		$company = jasanika_get_company_name();
	}

	$phone = (string) jasanika_get_option( 'footer_contact_phone', '' );
	if ( ! $phone ) {
		$phone = jasanika_get_phone();
	}

	$email = (string) jasanika_get_option( 'footer_contact_email', '' );
	if ( ! $email ) {
		$email = jasanika_get_email();
	}

	$address = (string) jasanika_get_option( 'footer_contact_address', '' );
	if ( ! $address ) {
		$street = jasanika_get_address_street();
		$city   = jasanika_get_address_city();
		$zip    = jasanika_get_address_zip();
		$parts  = array_filter( array( $street, trim( $zip . ' ' . $city ) ) );
		if ( $parts ) {
			$address = implode( ', ', $parts );
		}
	}

	return array(
		'company' => $company,
		'phone'   => $phone,
		'email'   => $email,
		'address' => $address,
	);
}
