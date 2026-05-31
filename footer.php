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

					<div class="footer-widget footer-widget--brand">
					<?php $footer_logo = jasanika_get_footer_logo(); ?>
					<?php if ( $footer_logo ) : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-branding__link">
							<?php echo $footer_logo; // Output is escaped within the helper. ?>
						</a>
					<?php endif; ?>
					<h3 class="footer-widget__title"><?php echo esc_html( jasanika_get_company_name() ); ?></h3>
					<?php $company_description = jasanika_get_company_description(); ?>
					<?php if ( $company_description ) : ?>
						<p class="footer-widget__text">
							<?php echo esc_html( $company_description ); ?>
						</p>
					<?php else : ?>
						<p class="footer-widget__text">
							<?php echo esc_html( jasanika_get_company_slogan() ); ?>
						</p>
					<?php endif; ?>
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
					<ul class="footer-contact__list">

						<?php $phone = jasanika_get_phone(); ?>
						<?php if ( $phone ) : ?>
							<li class="footer-contact__item">
								<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>">
									<?php echo esc_html( $phone ); ?>
								</a>
							</li>
						<?php endif; ?>

						<?php $email = jasanika_get_email(); ?>
						<?php if ( $email ) : ?>
							<li class="footer-contact__item">
								<a href="mailto:<?php echo esc_attr( $email ); ?>">
									<?php echo esc_html( $email ); ?>
								</a>
							</li>
						<?php endif; ?>

						<?php
						$street = jasanika_get_address_street();
						$city   = jasanika_get_address_city();
						$zip    = jasanika_get_address_zip();
						if ( $street || $city || $zip ) :
						?>
							<li class="footer-contact__item">
								<?php if ( $street ) : ?>
									<?php echo esc_html( $street ); ?><br>
								<?php endif; ?>
								<?php if ( $zip || $city ) : ?>
									<?php echo esc_html( trim( $zip . ' ' . $city ) ); ?>
								<?php endif; ?>
							</li>
						<?php endif; ?>

					</ul>

					<?php
					$facebook  = jasanika_get_facebook_url();
					$instagram = jasanika_get_instagram_url();
					$youtube   = jasanika_get_youtube_url();
					$linkedin  = jasanika_get_linkedin_url();
					if ( $facebook || $instagram || $youtube || $linkedin ) :
					?>
						<ul class="footer-social__list">
							<?php if ( $facebook ) : ?>
								<li class="footer-social__item">
									<a href="<?php echo esc_url( $facebook ); ?>" class="footer-social__link" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'Facebook', 'jasanika' ); ?>
									</a>
								</li>
							<?php endif; ?>
							<?php if ( $instagram ) : ?>
								<li class="footer-social__item">
									<a href="<?php echo esc_url( $instagram ); ?>" class="footer-social__link" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'Instagram', 'jasanika' ); ?>
									</a>
								</li>
							<?php endif; ?>
							<?php if ( $youtube ) : ?>
								<li class="footer-social__item">
									<a href="<?php echo esc_url( $youtube ); ?>" class="footer-social__link" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'YouTube', 'jasanika' ); ?>
									</a>
								</li>
							<?php endif; ?>
							<?php if ( $linkedin ) : ?>
								<li class="footer-social__item">
									<a href="<?php echo esc_url( $linkedin ); ?>" class="footer-social__link" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'LinkedIn', 'jasanika' ); ?>
									</a>
								</li>
							<?php endif; ?>
						</ul>
					<?php endif; ?>

				</div>

			</div>
		</div>

		<div class="site-footer__info">
			<span class="site-footer__copyright">
				<?php echo esc_html( jasanika_get_copyright_text() ); ?>
			</span>
			<?php $footer_note = jasanika_get_footer_note(); ?>
			<?php if ( $footer_note ) : ?>
				<span class="site-footer__credits">
					<?php echo esc_html( $footer_note ); ?>
				</span>
			<?php endif; ?>
		</div>

	</div>
</footer>

<?php wp_footer(); ?>

</body>
</html>
