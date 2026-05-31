/**
 * Newsletter – Frontend AJAX subscription handler.
 *
 * Handles form validation and AJAX submission of the newsletter subscription form.
 * Depends on jasanikaNL global localised by wp_localize_script().
 */

( function () {
	'use strict';

	const form = document.querySelector( '.newsletter-section__form' );

	if ( ! form ) {
		return;
	}

	const emailInput   = form.querySelector( '.newsletter-section__email' );
	const consentInput = form.querySelector( '.newsletter-section__consent-checkbox' );
	const submitBtn    = form.querySelector( '.newsletter-section__submit' );
	const messageBox   = form.querySelector( '.newsletter-section__message' );
	const nonceInput   = form.querySelector( '[name="jasanika_newsletter_nonce"]' );

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		// Reset state.
		clearMessage( messageBox );

		const email   = emailInput ? emailInput.value.trim() : '';
		const consent = consentInput ? consentInput.checked : false;
		const nonce   = nonceInput ? nonceInput.value : '';

		// Client-side validation.
		if ( ! email ) {
			showError( messageBox, jasanikaNL.messages.email_required );
			if ( emailInput ) {
				emailInput.focus();
			}
			return;
		}

		if ( ! consent ) {
			showError( messageBox, jasanikaNL.messages.consent_required );
			return;
		}

		// Disable submit during request.
		if ( submitBtn ) {
			submitBtn.disabled = true;
		}

		const body = new URLSearchParams();
		body.append( 'action', 'jasanika_newsletter_subscribe' );
		body.append( 'email', email );
		body.append( 'consent', '1' );
		body.append( 'nonce', nonce );

		fetch( jasanikaNL.ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
		} )
			.then( function ( res ) {
				return res.json();
			} )
			.then( function ( data ) {
				if ( submitBtn ) {
					submitBtn.disabled = false;
				}
				if ( data.success ) {
					showSuccess( messageBox, data.data.message );
					form.reset();
				} else {
					showError( messageBox, data.data.message );
				}
			} )
			.catch( function () {
				if ( submitBtn ) {
					submitBtn.disabled = false;
				}
				showError( messageBox, jasanikaNL.messages.server_error );
			} );
	} );

	function clearMessage( el ) {
		if ( ! el ) {
			return;
		}
		el.textContent = '';
		el.className   = 'newsletter-section__message';
	}

	function showSuccess( el, text ) {
		if ( ! el ) {
			return;
		}
		el.textContent = text;
		el.classList.add( 'newsletter-section__message--success' );
	}

	function showError( el, text ) {
		if ( ! el ) {
			return;
		}
		el.textContent = text;
		el.classList.add( 'newsletter-section__message--error' );
	}

} )();
