/**
 * Jasanika Admin – Homepage Builder Background Settings
 *
 * Handles:
 * - Background type radio visibility toggling (show/hide image and overlay fields)
 * - WordPress media library picker for background images (stores attachment ID)
 * - Remove image button
 * - Overlay opacity range → live numeric display
 * - Overlay color native picker ↔ HEX text sync
 */

document.addEventListener( 'DOMContentLoaded', function () {

	// -------------------------------------------------------------------------
	// Background type visibility
	// -------------------------------------------------------------------------

	/**
	 * Show or hide the relevant field groups based on the selected bg type.
	 *
	 * @param {HTMLElement} container The .jasanika-bg-settings element.
	 * @param {string}      type      Selected background type value.
	 */
	function applyBgTypeVisibility( container, type ) {
		var imageRow   = container.querySelector( '.jbg-image-row' );
		var fitRow     = container.querySelector( '.jbg-fit-row' );
		var posRow     = container.querySelector( '.jbg-position-row' );
		var repeatRow  = container.querySelector( '.jbg-repeat-row' );
		var overlayRow = container.querySelector( '.jbg-overlay-row' );

		var hasImage   = ( type === 'image' || type === 'color_image' );
		var hasOverlay = ( type === 'color_image' );

		if ( imageRow )   imageRow.style.display   = hasImage   ? '' : 'none';
		if ( fitRow )     fitRow.style.display     = hasImage   ? '' : 'none';
		if ( posRow )     posRow.style.display     = hasImage   ? '' : 'none';
		if ( repeatRow )  repeatRow.style.display  = hasImage   ? '' : 'none';
		if ( overlayRow ) overlayRow.style.display = hasOverlay ? '' : 'none';
	}

	document.querySelectorAll( '.jasanika-bg-settings' ).forEach( function ( container ) {

		// Bind type radio buttons.
		container.querySelectorAll( '.jbg-type-radio' ).forEach( function ( radio ) {
			radio.addEventListener( 'change', function () {
				applyBgTypeVisibility( container, this.value );
			} );
		} );

		// Apply initial visibility based on saved value.
		var checked = container.querySelector( '.jbg-type-radio:checked' );
		if ( checked ) {
			applyBgTypeVisibility( container, checked.value );
		}

	} );

	// -------------------------------------------------------------------------
	// Background image – media library picker (stores attachment ID)
	// -------------------------------------------------------------------------

	document.querySelectorAll( '.jasanika-bg-select-btn' ).forEach( function ( btn ) {

		btn.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			var idInputId  = btn.getAttribute( 'data-id-target' );
			var previewId  = btn.getAttribute( 'data-preview' );
			var removeBtnId = btn.getAttribute( 'data-remove-btn' );
			var frameTitle = btn.getAttribute( 'data-title' ) || 'Select Background Image';

			var frame = wp.media( {
				title:    frameTitle,
				button:   { text: 'Use this image' },
				multiple: false,
				library:  { type: 'image' },
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();

				var idInput = document.getElementById( idInputId );
				if ( idInput ) {
					idInput.value = attachment.id;
				}

				var previewEl = document.getElementById( previewId );
				if ( previewEl ) {
					var previewUrl = ( attachment.sizes && attachment.sizes.medium )
						? attachment.sizes.medium.url
						: attachment.url;
					previewEl.src           = previewUrl;
					previewEl.style.display = 'block';
				}

				var removeBtn = document.getElementById( removeBtnId );
				if ( removeBtn ) {
					removeBtn.style.display = 'inline-block';
				}
			} );

			frame.open();
		} );

	} );

	// -------------------------------------------------------------------------
	// Background image – remove
	// -------------------------------------------------------------------------

	document.querySelectorAll( '.jasanika-bg-remove-btn' ).forEach( function ( btn ) {

		btn.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			var idInputId = btn.getAttribute( 'data-id-target' );
			var previewId = btn.getAttribute( 'data-preview' );

			var idInput = document.getElementById( idInputId );
			if ( idInput ) {
				idInput.value = '';
			}

			var previewEl = document.getElementById( previewId );
			if ( previewEl ) {
				previewEl.src           = '';
				previewEl.style.display = 'none';
			}

			btn.style.display = 'none';
		} );

	} );

	// -------------------------------------------------------------------------
	// Overlay opacity range → live display
	// -------------------------------------------------------------------------

	document.querySelectorAll( '.jbg-opacity-range' ).forEach( function ( range ) {
		var displayId = range.getAttribute( 'data-display' );
		var display   = displayId ? document.getElementById( displayId ) : null;

		if ( display ) {
			display.textContent = range.value + '%';

			range.addEventListener( 'input', function () {
				display.textContent = this.value + '%';
			} );
		}
	} );

	// -------------------------------------------------------------------------
	// Overlay color – native <input type="color"> ↔ HEX text input sync
	// -------------------------------------------------------------------------

	document.querySelectorAll( '.jbg-overlay-color-native' ).forEach( function ( native ) {
		var textId = native.getAttribute( 'data-text-target' );
		var text   = document.getElementById( textId );

		if ( ! text ) {
			return;
		}

		if ( /^#[0-9a-fA-F]{6}$/.test( text.value ) ) {
			native.value = text.value;
		}

		native.addEventListener( 'input', function () {
			text.value = native.value.toUpperCase();
		} );

		text.addEventListener( 'input', function () {
			if ( /^#[0-9a-fA-F]{6}$/.test( text.value ) ) {
				native.value = text.value;
			}
		} );
	} );

} );
