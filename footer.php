<?php

/**
 * Footer
 *
 * Theme footer template.
 */

?>

<footer id="site-footer" class="site-footer">
	<div class="container">

		<div class="site-footer__widgets">
			<div class="site-footer__widgets-grid">

				<div class="footer-widget">
					<h3 class="footer-widget__title"><?php bloginfo( 'name' ); ?></h3>
					<p class="footer-widget__text">
						<?php bloginfo( 'description' ); ?>
					</p>
				</div>

				<div class="footer-widget">
					<h3 class="footer-widget__title"><?php esc_html_e( 'Navigation', 'jasanika' ); ?></h3>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'menu_class'     => 'footer-nav__list',
							'container'      => false,
							'fallback_cb'    => false,
						)
					);
					?>
				</div>

				<div class="footer-widget">
					<h3 class="footer-widget__title"><?php esc_html_e( 'Contact', 'jasanika' ); ?></h3>
					<?php /* Contact info will be implemented in a future milestone. */ ?>
				</div>

			</div>
		</div>

		<div class="site-footer__info">
			<span class="site-footer__copyright">
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
			</span>
			<span class="site-footer__credits">
				<?php esc_html_e( 'Handmade with love', 'jasanika' ); ?>
			</span>
		</div>

	</div>
</footer>

<?php wp_footer(); ?>

</body>
</html>
