<?php

/**
 * CTA Section
 *
 * Displays a Call To Action section on the homepage.
 */
?>

<section class="cta-section">
	<div class="cta-section__container">

		<h2 class="cta-section__heading"><?php esc_html_e( 'Máte vlastní nápad?', 'jasanika' ); ?></h2>

		<p class="cta-section__text">
			<?php esc_html_e( 'Vyrábíme zakázkové výrobky podle vašich představ.', 'jasanika' ); ?><br>
			<?php esc_html_e( 'Kontaktujte nás a společně najdeme řešení.', 'jasanika' ); ?>
		</p>

		<a href="<?php echo esc_url( home_url( '/kontakt' ) ); ?>" class="cta-section__button">
			<?php esc_html_e( 'Kontaktujte nás', 'jasanika' ); ?>
		</a>

	</div>
</section>
