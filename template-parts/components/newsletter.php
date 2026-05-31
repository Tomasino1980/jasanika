<?php

/**
 * Newsletter Section
 *
 * Displays the newsletter subscription form on the homepage.
 * Content is managed from Jasanika → Theme Settings → Newsletter.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$newsletter_title        = jasanika_get_newsletter_title();
$newsletter_description  = jasanika_get_newsletter_description();
$newsletter_privacy_text = jasanika_get_newsletter_privacy_text();
?>

<section class="newsletter-section" id="newsletter">
	<div class="newsletter-section__container">

		<h2 class="newsletter-section__heading"><?php echo esc_html( $newsletter_title ); ?></h2>

		<?php if ( ! empty( $newsletter_description ) ) : ?>
		<p class="newsletter-section__description">
			<?php echo esc_html( $newsletter_description ); ?>
		</p>
		<?php endif; ?>

		<form class="newsletter-section__form" method="post" novalidate>

			<?php wp_nonce_field( 'jasanika_newsletter_subscribe', 'jasanika_newsletter_nonce' ); ?>

			<div class="newsletter-section__input-row">
				<input
					type="email"
					class="newsletter-section__email"
					name="newsletter_email"
					placeholder="<?php esc_attr_e( 'Your email address', 'jasanika' ); ?>"
					autocomplete="email"
					required
				>
				<button type="submit" class="newsletter-section__submit">
					<?php esc_html_e( 'Subscribe', 'jasanika' ); ?>
				</button>
			</div>

			<?php if ( ! empty( $newsletter_privacy_text ) ) : ?>
			<label class="newsletter-section__consent">
				<input
					type="checkbox"
					class="newsletter-section__consent-checkbox"
					name="newsletter_consent"
					value="1"
					required
				>
				<span><?php echo esc_html( $newsletter_privacy_text ); ?></span>
			</label>
			<?php endif; ?>

			<div class="newsletter-section__message" aria-live="polite"></div>

		</form>

	</div>
</section>
