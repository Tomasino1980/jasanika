/**
 * Jasanika – Theme Presets Color Editor
 *
 * Modern color editor modal. Replaces the WordPress Color Picker.
 * Inspired by Windows 11, Figma and Visual Studio color tools.
 *
 * Vanilla JavaScript – no jQuery dependency.
 *
 * @since 0.52.0
 */
(function () {
	'use strict';

	/** @type {JasanikaEditorData} */
	const data = window.jasanikaEditorData;
	if ( ! data ) {
		return;
	}

	const i18n = data.i18n || {};

	// ─── Color utility functions ──────────────────────────────────

	/**
	 * Convert a 6-digit HEX string to {r, g, b}.
	 *
	 * @param {string} hex
	 * @returns {{r:number,g:number,b:number}|null}
	 */
	function hexToRgb( hex ) {
		const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec( hex );
		if ( ! m ) {
			return null;
		}
		return {
			r: parseInt( m[ 1 ], 16 ),
			g: parseInt( m[ 2 ], 16 ),
			b: parseInt( m[ 3 ], 16 ),
		};
	}

	/**
	 * Convert RGB (0-255) to a 6-digit HEX string.
	 *
	 * @param {number} r
	 * @param {number} g
	 * @param {number} b
	 * @returns {string}
	 */
	function rgbToHex( r, g, b ) {
		return '#' + [ r, g, b ]
			.map( function ( v ) {
				return Math.max( 0, Math.min( 255, Math.round( v ) ) )
					.toString( 16 )
					.padStart( 2, '0' );
			} )
			.join( '' );
	}

	/**
	 * Convert RGB (0-255) to HSV {h:0-360, s:0-1, v:0-1}.
	 *
	 * @param {number} r
	 * @param {number} g
	 * @param {number} b
	 * @returns {{h:number,s:number,v:number}}
	 */
	function rgbToHsv( r, g, b ) {
		r /= 255;
		g /= 255;
		b /= 255;

		const max = Math.max( r, g, b );
		const min = Math.min( r, g, b );
		const d   = max - min;

		let h = 0;
		const s = max === 0 ? 0 : d / max;
		const v = max;

		if ( d !== 0 ) {
			if ( max === r ) {
				h = ( ( g - b ) / d + ( g < b ? 6 : 0 ) ) / 6;
			} else if ( max === g ) {
				h = ( ( b - r ) / d + 2 ) / 6;
			} else {
				h = ( ( r - g ) / d + 4 ) / 6;
			}
		}

		return { h: h * 360, s: s, v: v };
	}

	/**
	 * Convert HSV {h:0-360, s:0-1, v:0-1} to RGB {r,g,b} (0-255).
	 *
	 * @param {number} h
	 * @param {number} s
	 * @param {number} v
	 * @returns {{r:number,g:number,b:number}}
	 */
	function hsvToRgb( h, s, v ) {
		h = ( ( h % 360 ) + 360 ) % 360;
		const c = v * s;
		const x = c * ( 1 - Math.abs( ( h / 60 ) % 2 - 1 ) );
		const m = v - c;

		let r = 0;
		let g = 0;
		let b = 0;

		if      ( h < 60  ) { r = c; g = x; b = 0; }
		else if ( h < 120 ) { r = x; g = c; b = 0; }
		else if ( h < 180 ) { r = 0; g = c; b = x; }
		else if ( h < 240 ) { r = 0; g = x; b = c; }
		else if ( h < 300 ) { r = x; g = 0; b = c; }
		else                { r = c; g = 0; b = x; }

		return {
			r: Math.round( ( r + m ) * 255 ),
			g: Math.round( ( g + m ) * 255 ),
			b: Math.round( ( b + m ) * 255 ),
		};
	}

	/**
	 * Check if a string is a valid 6-digit HEX color.
	 *
	 * @param {string} v
	 * @returns {boolean}
	 */
	function isValidHex( v ) {
		return /^#[0-9a-fA-F]{6}$/.test( v );
	}

	/**
	 * Clamp value between min and max.
	 *
	 * @param {number} v
	 * @param {number} min
	 * @param {number} max
	 * @returns {number}
	 */
	function clamp( v, min, max ) {
		return Math.max( min, Math.min( max, v ) );
	}

	/**
	 * Escape HTML special characters.
	 *
	 * @param {string} str
	 * @returns {string}
	 */
	function escHtml( str ) {
		return String( str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	// ─── State ────────────────────────────────────────────────────

	const FIELD_LABELS = {
		primary_color:    i18n.primaryColor    || 'Primary Color',
		secondary_color:  i18n.secondaryColor  || 'Secondary Color',
		accent_color:     i18n.accentColor     || 'Accent Color',
		background_color: i18n.backgroundColor || 'Background Color',
		text_color:       i18n.textColor       || 'Text Color',
	};

	const FIELDS = Object.keys( FIELD_LABELS );

	/** @type {HTMLElement|null} */
	let overlayEl = null;

	/** @type {HTMLElement|null} */
	let modalEl = null;

	/** @type {HTMLCanvasElement|null} */
	let specCanvas = null;

	/** @type {CanvasRenderingContext2D|null} */
	let specCtx = null;

	let specWidth  = 0;
	let specHeight = 0;

	/** @type {HTMLElement|null} Last focused element before modal opened. */
	let lastFocusedEl = null;

	const state = {
		presetId:    '',
		presetName:  '',
		activeField: 'primary_color',
		original:    {},
		defaults:    {},
		current:     {},
		hsv:         { h: 0, s: 1, v: 1 },
		favorites:   [],
	};

	// ─── Spectrum rendering ───────────────────────────────────────

	/**
	 * Render the 2D spectrum canvas for a given hue.
	 * X-axis = saturation (left=white, right=pure hue)
	 * Y-axis = value/brightness (top=bright, bottom=black)
	 *
	 * @param {number} hue 0-360
	 */
	function renderSpectrum( hue ) {
		if ( ! specCtx ) {
			return;
		}

		specCtx.clearRect( 0, 0, specWidth, specHeight );

		// Horizontal: white → pure hue
		const gradH = specCtx.createLinearGradient( 0, 0, specWidth, 0 );
		gradH.addColorStop( 0, '#ffffff' );
		gradH.addColorStop( 1, 'hsl(' + hue + ',100%,50%)' );
		specCtx.fillStyle = gradH;
		specCtx.fillRect( 0, 0, specWidth, specHeight );

		// Vertical: transparent → black
		const gradV = specCtx.createLinearGradient( 0, 0, 0, specHeight );
		gradV.addColorStop( 0, 'rgba(0,0,0,0)' );
		gradV.addColorStop( 1, 'rgba(0,0,0,1)' );
		specCtx.fillStyle = gradV;
		specCtx.fillRect( 0, 0, specWidth, specHeight );
	}

	/**
	 * Move the spectrum cursor to the given saturation/value position.
	 *
	 * @param {number} s 0-1
	 * @param {number} v 0-1
	 */
	function updateSpectrumCursor( s, v ) {
		const cursor = document.getElementById( 'jse-spectrum-cursor' );
		if ( ! cursor ) {
			return;
		}
		cursor.style.left = ( s * specWidth ) + 'px';
		cursor.style.top  = ( ( 1 - v ) * specHeight ) + 'px';
	}

	// ─── UI synchronisation ───────────────────────────────────────

	/**
	 * Sync all UI elements from the current state.hsv.
	 * The `source` parameter prevents feedback loops.
	 *
	 * @param {string} source 'hue'|'spectrum'|'hex'|'rgb'|'all'
	 */
	function syncFromHsv( source ) {
		const h   = state.hsv.h;
		const s   = state.hsv.s;
		const v   = state.hsv.v;
		const rgb = hsvToRgb( h, s, v );
		const hex = rgbToHex( rgb.r, rgb.g, rgb.b );

		state.current[ state.activeField ] = hex;

		if ( source !== 'hue' ) {
			const slider = document.getElementById( 'jse-hue-slider' );
			if ( slider ) {
				slider.value = String( Math.round( h ) );
			}
		}

		renderSpectrum( h );
		updateSpectrumCursor( s, v );

		if ( source !== 'hex' ) {
			const hexInput = document.getElementById( 'jse-hex' );
			if ( hexInput ) {
				hexInput.value = hex;
			}
		}

		if ( source !== 'rgb' ) {
			const rI = document.getElementById( 'jse-r' );
			const gI = document.getElementById( 'jse-g' );
			const bI = document.getElementById( 'jse-b' );
			if ( rI ) { rI.value = String( rgb.r ); }
			if ( gI ) { gI.value = String( rgb.g ); }
			if ( bI ) { bI.value = String( rgb.b ); }
		}

		updatePreviewSwatch( hex );
		updateTabSwatch( state.activeField, hex );
		updateLivePreview();
	}

	/**
	 * Set a new color from a HEX string and sync all UI.
	 *
	 * @param {string} hex    Valid 6-digit HEX
	 * @param {string} source Sync source identifier
	 */
	function setColorFromHex( hex, source ) {
		if ( ! isValidHex( hex ) ) {
			return;
		}
		const rgb = hexToRgb( hex );
		if ( ! rgb ) {
			return;
		}
		state.hsv = rgbToHsv( rgb.r, rgb.g, rgb.b );
		syncFromHsv( source || 'all' );
	}

	// ─── Preview updates ──────────────────────────────────────────

	/**
	 * Update the color swatch inside the modal.
	 *
	 * @param {string} hex
	 */
	function updatePreviewSwatch( hex ) {
		const el = document.getElementById( 'jse-preview-swatch' );
		if ( el ) {
			el.style.backgroundColor = hex;
		}
	}

	/**
	 * Update the small swatch inside a field tab.
	 *
	 * @param {string} field
	 * @param {string} hex
	 */
	function updateTabSwatch( field, hex ) {
		const tab = document.querySelector( '.jse-field-tab[data-field="' + field + '"]' );
		if ( ! tab ) {
			return;
		}
		const swatch = tab.querySelector( '.jse-field-tab__swatch' );
		if ( swatch ) {
			swatch.style.backgroundColor = hex;
		}
	}

	/**
	 * Apply current working colors to the live page preview area.
	 */
	function updateLivePreview() {
		const preview = document.getElementById( 'jasanika-preset-live-preview' );
		if ( ! preview ) {
			return;
		}
		const c = state.current;
		if ( isValidHex( c.primary_color ) )    { preview.style.setProperty( '--js-primary',   c.primary_color ); }
		if ( isValidHex( c.secondary_color ) )  { preview.style.setProperty( '--js-secondary', c.secondary_color ); }
		if ( isValidHex( c.accent_color ) )     { preview.style.setProperty( '--js-accent',    c.accent_color ); }
		if ( isValidHex( c.background_color ) ) { preview.style.setProperty( '--js-bg',        c.background_color ); }
		if ( isValidHex( c.text_color ) )       { preview.style.setProperty( '--js-text',      c.text_color ); }
	}

	/**
	 * Restore the live preview to the original (pre-edit) colors.
	 */
	function restoreLivePreview() {
		const preview = document.getElementById( 'jasanika-preset-live-preview' );
		if ( ! preview ) {
			return;
		}
		const c = state.original;
		if ( isValidHex( c.primary_color ) )    { preview.style.setProperty( '--js-primary',   c.primary_color ); }
		if ( isValidHex( c.secondary_color ) )  { preview.style.setProperty( '--js-secondary', c.secondary_color ); }
		if ( isValidHex( c.accent_color ) )     { preview.style.setProperty( '--js-accent',    c.accent_color ); }
		if ( isValidHex( c.background_color ) ) { preview.style.setProperty( '--js-bg',        c.background_color ); }
		if ( isValidHex( c.text_color ) )       { preview.style.setProperty( '--js-text',      c.text_color ); }
	}

	// ─── Field tabs ───────────────────────────────────────────────

	/**
	 * Render all field selection tabs inside the modal.
	 */
	function renderFieldTabs() {
		const container = document.getElementById( 'jse-field-tabs' );
		if ( ! container ) {
			return;
		}
		container.innerHTML = '';

		FIELDS.forEach( function ( field ) {
			const btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'jse-field-tab' + ( field === state.activeField ? ' is-active' : '' );
			btn.setAttribute( 'data-field', field );
			btn.setAttribute( 'role', 'tab' );
			btn.setAttribute( 'aria-selected', field === state.activeField ? 'true' : 'false' );
			btn.setAttribute( 'aria-label', FIELD_LABELS[ field ] );

			const swatch = document.createElement( 'span' );
			swatch.className = 'jse-field-tab__swatch';
			swatch.style.backgroundColor = state.current[ field ] || '#000000';
			swatch.setAttribute( 'aria-hidden', 'true' );

			const label = document.createElement( 'span' );
			label.className = 'jse-field-tab__label';
			label.textContent = FIELD_LABELS[ field ];

			btn.appendChild( swatch );
			btn.appendChild( label );
			container.appendChild( btn );

			btn.addEventListener( 'click', function () {
				switchField( field );
			} );
		} );
	}

	/**
	 * Switch the active color field and update the editor.
	 *
	 * @param {string} field
	 */
	function switchField( field ) {
		state.activeField = field;

		document.querySelectorAll( '.jse-field-tab' ).forEach( function ( tab ) {
			const isActive = tab.getAttribute( 'data-field' ) === field;
			tab.classList.toggle( 'is-active', isActive );
			tab.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
		} );

		const hex = state.current[ field ] || '#b78acb';
		setColorFromHex( hex, 'switch' );
	}

	// ─── Favorites ────────────────────────────────────────────────

	/**
	 * Render the favorites color grid.
	 */
	function renderFavorites() {
		const grid = document.getElementById( 'jse-favorites-grid' );
		if ( ! grid ) {
			return;
		}
		grid.innerHTML = '';

		if ( ! state.favorites.length ) {
			const empty = document.createElement( 'span' );
			empty.className = 'jse-favorites-empty';
			empty.textContent = i18n.noFavorites || 'No saved colors yet.';
			grid.appendChild( empty );
			return;
		}

		state.favorites.forEach( function ( hex, index ) {
			const btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'jse-favorites__swatch';
			btn.style.backgroundColor = hex;
			btn.setAttribute( 'title', hex + ' (right-click to remove)' );
			btn.setAttribute( 'aria-label', hex );
			btn.setAttribute( 'role', 'listitem' );

			btn.addEventListener( 'click', function () {
				setColorFromHex( hex, 'favorite' );
			} );

			btn.addEventListener( 'contextmenu', function ( e ) {
				e.preventDefault();
				removeFavorite( index );
			} );

			grid.appendChild( btn );
		} );
	}

	/**
	 * Add the current active color to favorites.
	 */
	function addFavorite() {
		const hex = state.current[ state.activeField ];
		if ( ! isValidHex( hex ) ) {
			return;
		}
		if ( state.favorites.indexOf( hex ) !== -1 ) {
			return;
		}
		if ( state.favorites.length >= 20 ) {
			state.favorites.shift();
		}
		state.favorites.push( hex );
		saveFavoritesAjax();
		renderFavorites();
	}

	/**
	 * Remove a favorite color by its index.
	 *
	 * @param {number} index
	 */
	function removeFavorite( index ) {
		state.favorites.splice( index, 1 );
		saveFavoritesAjax();
		renderFavorites();
	}

	/**
	 * Persist favorites to WordPress options via AJAX.
	 */
	function saveFavoritesAjax() {
		const formData = new FormData();
		formData.append( 'action', 'jasanika_favorite_colors_save' );
		formData.append( 'nonce', data.nonceFavorites );
		formData.append( 'colors', JSON.stringify( state.favorites ) );
		fetch( data.ajaxUrl, { method: 'POST', body: formData } );
	}

	// ─── Save / Cancel / Reset ────────────────────────────────────

	/**
	 * Save preset colors via AJAX. On success: update card, close modal.
	 */
	function saveColors() {
		const saveBtn = document.getElementById( 'jse-save' );
		if ( saveBtn ) {
			saveBtn.disabled = true;
			saveBtn.textContent = i18n.saving || 'Saving\u2026';
		}

		hideError();

		const formData = new FormData();
		formData.append( 'action', 'jasanika_preset_editor_save' );
		formData.append( 'nonce', data.nonceSave );
		formData.append( 'preset_id', state.presetId );

		FIELDS.forEach( function ( field ) {
			formData.append( 'config[' + field + ']', state.current[ field ] || '' );
		} );

		fetch( data.ajaxUrl, { method: 'POST', body: formData } )
			.then( function ( r ) { return r.json(); } )
			.then( function ( response ) {
				if ( response.success ) {
					updateCardUI( state.presetId, state.current );
					closeModal( false );
				} else {
					showError( response.data || i18n.saveError || 'Save failed.' );
				}
			} )
			.catch( function () {
				showError( i18n.networkError || 'Network error.' );
			} )
			.finally( function () {
				if ( saveBtn ) {
					saveBtn.disabled = false;
					saveBtn.textContent = i18n.save || 'Save';
				}
			} );
	}

	/**
	 * Reset all colors to the preset defaults (state at modal open).
	 */
	function resetColors() {
		Object.assign( state.current, state.defaults );
		FIELDS.forEach( function ( field ) {
			updateTabSwatch( field, state.current[ field ] || '#000000' );
		} );
		setColorFromHex( state.current[ state.activeField ] || '#b78acb', 'reset' );
	}

	// ─── Error display ────────────────────────────────────────────

	function showError( msg ) {
		let el = document.getElementById( 'jse-error' );
		if ( ! el ) {
			el = document.createElement( 'div' );
			el.id        = 'jse-error';
			el.className = 'jse-error';
			el.setAttribute( 'role', 'alert' );
			el.setAttribute( 'aria-live', 'assertive' );
			const footer = document.querySelector( '.jse-modal__footer' );
			if ( footer ) {
				footer.insertBefore( el, footer.firstChild );
			}
		}
		el.textContent  = msg;
		el.style.display = 'block';
	}

	function hideError() {
		const el = document.getElementById( 'jse-error' );
		if ( el ) {
			el.style.display = 'none';
		}
	}

	// ─── Update card UI after save ────────────────────────────────

	/**
	 * After a successful save, update swatches and preview-trigger data
	 * on the preset card without a page reload.
	 *
	 * @param {string} presetId
	 * @param {Object} config
	 */
	function updateCardUI( presetId, config ) {
		const editBtn = document.querySelector(
			'.jasanika-open-color-editor[data-preset-id="' + presetId + '"]'
		);
		if ( ! editBtn ) {
			return;
		}
		const card = editBtn.closest( '.jasanika-presets__card' );
		if ( ! card ) {
			return;
		}

		// Update color swatches
		const swatchEls  = card.querySelectorAll( '.jasanika-presets__swatches span' );
		const colorKeys  = [ 'primary_color', 'secondary_color', 'accent_color', 'background_color', 'text_color' ];
		colorKeys.forEach( function ( key, i ) {
			if ( swatchEls[ i ] && isValidHex( config[ key ] ) ) {
				swatchEls[ i ].style.setProperty( '--swatch-color', config[ key ] );
			}
		} );

		// Update preview-trigger button data
		const previewBtn = card.querySelector( '.jasanika-preview-trigger' );
		if ( previewBtn ) {
			try {
				const existing = JSON.parse( previewBtn.getAttribute( 'data-config' ) || '{}' );
				Object.assign( existing, config );
				previewBtn.setAttribute( 'data-config', JSON.stringify( existing ) );
			} catch ( e ) {
				// Ignore
			}
		}

		// Update this button's stored config for the next open
		editBtn.setAttribute( 'data-preset-config', JSON.stringify( config ) );
	}

	// ─── Modal lifecycle ──────────────────────────────────────────

	/**
	 * Inject modal HTML into the document body on first call.
	 */
	function buildModal() {
		const root  = document.createElement( 'div' );
		root.id     = 'jse-root';

		root.innerHTML = [
			'<div id="jse-overlay" class="jse-overlay" aria-hidden="true"></div>',
			'<div id="jse-modal" class="jse-modal"',
			'  role="dialog" aria-modal="true" aria-labelledby="jse-modal-title" tabindex="-1">',
			'  <div class="jse-modal__header">',
			'    <h2 id="jse-modal-title" class="jse-modal__title"></h2>',
			'    <button type="button" class="jse-modal__close" id="jse-close"',
			'      aria-label="' + escHtml( i18n.close || 'Close' ) + '">&#x2715;</button>',
			'  </div>',
			'  <div class="jse-modal__body">',
			'    <div class="jse-field-tabs" id="jse-field-tabs" role="tablist"',
			'      aria-label="' + escHtml( i18n.colorFields || 'Color fields' ) + '"></div>',
			'    <div class="jse-editor-area">',
			'      <div class="jse-spectrum-wrap">',
			'        <canvas id="jse-spectrum" class="jse-spectrum" tabindex="0"',
			'          role="slider"',
			'          aria-label="' + escHtml( i18n.colorSpectrum || 'Color spectrum. Use arrow keys to adjust.' ) + '">',
			'        </canvas>',
			'        <div id="jse-spectrum-cursor" class="jse-spectrum__cursor" aria-hidden="true"></div>',
			'      </div>',
			'      <div class="jse-hue-row">',
			'        <span class="jse-slider-label">' + escHtml( i18n.hue || 'Hue' ) + '</span>',
			'        <div class="jse-hue-track">',
			'          <input type="range" id="jse-hue-slider" class="jse-hue-slider"',
			'            min="0" max="360" step="1" value="0"',
			'            aria-label="' + escHtml( i18n.hue || 'Hue' ) + '">',
			'        </div>',
			'      </div>',
			'      <div class="jse-inputs-row">',
			'        <div class="jse-input-group jse-input-group--hex">',
			'          <label class="jse-input-label" for="jse-hex">' + escHtml( i18n.hex || 'HEX' ) + '</label>',
			'          <input type="text" id="jse-hex" class="jse-input"',
			'            maxlength="7" spellcheck="false" autocomplete="off"',
			'            aria-label="' + escHtml( i18n.hexValue || 'HEX color value' ) + '">',
			'        </div>',
			'        <div class="jse-input-group jse-input-group--rgb">',
			'          <div class="jse-rgb-field">',
			'            <label class="jse-input-label" for="jse-r">R</label>',
			'            <input type="number" id="jse-r" class="jse-input jse-input--channel"',
			'              min="0" max="255" aria-label="' + escHtml( i18n.red || 'Red' ) + '">',
			'          </div>',
			'          <div class="jse-rgb-field">',
			'            <label class="jse-input-label" for="jse-g">G</label>',
			'            <input type="number" id="jse-g" class="jse-input jse-input--channel"',
			'              min="0" max="255" aria-label="' + escHtml( i18n.green || 'Green' ) + '">',
			'          </div>',
			'          <div class="jse-rgb-field">',
			'            <label class="jse-input-label" for="jse-b">B</label>',
			'            <input type="number" id="jse-b" class="jse-input jse-input--channel"',
			'              min="0" max="255" aria-label="' + escHtml( i18n.blue || 'Blue' ) + '">',
			'          </div>',
			'        </div>',
			'      </div>',
			'      <div class="jse-preview-row">',
			'        <div class="jse-preview-swatch" id="jse-preview-swatch" aria-hidden="true"></div>',
			'        <span class="jse-preview-label">' + escHtml( i18n.preview || 'Preview' ) + '</span>',
			'      </div>',
			'      <div class="jse-favorites-section">',
			'        <div class="jse-favorites-header">',
			'          <span class="jse-favorites-title">' + escHtml( i18n.favoriteColors || 'Favorite Colors' ) + '</span>',
			'          <button type="button" id="jse-add-favorite" class="jse-btn jse-btn--add-fav"',
			'            title="' + escHtml( i18n.saveToFavorites || 'Save to favorites' ) + '"',
			'            aria-label="' + escHtml( i18n.saveToFavorites || 'Save to favorites' ) + '">+</button>',
			'        </div>',
			'        <div id="jse-favorites-grid" class="jse-favorites-grid" role="list"',
			'          aria-label="' + escHtml( i18n.favoriteColors || 'Favorite Colors' ) + '"></div>',
			'      </div>',
			'    </div>',
			'  </div>',
			'  <div class="jse-modal__footer">',
			'    <button type="button" id="jse-reset" class="jse-btn jse-btn--reset">',
			'      ' + escHtml( i18n.reset || 'Reset' ),
			'    </button>',
			'    <div class="jse-footer-right">',
			'      <button type="button" id="jse-cancel" class="jse-btn jse-btn--cancel">',
			'        ' + escHtml( i18n.cancel || 'Cancel' ),
			'      </button>',
			'      <button type="button" id="jse-save" class="jse-btn jse-btn--save">',
			'        ' + escHtml( i18n.save || 'Save' ),
			'      </button>',
			'    </div>',
			'  </div>',
			'</div>',
		].join( '' );

		document.body.appendChild( root );

		overlayEl  = document.getElementById( 'jse-overlay' );
		modalEl    = document.getElementById( 'jse-modal' );
		specCanvas = document.getElementById( 'jse-spectrum' );

		bindEvents();
	}

	/**
	 * Initialise canvas dimensions. Must be called when modal is visible.
	 */
	function initCanvas() {
		const wrap = specCanvas.parentElement;
		specWidth  = wrap.clientWidth  || 380;
		specHeight = specCanvas.clientHeight || 180;

		// Force the canvas to have the correct resolution
		specCanvas.width  = specWidth;
		specCanvas.height = specHeight;
		specCtx = specCanvas.getContext( '2d' );
	}

	/**
	 * Open the color editor modal for a given preset.
	 *
	 * @param {string} presetId
	 */
	function openModal( presetId ) {
		const presetData = data.presets[ presetId ];
		if ( ! presetData ) {
			return;
		}

		state.presetId    = presetId;
		state.presetName  = presetData.name;
		state.activeField = FIELDS[ 0 ];
		state.original    = Object.assign( {}, presetData.config );
		state.defaults    = Object.assign( {}, presetData.config );
		state.current     = Object.assign( {}, presetData.config );
		state.favorites   = Array.isArray( data.favorites ) ? data.favorites.slice() : [];

		// Update title
		const title = document.getElementById( 'jse-modal-title' );
		if ( title ) {
			title.textContent = ( i18n.editColors || 'Edit Colors' ) + ' \u2013 ' + presetData.name;
		}

		// Hide stale error
		hideError();

		// Render tabs and favorites
		renderFieldTabs();
		renderFavorites();

		// Show overlay and modal
		overlayEl.style.display = 'block';
		modalEl.style.display   = 'block';
		document.body.classList.add( 'jse-modal-open' );

		// Init canvas after layout
		setTimeout( function () {
			initCanvas();
			const initialHex = state.current[ state.activeField ] || '#b78acb';
			setColorFromHex( initialHex, 'open' );
		}, 16 );

		// Trap focus
		lastFocusedEl = document.activeElement;
		modalEl.focus();
	}

	/**
	 * Close the modal.
	 *
	 * @param {boolean} restorePreview Restore live preview to original colors.
	 */
	function closeModal( restorePreview ) {
		overlayEl.style.display = 'none';
		modalEl.style.display   = 'none';
		document.body.classList.remove( 'jse-modal-open' );

		if ( restorePreview ) {
			restoreLivePreview();
		}

		if ( lastFocusedEl && lastFocusedEl.focus ) {
			lastFocusedEl.focus();
		}
	}

	// ─── Event binding ────────────────────────────────────────────

	function bindEvents() {
		// Close via × button or overlay click
		document.getElementById( 'jse-close' ).addEventListener( 'click', function () {
			closeModal( true );
		} );
		overlayEl.addEventListener( 'click', function () {
			closeModal( true );
		} );

		// Keyboard handling (ESC + focus trap)
		modalEl.addEventListener( 'keydown', handleModalKeydown );

		// Hue slider
		document.getElementById( 'jse-hue-slider' ).addEventListener( 'input', function ( e ) {
			state.hsv.h = parseFloat( e.target.value );
			syncFromHsv( 'hue' );
		} );

		// HEX input
		const hexInput = document.getElementById( 'jse-hex' );
		hexInput.addEventListener( 'input', function () {
			let v = hexInput.value.trim();
			if ( v.charAt( 0 ) !== '#' ) {
				v = '#' + v;
			}
			if ( isValidHex( v ) ) {
				setColorFromHex( v, 'hex' );
			}
		} );
		hexInput.addEventListener( 'blur', function () {
			let v = hexInput.value.trim();
			if ( v.charAt( 0 ) !== '#' ) {
				v = '#' + v;
			}
			if ( ! isValidHex( v ) ) {
				hexInput.value = state.current[ state.activeField ] || '#000000';
			}
		} );

		// RGB inputs
		[ 'r', 'g', 'b' ].forEach( function ( ch ) {
			document.getElementById( 'jse-' + ch ).addEventListener( 'input', function () {
				const r = parseInt( document.getElementById( 'jse-r' ).value, 10 );
				const g = parseInt( document.getElementById( 'jse-g' ).value, 10 );
				const b = parseInt( document.getElementById( 'jse-b' ).value, 10 );
				if (
					! isNaN( r ) && ! isNaN( g ) && ! isNaN( b ) &&
					r >= 0 && r <= 255 &&
					g >= 0 && g <= 255 &&
					b >= 0 && b <= 255
				) {
					state.hsv = rgbToHsv( r, g, b );
					syncFromHsv( 'rgb' );
				}
			} );
		} );

		// Spectrum – mouse
		specCanvas.addEventListener( 'mousedown', handleSpectrumMousedown );

		// Spectrum – touch
		specCanvas.addEventListener( 'touchstart', handleSpectrumTouchstart, { passive: false } );

		// Spectrum – keyboard (arrow keys)
		specCanvas.addEventListener( 'keydown', handleSpectrumKeydown );

		// Favorites
		document.getElementById( 'jse-add-favorite' ).addEventListener( 'click', addFavorite );

		// Footer buttons
		document.getElementById( 'jse-save' ).addEventListener( 'click', saveColors );
		document.getElementById( 'jse-cancel' ).addEventListener( 'click', function () {
			closeModal( true );
		} );
		document.getElementById( 'jse-reset' ).addEventListener( 'click', resetColors );
	}

	// ─── Spectrum interaction ─────────────────────────────────────

	/**
	 * Pick a color from a canvas position.
	 *
	 * @param {number} clientX
	 * @param {number} clientY
	 */
	function spectrumPickAt( clientX, clientY ) {
		const rect  = specCanvas.getBoundingClientRect();
		const x     = clamp( clientX - rect.left, 0, specWidth );
		const y     = clamp( clientY - rect.top, 0, specHeight );
		state.hsv.s = x / specWidth;
		state.hsv.v = 1 - y / specHeight;
		syncFromHsv( 'spectrum' );
	}

	function handleSpectrumMousedown( e ) {
		e.preventDefault();
		spectrumPickAt( e.clientX, e.clientY );

		function onMove( ev ) {
			spectrumPickAt( ev.clientX, ev.clientY );
		}
		function onUp() {
			document.removeEventListener( 'mousemove', onMove );
			document.removeEventListener( 'mouseup', onUp );
		}
		document.addEventListener( 'mousemove', onMove );
		document.addEventListener( 'mouseup', onUp );
	}

	function handleSpectrumTouchstart( e ) {
		e.preventDefault();
		if ( e.touches.length > 0 ) {
			spectrumPickAt( e.touches[ 0 ].clientX, e.touches[ 0 ].clientY );
		}
		function onMove( ev ) {
			if ( ev.touches.length > 0 ) {
				spectrumPickAt( ev.touches[ 0 ].clientX, ev.touches[ 0 ].clientY );
			}
		}
		function onEnd() {
			specCanvas.removeEventListener( 'touchmove', onMove );
			specCanvas.removeEventListener( 'touchend', onEnd );
		}
		specCanvas.addEventListener( 'touchmove', onMove, { passive: false } );
		specCanvas.addEventListener( 'touchend', onEnd );
	}

	/**
	 * Keyboard control of spectrum canvas.
	 * Arrow keys adjust saturation (←→) and value (↑↓).
	 *
	 * @param {KeyboardEvent} e
	 */
	function handleSpectrumKeydown( e ) {
		const step    = e.shiftKey ? 0.05 : 0.01;
		let changed   = false;

		if ( e.key === 'ArrowRight' ) { state.hsv.s = clamp( state.hsv.s + step, 0, 1 ); changed = true; }
		if ( e.key === 'ArrowLeft'  ) { state.hsv.s = clamp( state.hsv.s - step, 0, 1 ); changed = true; }
		if ( e.key === 'ArrowUp'    ) { state.hsv.v = clamp( state.hsv.v + step, 0, 1 ); changed = true; }
		if ( e.key === 'ArrowDown'  ) { state.hsv.v = clamp( state.hsv.v - step, 0, 1 ); changed = true; }

		if ( changed ) {
			e.preventDefault();
			syncFromHsv( 'spectrum' );
		}
	}

	// ─── Focus trap ───────────────────────────────────────────────

	/**
	 * Handle keyboard events inside the modal: ESC closes, Tab traps focus.
	 *
	 * @param {KeyboardEvent} e
	 */
	function handleModalKeydown( e ) {
		if ( e.key === 'Escape' ) {
			closeModal( true );
			return;
		}
		if ( e.key !== 'Tab' ) {
			return;
		}

		const focusable = Array.prototype.slice.call(
			modalEl.querySelectorAll(
				'button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'
			)
		);
		if ( ! focusable.length ) {
			return;
		}

		const first = focusable[ 0 ];
		const last  = focusable[ focusable.length - 1 ];

		if ( e.shiftKey ) {
			if ( document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			}
		} else {
			if ( document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		}
	}

	// ─── Entry point ──────────────────────────────────────────────

	function init() {
		if ( ! data || ! data.presets ) {
			return;
		}

		buildModal();

		// Hide modal and overlay initially
		document.getElementById( 'jse-overlay' ).style.display = 'none';
		document.getElementById( 'jse-modal' ).style.display   = 'none';

		// Bind "Edit Colors" buttons
		document.querySelectorAll( '.jasanika-open-color-editor' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				const presetId = btn.getAttribute( 'data-preset-id' );
				if ( presetId ) {
					openModal( presetId );
				}
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

}() );
