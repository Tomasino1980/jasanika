<?php

/**
 * Jasanika – Login Branding
 *
 * Customises the WordPress login page with the site logo, company name and
 * brand colours configured in Jasanika → Theme Settings → Branding.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Login Header URL / Title
// ---------------------------------------------------------------------------

/**
 * Point the login logo link to the site home instead of wordpress.org.
 */
add_filter( 'login_headerurl', 'jasanika_login_header_url' );

function jasanika_login_header_url(): string {
	return home_url( '/' );
}

/**
 * Replace the default logo link title with the company name.
 */
add_filter( 'login_headertext', 'jasanika_login_header_text' );

function jasanika_login_header_text(): string {
	return jasanika_get_company_name();
}

// ---------------------------------------------------------------------------
// Login Styles
// ---------------------------------------------------------------------------

/**
 * Inject custom brand styles into the login page.
 */
add_action( 'login_enqueue_scripts', 'jasanika_login_enqueue_styles' );

function jasanika_login_enqueue_styles(): void {
	$logo_url  = jasanika_get_logo_url();
	$primary   = jasanika_get_brand_color( 'primary',   '#b78acb' );
	$secondary = jasanika_get_brand_color( 'secondary', '#24212b' );
	$accent    = jasanika_get_brand_color( 'accent',    '#f1c95d' );

	$css  = ':root{';
	$css .= '--brand-primary:' . $primary . ';';
	$css .= '--brand-secondary:' . $secondary . ';';
	$css .= '--brand-accent:' . $accent . ';';
	$css .= '}';

	$css .= 'body.login{background-color:#1b1a1f;color:#f5f2f7;}';

	$css .= 'body.login #login h1 a{';
	$css .= 'background-color:transparent;';
	$css .= 'background-repeat:no-repeat;';
	$css .= 'background-position:center;';
	$css .= 'background-size:contain;';
	$css .= 'width:100%;';
	$css .= 'height:80px;';
	if ( $logo_url ) {
		$css .= 'background-image:url(' . esc_url( $logo_url ) . ');';
	}
	$css .= '}';

	$css .= '#loginform,#lostpasswordform,#registerform{';
	$css .= 'background:' . $secondary . ';';
	$css .= 'border:1px solid rgba(255,255,255,0.08);';
	$css .= 'box-shadow:0 8px 32px rgba(0,0,0,0.6);';
	$css .= '}';

	$css .= '#loginform label,#lostpasswordform label{color:#f5f2f7;}';

	$css .= '.button-primary,input[type="submit"].button-primary{';
	$css .= 'background:' . $primary . ' !important;';
	$css .= 'border-color:' . $primary . ' !important;';
	$css .= 'color:#1b1a1f !important;';
	$css .= '}';

	$css .= '.button-primary:hover,input[type="submit"].button-primary:hover{';
	$css .= 'background:' . $accent . ' !important;';
	$css .= 'border-color:' . $accent . ' !important;';
	$css .= '}';

	$css .= '#backtoblog a,#nav a{color:' . $primary . ';}';
	$css .= '#backtoblog a:hover,#nav a:hover{color:' . $accent . ';}';

	wp_register_style( 'jasanika-login', false, array(), '0.34.0' );
	wp_enqueue_style( 'jasanika-login' );
	wp_add_inline_style( 'jasanika-login', $css );
}
