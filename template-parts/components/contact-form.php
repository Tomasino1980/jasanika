<?php

/**
 * Contact Form
 *
 * Visual placeholder for the contact form.
 * Form processing will be implemented in M20 - Contact Form Processing.
 */
?>

<div class="contact-form__card">

	<form class="contact-form" action="#" method="post" novalidate>

		<div class="contact-form__group">
			<label class="contact-form__label" for="contact-name">
				<?php esc_html_e( 'Jméno', 'jasanika' ); ?>
			</label>
			<input
				class="contact-form__input"
				type="text"
				id="contact-name"
				name="contact_name"
				placeholder="<?php esc_attr_e( 'Vaše jméno', 'jasanika' ); ?>"
				disabled
			>
		</div>

		<div class="contact-form__group">
			<label class="contact-form__label" for="contact-email">
				<?php esc_html_e( 'E-mail', 'jasanika' ); ?>
			</label>
			<input
				class="contact-form__input"
				type="email"
				id="contact-email"
				name="contact_email"
				placeholder="<?php esc_attr_e( 'vas@email.cz', 'jasanika' ); ?>"
				disabled
			>
		</div>

		<div class="contact-form__group">
			<label class="contact-form__label" for="contact-phone">
				<?php esc_html_e( 'Telefon', 'jasanika' ); ?>
			</label>
			<input
				class="contact-form__input"
				type="tel"
				id="contact-phone"
				name="contact_phone"
				placeholder="<?php esc_attr_e( '+420 000 000 000', 'jasanika' ); ?>"
				disabled
			>
		</div>

		<div class="contact-form__group">
			<label class="contact-form__label" for="contact-message">
				<?php esc_html_e( 'Zpráva', 'jasanika' ); ?>
			</label>
			<textarea
				class="contact-form__textarea"
				id="contact-message"
				name="contact_message"
				rows="6"
				placeholder="<?php esc_attr_e( 'Napište nám zprávu…', 'jasanika' ); ?>"
				disabled
			></textarea>
		</div>

		<div class="contact-form__footer">
			<button class="btn btn-primary btn-lg contact-form__submit" type="submit" disabled>
				<?php esc_html_e( 'Odeslat zprávu', 'jasanika' ); ?>
			</button>
			<p class="contact-form__notice">
				<?php esc_html_e( 'Formulář bude dostupný po aktivaci funkce odesílání.', 'jasanika' ); ?>
			</p>
		</div>

	</form>

</div>
