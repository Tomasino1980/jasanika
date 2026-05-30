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

<header id="site-header" class="site-header">
	<div class="container">
		<div class="site-header__inner">

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-branding">
				<span class="site-branding__name"><?php bloginfo( 'name' ); ?></span>
				<?php if ( get_bloginfo( 'description' ) ) : ?>
					<span class="site-branding__tagline"><?php bloginfo( 'description' ); ?></span>
				<?php endif; ?>
			</a>

			<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'jasanika' ); ?>">
				<?php /* Navigation will be implemented in M4 - Menu System. */ ?>
			</nav>

		</div>
	</div>
</header>

<div class="site-header-offset"></div>
