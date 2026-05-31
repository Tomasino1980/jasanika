/**
 * Jasanika Admin – Media Uploader & Colour Picker
 *
 * Connects "Select Image" / "Remove Image" buttons to the WordPress media
 * library and syncs the native colour picker with the HEX text inputs.
 */

document.addEventListener( 'DOMContentLoaded', function () {

	// -------------------------------------------------------------------------
	// Media uploader – Select Image
	// -------------------------------------------------------------------------

	document.querySelectorAll( '.jasanika-media-upload-btn' ).forEach( function ( btn ) {

		btn.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			var targetId   = btn.getAttribute( 'data-target' );
			var previewId  = btn.getAttribute( 'data-preview' );
			var frameTitle = btn.getAttribute( 'data-title' ) || 'Select Image';

			var frame = wp.media( {
				title:    frameTitle,
				button:   { text: 'Use this image' },
				multiple: false,
				library:  { type: 'image' },
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var input      = document.getElementById( targetId );

				if ( input ) {
					input.value = attachment.url;
				}

				if ( previewId ) {
					var preview = document.getElementById( previewId );
					if ( preview ) {
						preview.src           = attachment.url;
						preview.style.display = 'block';
					}
				}

				// Show the remove button if present.
				var removeBtn = document.querySelector( '[data-remove="' + targetId + '"]' );
				if ( removeBtn ) {
					removeBtn.style.display = 'inline-block';
				}
			} );

			frame.open();
		} );

	} );

	// -------------------------------------------------------------------------
	// Media uploader – Remove Image
	// -------------------------------------------------------------------------

	document.querySelectorAll( '.jasanika-media-remove-btn' ).forEach( function ( btn ) {

		btn.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			var targetId  = btn.getAttribute( 'data-remove' );
			var previewId = btn.getAttribute( 'data-preview' );

			var input = document.getElementById( targetId );
			if ( input ) {
				input.value = '';
			}

			if ( previewId ) {
				var preview = document.getElementById( previewId );
				if ( preview ) {
					preview.src           = '';
					preview.style.display = 'none';
				}
			}

			btn.style.display = 'none';
		} );

	} );

	// -------------------------------------------------------------------------
	// Colour picker – sync native <input type="color"> with the HEX text input
	// -------------------------------------------------------------------------

	document.querySelectorAll( '.jasanika-color-native' ).forEach( function ( native ) {

		var textId = native.getAttribute( 'data-text-target' );
		var text   = document.getElementById( textId );

		if ( ! text ) {
			return;
		}

		// Initialise native picker from the current text value.
		if ( /^#[0-9a-fA-F]{6}$/.test( text.value ) ) {
			native.value = text.value;
		}

		// Native picker → text input.
		native.addEventListener( 'input', function () {
			text.value = native.value.toUpperCase();
		} );

		// Text input → native picker (only when valid HEX).
		text.addEventListener( 'input', function () {
			if ( /^#[0-9a-fA-F]{6}$/.test( text.value ) ) {
				native.value = text.value;
			}
		} );

	} );

} );
