/* Theme Presets – Jasanika Admin */
(function () {
	'use strict';

	var previewRoot = document.getElementById( 'jasanika-preset-live-preview' );

	function isValidHex( value ) {
		return /^#[0-9a-fA-F]{6}$/.test( value );
	}

	/**
	 * Apply a preset config object to the live preview area.
	 *
	 * @param {Object} config
	 */
	function applyPreview( config ) {
		if ( ! config || typeof config !== 'object' || ! previewRoot ) {
			return;
		}

		previewRoot.style.setProperty( '--js-primary',   isValidHex( config.primary_color )    ? config.primary_color    : '#b78acb' );
		previewRoot.style.setProperty( '--js-secondary', isValidHex( config.secondary_color )  ? config.secondary_color  : '#24212b' );
		previewRoot.style.setProperty( '--js-accent',    isValidHex( config.accent_color )     ? config.accent_color     : '#f1c95d' );
		previewRoot.style.setProperty( '--js-bg',        isValidHex( config.background_color ) ? config.background_color : '#1b1a1f' );
		previewRoot.style.setProperty( '--js-text',      isValidHex( config.text_color )       ? config.text_color       : '#f5f2f7' );

		var button = previewRoot.querySelector( '.jasanika-presets__preview-button' );
		if ( ! button ) {
			return;
		}
		button.classList.remove(
			'jasanika-presets__preview-button--solid',
			'jasanika-presets__preview-button--outline',
			'jasanika-presets__preview-button--ghost'
		);
		button.classList.add( 'jasanika-presets__preview-button--' + ( config.button_style || 'solid' ) );
	}

	// Preview trigger buttons on each preset card.
	document.querySelectorAll( '.jasanika-preview-trigger' ).forEach( function ( trigger ) {
		trigger.addEventListener( 'click', function () {
			var json = trigger.getAttribute( 'data-config' );
			if ( ! json ) {
				return;
			}
			try {
				applyPreview( JSON.parse( json ) );
			} catch ( e ) {
				// Ignore invalid JSON.
			}
		} );
	} );

	// Auto-preview first preset on load.
	var first = document.querySelector( '.jasanika-preview-trigger' );
	if ( first ) {
		first.click();
	}

}() );
