/**
 * logo-placement.js
 *
 * Handles the Logo Placement admin UI:
 * - Range slider live value display
 * - Logo height custom/auto toggle
 * - Live alignment preview updates
 */

( function () {
	'use strict';

	// -------------------------------------------------------------------------
	// Range sliders – live value display
	// -------------------------------------------------------------------------

	function initRangeSliders() {
		document.querySelectorAll( '.jasanika-range-input' ).forEach( function ( slider ) {
			var displayId = slider.dataset.display;
			var unit      = slider.dataset.unit || '';
			var display   = displayId ? document.getElementById( displayId ) : null;

			if ( ! display ) return;

			slider.addEventListener( 'input', function () {
				display.textContent = slider.value + unit;
				updatePreview();
			} );
		} );
	}

	// -------------------------------------------------------------------------
	// Logo height – auto / custom toggle
	// -------------------------------------------------------------------------

	function initHeightToggle() {
		document.querySelectorAll( '.jasanika-height-mode-radio' ).forEach( function ( radio ) {
			radio.addEventListener( 'change', function () {
				var customWrap = document.querySelector( '.jasanika-height-custom' );
				if ( ! customWrap ) return;
				customWrap.style.display = ( radio.value === 'custom' && radio.checked ) ? '' : 'none';
			} );
		} );
	}

	// -------------------------------------------------------------------------
	// Live preview – read current control values and update preview panels
	// -------------------------------------------------------------------------

	function getVal( name, fallback ) {
		var el = document.querySelector( '[name="jasanika_settings[' + name + ']"]' );
		return el ? el.value : fallback;
	}

	function getRadioVal( name, fallback ) {
		var checked = document.querySelector( '[name="jasanika_settings[' + name + '"]]:checked' );
		return checked ? checked.value : fallback;
	}

	function updatePreview() {
		var headerPos = getRadioVal( 'logo_header_pos', 'left' );
		var footerPos = getRadioVal( 'logo_footer_pos', 'left' );
		var heroPos   = getRadioVal( 'logo_hero_pos',   'center' );

		// Header preview
		var headerPanel = document.querySelector( '.jlp-header-preview' );
		if ( headerPanel ) {
			headerPanel.dataset.pos = headerPos;
		}

		// Hero preview
		var heroPanel = document.querySelector( '.jlp-hero-preview' );
		if ( heroPanel ) {
			heroPanel.dataset.pos = heroPos;
		}

		// Footer preview
		var footerPanel = document.querySelector( '.jlp-footer-preview' );
		if ( footerPanel ) {
			footerPanel.dataset.pos = footerPos;
		}
	}

	// -------------------------------------------------------------------------
	// Listen to position radio changes
	// -------------------------------------------------------------------------

	function initPositionRadios() {
		var positionFields = [
			'logo_header_pos',
			'logo_footer_pos',
			'logo_hero_pos',
		];

		positionFields.forEach( function ( name ) {
			document.querySelectorAll( '[name="jasanika_settings[' + name + ']"]' ).forEach( function ( radio ) {
				radio.addEventListener( 'change', updatePreview );
			} );
		} );
	}

	// -------------------------------------------------------------------------
	// Boot
	// -------------------------------------------------------------------------

	function init() {
		initRangeSliders();
		initHeightToggle();
		initPositionRadios();
		updatePreview(); // apply saved values on load
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
