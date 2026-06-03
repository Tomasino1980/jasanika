/**
 * homepage-builder-admin.js – Tab switching for Homepage Builder admin page.
 */

( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		const tabButtons = document.querySelectorAll( '.jasanika-hb-tab-btn' );
		const panels     = document.querySelectorAll( '.jasanika-hb-panel' );

		tabButtons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				const target = this.dataset.tab;

				tabButtons.forEach( function ( b ) {
					b.classList.remove( 'active' );
				} );
				panels.forEach( function ( p ) {
					p.classList.remove( 'active' );
				} );

				this.classList.add( 'active' );
				const panel = document.getElementById( 'jshb-panel-' + target );
				if ( panel ) {
					panel.classList.add( 'active' );
				}
			} );
		} );

	} );

} )();
