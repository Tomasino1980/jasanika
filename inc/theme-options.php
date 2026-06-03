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
 * Respects the logo_show_footer visibility setting.
 *
 * The returned string is already properly escaped.
 */
function jasanika_get_footer_logo(): string {
	if ( ! jasanika_logo_is_shown_in( 'footer' ) ) {
		return '';
	}

	$url = jasanika_get_footer_logo_url();

	if ( ! $url ) {
		return '';
	}

	return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( jasanika_get_company_name() ) . '" class="footer-branding__logo">';
}

// ---------------------------------------------------------------------------
// Logo Placement – M55
// ---------------------------------------------------------------------------

/**
 * Check whether the logo is configured to be shown in a given location.
 *
 * Locations: 'header' | 'footer' | 'hero' | 'mobile'
 * Defaults (when the setting has never been saved): header=true, footer=true, hero=false, mobile=true.
 *
 * @param string $location Location identifier.
 * @return bool
 */
function jasanika_logo_is_shown_in( string $location ): bool {
	$options  = get_option( 'jasanika_settings', array() );
	$defaults = array(
		'header' => true,
		'footer' => true,
		'hero'   => false,
		'mobile' => true,
	);

	$key = 'logo_show_' . $location;

	if ( ! array_key_exists( $key, $options ) ) {
		return $defaults[ $location ] ?? true;
	}

	return (bool) $options[ $key ];
}

/**
 * Return the configured header logo position.
 *
 * @return string 'left' | 'center' | 'right'
 */
function jasanika_logo_get_header_position(): string {
	$valid = array( 'left', 'center', 'right' );
	$val   = (string) jasanika_get_option( 'logo_header_pos', 'left' );
	return in_array( $val, $valid, true ) ? $val : 'left';
}

/**
 * Return the configured footer logo position.
 *
 * @return string 'left' | 'center' | 'right'
 */
function jasanika_logo_get_footer_position(): string {
	$valid = array( 'left', 'center', 'right' );
	$val   = (string) jasanika_get_option( 'logo_footer_pos', 'left' );
	return in_array( $val, $valid, true ) ? $val : 'left';
}

/**
 * Return the configured hero logo position.
 *
 * @return string One of: top_left | top_center | top_right | center | bottom_left | bottom_center | bottom_right
 */
function jasanika_logo_get_hero_position(): string {
	$valid = array( 'top_left', 'top_center', 'top_right', 'center', 'bottom_left', 'bottom_center', 'bottom_right' );
	$val   = (string) jasanika_get_option( 'logo_hero_pos', 'center' );
	return in_array( $val, $valid, true ) ? $val : 'center';
}

/**
 * Return the general logo width in pixels (used for --js-logo-width CSS variable).
 *
 * @return int
 */
function jasanika_logo_get_width(): int {
	$val = (int) jasanika_get_option( 'logo_width', 200 );
	return min( 600, max( 50, $val ) );
}

/**
 * Return the desktop logo width in pixels (used for --js-logo-desktop-width CSS variable).
 *
 * @return int
 */
function jasanika_logo_get_desktop_width(): int {
	$val = (int) jasanika_get_option( 'logo_desktop_width', 200 );
	return min( 600, max( 50, $val ) );
}

/**
 * Return the tablet logo width in pixels (used for --js-logo-tablet-width CSS variable).
 *
 * @return int
 */
function jasanika_logo_get_tablet_width(): int {
	$val = (int) jasanika_get_option( 'logo_tablet_width', 160 );
	return min( 600, max( 50, $val ) );
}

/**
 * Return the mobile logo width in pixels (used for --js-logo-mobile-width CSS variable).
 *
 * @return int
 */
function jasanika_logo_get_mobile_width(): int {
	$val = (int) jasanika_get_option( 'logo_mobile_width', 120 );
	return min( 600, max( 50, $val ) );
}

/**
 * Return the logo height mode.
 *
 * @return string 'auto' | 'custom'
 */
function jasanika_logo_get_height_mode(): string {
	$val = (string) jasanika_get_option( 'logo_height_mode', 'auto' );
	return in_array( $val, array( 'auto', 'custom' ), true ) ? $val : 'auto';
}

/**
 * Return the custom logo height in pixels (relevant only when height mode = 'custom').
 *
 * @return int
 */
function jasanika_logo_get_height(): int {
	$val = (int) jasanika_get_option( 'logo_height', 100 );
	return min( 600, max( 50, $val ) );
}

/**
 * Return a single logo margin value in pixels.
 *
 * @param string $side 'top' | 'right' | 'bottom' | 'left'
 * @return int
 */
function jasanika_logo_get_margin( string $side ): int {
	$sides = array( 'top', 'right', 'bottom', 'left' );
	if ( ! in_array( $side, $sides, true ) ) {
		return 0;
	}
	$val = (int) jasanika_get_option( 'logo_margin_' . $side, 0 );
	return min( 200, max( 0, $val ) );
}

/**
 * Generate inline CSS for logo placement CSS custom properties.
 *
 * Returns a full :root { … } block ready to be injected via wp_add_inline_style().
 * Only outputs when at least one non-default value is configured.
 *
 * @return string CSS string or empty string.
 */
function jasanika_logo_get_css_variables(): string {
	$opts = get_option( 'jasanika_settings', array() );

	// Per-location width with fallback chain to legacy unified keys.
	$header_width = (int) ( $opts['logo_header_width'] ?? $opts['logo_desktop_width'] ?? $opts['logo_width'] ?? 200 );
	$footer_width = (int) ( $opts['logo_footer_width'] ?? $opts['logo_desktop_width'] ?? $opts['logo_width'] ?? 200 );
	$hero_width   = (int) ( $opts['logo_hero_width']   ?? $opts['logo_desktop_width'] ?? $opts['logo_width'] ?? 200 );
	$mobile_width = (int) ( $opts['logo_mobile_width'] ?? 120 );

	// Per-location height.
	$header_hmode = (string) ( $opts['logo_header_height_mode'] ?? $opts['logo_height_mode'] ?? 'auto' );
	$footer_hmode = (string) ( $opts['logo_footer_height_mode'] ?? $opts['logo_height_mode'] ?? 'auto' );
	$hero_hmode   = (string) ( $opts['logo_hero_height_mode']   ?? $opts['logo_height_mode'] ?? 'auto' );
	$mobile_hmode = (string) ( $opts['logo_mobile_height_mode'] ?? $opts['logo_height_mode'] ?? 'auto' );

	$header_height = 'custom' === $header_hmode ? ( (int) ( $opts['logo_header_height'] ?? $opts['logo_height'] ?? 100 ) ) . 'px' : 'auto';
	$footer_height = 'custom' === $footer_hmode ? ( (int) ( $opts['logo_footer_height'] ?? $opts['logo_height'] ?? 100 ) ) . 'px' : 'auto';
	$hero_height   = 'custom' === $hero_hmode   ? ( (int) ( $opts['logo_hero_height']   ?? $opts['logo_height'] ?? 100 ) ) . 'px' : 'auto';
	$mobile_height = 'custom' === $mobile_hmode ? ( (int) ( $opts['logo_mobile_height'] ?? $opts['logo_height'] ?? 100 ) ) . 'px' : 'auto';

	// Per-location margins with fallback to legacy unified margins.
	$header_mt = (int) ( $opts['logo_header_margin_top']    ?? $opts['logo_margin_top']    ?? 0 );
	$header_mr = (int) ( $opts['logo_header_margin_right']  ?? $opts['logo_margin_right']  ?? 0 );
	$header_mb = (int) ( $opts['logo_header_margin_bottom'] ?? $opts['logo_margin_bottom'] ?? 0 );
	$header_ml = (int) ( $opts['logo_header_margin_left']   ?? $opts['logo_margin_left']   ?? 0 );

	$footer_mt = (int) ( $opts['logo_footer_margin_top']    ?? 0 );
	$footer_mr = (int) ( $opts['logo_footer_margin_right']  ?? 0 );
	$footer_mb = (int) ( $opts['logo_footer_margin_bottom'] ?? 0 );
	$footer_ml = (int) ( $opts['logo_footer_margin_left']   ?? 0 );

	$hero_mt = (int) ( $opts['logo_hero_margin_top']    ?? 0 );
	$hero_mr = (int) ( $opts['logo_hero_margin_right']  ?? 0 );
	$hero_mb = (int) ( $opts['logo_hero_margin_bottom'] ?? 0 );
	$hero_ml = (int) ( $opts['logo_hero_margin_left']   ?? 0 );

	$mobile_mt = (int) ( $opts['logo_mobile_margin_top']    ?? 0 );
	$mobile_mr = (int) ( $opts['logo_mobile_margin_right']  ?? 0 );
	$mobile_mb = (int) ( $opts['logo_mobile_margin_bottom'] ?? 0 );
	$mobile_ml = (int) ( $opts['logo_mobile_margin_left']   ?? 0 );

	// Legacy unified variables kept for backward-compat with existing front-end CSS.
	$width         = jasanika_logo_get_width();
	$desktop_width = jasanika_logo_get_desktop_width();
	$tablet_width  = jasanika_logo_get_tablet_width();
	$mobile_bkp    = jasanika_logo_get_mobile_width();
	$height_mode   = jasanika_logo_get_height_mode();
	$height        = 'custom' === $height_mode ? jasanika_logo_get_height() . 'px' : 'auto';
	$margin_top    = jasanika_logo_get_margin( 'top' );
	$margin_right  = jasanika_logo_get_margin( 'right' );
	$margin_bottom = jasanika_logo_get_margin( 'bottom' );
	$margin_left   = jasanika_logo_get_margin( 'left' );

	$vars  = ":root{\n";

	// Per-location CSS variables.
	$vars .= "\t--js-logo-header-width:{$header_width}px;\n";
	$vars .= "\t--js-logo-header-height:{$header_height};\n";
	$vars .= "\t--js-logo-header-margin:{$header_mt}px {$header_mr}px {$header_mb}px {$header_ml}px;\n";
	$vars .= "\t--js-logo-footer-width:{$footer_width}px;\n";
	$vars .= "\t--js-logo-footer-height:{$footer_height};\n";
	$vars .= "\t--js-logo-footer-margin:{$footer_mt}px {$footer_mr}px {$footer_mb}px {$footer_ml}px;\n";
	$vars .= "\t--js-logo-hero-width:{$hero_width}px;\n";
	$vars .= "\t--js-logo-hero-height:{$hero_height};\n";
	$vars .= "\t--js-logo-hero-margin:{$hero_mt}px {$hero_mr}px {$hero_mb}px {$hero_ml}px;\n";
	$vars .= "\t--js-logo-mobile-width:{$mobile_width}px;\n";
	$vars .= "\t--js-logo-mobile-height:{$mobile_height};\n";
	$vars .= "\t--js-logo-mobile-margin:{$mobile_mt}px {$mobile_mr}px {$mobile_mb}px {$mobile_ml}px;\n";

	// Legacy variables (backward-compat).
	$vars .= "\t--js-logo-width:{$width}px;\n";
	$vars .= "\t--js-logo-desktop-width:{$desktop_width}px;\n";
	$vars .= "\t--js-logo-tablet-width:{$tablet_width}px;\n";
	$vars .= "\t--js-logo-mobile-width:{$mobile_bkp}px;\n";
	$vars .= "\t--js-logo-height:{$height};\n";
	$vars .= "\t--js-logo-margin-top:{$margin_top}px;\n";
	$vars .= "\t--js-logo-margin-right:{$margin_right}px;\n";
	$vars .= "\t--js-logo-margin-bottom:{$margin_bottom}px;\n";
	$vars .= "\t--js-logo-margin-left:{$margin_left}px;\n";
	$vars .= "}";

	return $vars;
}

/**
 * Return the hero logo HTML or empty string when not configured to show in the hero.
 *
 * The returned string is already properly escaped.
 */
function jasanika_get_hero_logo(): string {
	if ( ! jasanika_logo_is_shown_in( 'hero' ) ) {
		return '';
	}

	// Use dedicated hero logo URL if set, fall back to header logo.
	$url = (string) jasanika_get_option( 'logo_hero_url', '' );
	if ( ! $url ) {
		$url = jasanika_get_logo_url();
	}

	$pos      = jasanika_logo_get_hero_position();
	$pos_class = 'hero-logo--' . esc_attr( $pos );

	ob_start();
	?>
	<div class="hero-logo <?php echo esc_attr( $pos_class ); ?>" aria-hidden="true">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( $url ) : ?>
				<img
					src="<?php echo esc_url( $url ); ?>"
					alt="<?php echo esc_attr( jasanika_get_company_name() ); ?>"
					class="hero-logo__img"
				>
			<?php else : ?>
				<span class="hero-logo__name"><?php echo esc_html( jasanika_get_company_name() ); ?></span>
			<?php endif; ?>
		</a>
	</div>
	<?php
	return (string) ob_get_clean();
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
