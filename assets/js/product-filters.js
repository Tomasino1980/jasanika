/**
 * Product Filters — Toggle
 *
 * Handles the collapsible filter panel on tablet and mobile viewports.
 * No AJAX — filter submission is a standard GET form request.
 */
( function () {
	'use strict';

	var BREAKPOINT_DESKTOP = 1024;

	function initFilterToggle() {
		var toggle = document.querySelector( '.product-filters__toggle' );
		var body   = document.getElementById( 'product-filters-body' );

		if ( ! toggle || ! body ) {
			return;
		}

		toggle.addEventListener( 'click', function () {
			var isExpanded = 'true' === this.getAttribute( 'aria-expanded' );
			this.setAttribute( 'aria-expanded', String( ! isExpanded ) );
			body.classList.toggle( 'is-open', ! isExpanded );
		} );
	}

	/**
	 * On desktop (> 1024px) always show the filter body regardless of JS state.
	 * Uses ResizeObserver when available, falls back to window.resize.
	 */
	function initResponsiveBehavior() {
		var body = document.getElementById( 'product-filters-body' );
		if ( ! body ) {
			return;
		}

		function ensureVisible() {
			if ( window.innerWidth > BREAKPOINT_DESKTOP ) {
				body.style.display = '';
			}
		}

		ensureVisible();

		if ( typeof ResizeObserver !== 'undefined' ) {
			var observer = new ResizeObserver( ensureVisible );
			observer.observe( document.documentElement );
		} else {
			window.addEventListener( 'resize', ensureVisible );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initFilterToggle();
		initResponsiveBehavior();
	} );
}() );
