/**
 * Jasanika Admin – Media Uploader
 *
 * Connects "Select Image" buttons to the WordPress media library.
 * Works with any element that has the class `jasanika-media-upload-btn`
 * and a `data-target` attribute pointing to the related text input ID.
 */

document.addEventListener( 'DOMContentLoaded', function () {

	document.querySelectorAll( '.jasanika-media-upload-btn' ).forEach( function ( btn ) {

		btn.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			var targetId  = btn.getAttribute( 'data-target' );
			var previewId = btn.getAttribute( 'data-preview' );
			var frameTitle = btn.getAttribute( 'data-title' ) || 'Select Image';

			var frame = wp.media( {
				title:    frameTitle,
				button:   { text: 'Use this image' },
				multiple: false,
				library:  { type: 'image' },
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var input = document.getElementById( targetId );

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
			} );

			frame.open();
		} );

	} );

} );
