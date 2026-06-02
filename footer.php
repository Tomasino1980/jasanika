<?php

/**
 * Footer
 *
 * Theme footer template – Footer Builder.
 */

?>

<footer id="site-footer" class="site-footer">
	<div class="container">

		<?php // Widget Area – displayed only when active. ?>
		<?php if ( is_active_sidebar( 'footer-widgets' ) ) : ?>
			<div class="footer-builder__widgets">
				<?php dynamic_sidebar( 'footer-widgets' ); ?>
			</div>
		<?php endif; ?>

		<?php
		// Collect non-empty footer columns.
		$footer_columns = array();
		for ( $i = 1; $i <= 4; $i++ ) {
			$col = jasanika_get_footer_column( $i );
			if ( $col['title'] || $col['content'] ) {
				$footer_columns[ $i ] = $col;
			}
		}
		?>

		<?php if ( ! empty( $footer_columns ) ) : ?>
			<div class="footer-builder__columns">
				<?php foreach ( $footer_columns as $col ) : ?>
					<div class="footer-builder__column">
						<?php if ( $col['title'] ) : ?>
							<h3 class="footer-builder__column-title"><?php echo esc_html( $col['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( $col['content'] ) : ?>
							<div class="footer-builder__column-content">
								<?php echo wp_kses( $col['content'], jasanika_footer_allowed_html() ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php
		// Prepare contact, social and menu data.
		$contact   = jasanika_get_footer_contact();
		$facebook  = jasanika_get_facebook_url();
		$instagram = jasanika_get_instagram_url();
		$youtube   = jasanika_get_youtube_url();
		$linkedin  = jasanika_get_linkedin_url();

		$has_contact = $contact['phone'] || $contact['email'] || $contact['address'];
		$has_social  = $facebook || $instagram || $youtube || $linkedin;
		$has_menu    = has_nav_menu( 'footer' );
		?>

		<?php if ( $has_contact || $has_social || $has_menu ) : ?>
			<div class="footer-builder__bottom">

				<?php if ( $has_contact || $has_social ) : ?>
					<?php
					$footer_logo_pos  = function_exists( 'jasanika_logo_get_footer_position' ) ? jasanika_logo_get_footer_position() : 'left';
					$footer_pos_class = 'left' !== $footer_logo_pos ? ' footer-builder__contact--pos-' . $footer_logo_pos : '';
					?>
					<div class="footer-builder__contact<?php echo esc_attr( $footer_pos_class ); ?>">

						<?php
						$footer_logo = jasanika_get_footer_logo();
						if ( $footer_logo ) :
						?>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-builder__brand-logo">
								<?php echo $footer_logo; // Escaped within helper. ?>
							</a>
						<?php endif; ?>

						<?php if ( $contact['company'] ) : ?>
							<p class="footer-builder__contact-company"><?php echo esc_html( $contact['company'] ); ?></p>
						<?php endif; ?>

						<?php if ( $has_contact ) : ?>
							<ul class="footer-contact__list">

								<?php if ( $contact['phone'] ) : ?>
									<li class="footer-contact__item">
										<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $contact['phone'] ) ); ?>">
											<?php echo esc_html( $contact['phone'] ); ?>
										</a>
									</li>
								<?php endif; ?>

								<?php if ( $contact['email'] ) : ?>
									<li class="footer-contact__item">
										<a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>">
											<?php echo esc_html( $contact['email'] ); ?>
										</a>
									</li>
								<?php endif; ?>

								<?php if ( $contact['address'] ) : ?>
									<li class="footer-contact__item">
										<?php echo nl2br( esc_html( $contact['address'] ) ); ?>
									</li>
								<?php endif; ?>

							</ul>
						<?php endif; ?>

						<?php if ( $has_social ) : ?>
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
				<?php endif; ?>

				<?php if ( $has_menu ) : ?>
					<nav class="footer-builder__nav" aria-label="<?php esc_attr_e( 'Footer Navigation', 'jasanika' ); ?>">
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
					</nav>
				<?php endif; ?>

			</div>
		<?php endif; ?>

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

<?php get_template_part( 'template-parts/components/cookie-banner' ); ?>

<?php wp_footer(); ?>

</body>
</html>
