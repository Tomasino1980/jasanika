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

<header id="site-header" class="site-header">
	<div class="container">
		<div class="site-header__inner">

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-branding">

			<?php echo jasanika_get_logo(); // Output is escaped within the helper. ?>

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
