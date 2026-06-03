/**
 * logo-settings.js – Live preview for Logo Settings admin page.
 *
 * Handles:
 *  – Updating preview panels when position radios change.
 *  – Updating logo image in previews when URL fields change.
 */

( function () {
	'use strict';

	/**
	 * Update the position class on a preview canvas.
	 *
	 * @param {HTMLElement} canvas   Preview element.
	 * @param {string}      newPos   New position value.
	 */
	function updatePreviewPosition( canvas, newPos ) {
		canvas.className = canvas.className.replace( /\bpos-\S+/g, '' ).trim();
		canvas.classList.add( 'pos-' + newPos );
	}

	/**
	 * Update the logo image element inside a preview canvas.
	 *
	 * @param {string}      previewId  ID of the preview canvas element.
	 * @param {string}      url        New image URL.
	 */
	function updatePreviewLogo( previewId, url ) {
		const canvas = document.getElementById( previewId );
		if ( ! canvas ) {
			return;
		}
		const img  = canvas.querySelector( '.jasanika-lsp-logo' );
		const text = canvas.querySelector( '.jasanika-lsp-logo-text' );

		if ( url ) {
			if ( img ) {
				img.src           = url;
				img.style.display = '';
			} else {
				const newImg    = document.createElement( 'img' );
				newImg.src      = url;
				newImg.alt      = '';
				newImg.className = 'jasanika-lsp-logo';
				canvas.appendChild( newImg );
			}
			if ( text ) {
				text.style.display = 'none';
			}
		} else {
			if ( img ) {
				img.style.display = 'none';
			}
			if ( text ) {
				text.style.display = '';
			}
		}
	}

	/**
	 * Bind position radio buttons to their preview canvas.
	 *
	 * @param {string} radioName   Name attribute of the radio group.
	 * @param {string} canvasId    ID of the preview canvas element.
	 */
	function bindPositionRadios( radioName, canvasId ) {
		const radios = document.querySelectorAll( 'input[name="' + radioName + '"]' );
		const canvas = document.getElementById( canvasId );

		if ( ! canvas ) {
			return;
		}

		radios.forEach( function ( radio ) {
			radio.addEventListener( 'change', function () {
				if ( this.checked ) {
					updatePreviewPosition( canvas, this.value );
				}
			} );
		} );
	}

	/**
	 * Bind a URL text input to a preview canvas logo.
	 *
	 * @param {string} inputId    ID of the URL text input.
	 * @param {string} canvasId   ID of the preview canvas element.
	 */
	function bindUrlInput( inputId, canvasId ) {
		const input = document.getElementById( inputId );
		if ( ! input ) {
			return;
		}

		input.addEventListener( 'input', function () {
			updatePreviewLogo( canvasId, this.value.trim() );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {

		// Header preview.
		bindPositionRadios(
			'jasanika_settings[logo_header_pos]',
			'jlsp-header-canvas'
		);
		bindUrlInput( 'jasanika_logo_url', 'jlsp-header-canvas' );

		// Footer preview.
		bindPositionRadios(
			'jasanika_settings[logo_footer_pos]',
			'jlsp-footer-canvas'
		);
		bindUrlInput( 'jasanika_footer_logo_url', 'jlsp-footer-canvas' );

		// Hero preview.
		bindPositionRadios(
			'jasanika_settings[logo_hero_pos]',
			'jlsp-hero-canvas'
		);
		bindUrlInput( 'jasanika_logo_hero_url', 'jlsp-hero-canvas' );

		// Mobile preview.
		bindPositionRadios(
			'jasanika_settings[logo_mobile_pos]',
			'jlsp-mobile-canvas'
		);
		bindUrlInput( 'jasanika_logo_mobile_url', 'jlsp-mobile-canvas' );

	} );

} )();
