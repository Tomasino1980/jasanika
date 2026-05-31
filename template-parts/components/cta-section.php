<?php

/**
 * CTA Section
 *
 * Displays a Call To Action section on the homepage.
 * Content is managed from Jasanika → Theme Settings → Homepage Content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cta_title       = jasanika_get_cta_title();
$cta_description = jasanika_get_cta_description();
$cta_button_text = jasanika_get_cta_button_text();
$cta_button_url  = jasanika_get_cta_button_url();
?>

<section class="cta-section">
	<div class="cta-section__container">

		<h2 class="cta-section__heading"><?php echo esc_html( $cta_title ); ?></h2>

		<?php if ( ! empty( $cta_description ) ) : ?>
		<p class="cta-section__text">
			<?php echo esc_html( $cta_description ); ?>
		</p>
		<?php endif; ?>

		<?php if ( ! empty( $cta_button_text ) && ! empty( $cta_button_url ) ) : ?>
		<a href="<?php echo esc_url( $cta_button_url ); ?>" class="cta-section__button">
			<?php echo esc_html( $cta_button_text ); ?>
		</a>
		<?php endif; ?>

	</div>
</section>
