<?php

/**
 * Header
 *
 * Theme header template.
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-to-content" href="#main"><?php esc_html_e( 'Skip to content', 'jasanika' ); ?></a>

<?php
$logo_header_pos   = function_exists( 'jasanika_logo_get_header_position' ) ? jasanika_logo_get_header_position() : 'left';
$show_logo_header  = ! function_exists( 'jasanika_logo_is_shown_in' ) || jasanika_logo_is_shown_in( 'header' );
$show_logo_mobile  = ! function_exists( 'jasanika_logo_is_shown_in' ) || jasanika_logo_is_shown_in( 'mobile' );

$header_classes = array( 'site-header' );
if ( 'left' !== $logo_header_pos ) {
	$header_classes[] = 'site-header--logo-' . $logo_header_pos;
}
if ( ! $show_logo_mobile ) {
	$header_classes[] = 'site-header--hide-mobile-logo';
}
?>

<header id="site-header" class="<?php echo esc_attr( implode( ' ', $header_classes ) ); ?>">
	<div class="container">
		<div class="site-header__inner">

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-branding">

			<?php if ( $show_logo_header ) : ?>
				<?php echo jasanika_get_logo(); // Output is escaped within the helper. ?>
			<?php endif; ?>

			<?php $slogan = jasanika_get_company_slogan(); ?>
			<?php if ( $slogan ) : ?>
				<span class="site-branding__tagline"><?php echo esc_html( $slogan ); ?></span>
			<?php endif; ?>

		</a>

			<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'jasanika' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_class'     => 'primary-nav__list',
						'container'      => false,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>

		</div>
	</div>
</header>

<div class="site-header-offset"></div>
