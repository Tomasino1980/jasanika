/**
 * cookie-consent.js – Cookie Consent Manager
 *
 * Handles the cookie consent banner and preferences modal.
 * No external libraries.
 */

( function () {

	'use strict';

	// -------------------------------------------------------------------------
	// Configuration (injected by wp_localize_script as jasanikaCookieConsent)
	// -------------------------------------------------------------------------

	var config = window.jasanikaCookieConsent || {};
	var COOKIE_NAME   = 'jasanika_cookie_consent';
	var COOKIE_EXPIRY = parseInt( config.expiration, 10 ) || 365;
	var CONSENT_VER   = config.version || '1';

	// -------------------------------------------------------------------------
	// DOM References
	// -------------------------------------------------------------------------

	var banner         = document.getElementById( 'jasanika-cookie-banner' );
	var modal          = document.getElementById( 'jasanika-cookie-modal' );
	var overlay        = document.getElementById( 'jasanika-cookie-modal-overlay' );
	var btnAcceptAll   = document.getElementById( 'jasanika-cookie-accept-all' );
	var btnReject      = document.getElementById( 'jasanika-cookie-reject' );
	var btnPreferences = document.getElementById( 'jasanika-cookie-preferences-open' );
	var btnModalClose  = document.getElementById( 'jasanika-cookie-modal-close' );
	var btnSavePrefs   = document.getElementById( 'jasanika-cookie-save-prefs' );
	var chkAnalytics   = document.getElementById( 'cookie-pref-analytics' );
	var chkMarketing   = document.getElementById( 'cookie-pref-marketing' );

	// -------------------------------------------------------------------------
	// Cookie Utilities
	// -------------------------------------------------------------------------

	/**
	 * Read a cookie by name.
	 *
	 * @param {string} name
	 * @returns {string|null}
	 */
	function getCookie( name ) {
		var pairs = document.cookie.split( ';' );
		for ( var i = 0; i < pairs.length; i++ ) {
			var pair = pairs[ i ].trim().split( '=' );
			if ( decodeURIComponent( pair[ 0 ] ) === name ) {
				return decodeURIComponent( pair.slice( 1 ).join( '=' ) );
			}
		}
		return null;
	}

	/**
	 * Write a cookie.
	 *
	 * @param {string} name
	 * @param {string} value
	 * @param {number} days
	 */
	function setCookie( name, value, days ) {
		var expires = '';
		if ( days ) {
			var date = new Date();
			date.setTime( date.getTime() + days * 24 * 60 * 60 * 1000 );
			expires = '; expires=' + date.toUTCString();
		}
		document.cookie = encodeURIComponent( name )
			+ '=' + encodeURIComponent( value )
			+ expires
			+ '; path=/; SameSite=Lax';
	}

	// -------------------------------------------------------------------------
	// Consent Storage
	// -------------------------------------------------------------------------

	/**
	 * Save consent choices as a JSON cookie.
	 *
	 * @param {boolean} analytics
	 * @param {boolean} marketing
	 */
	function saveConsent( analytics, marketing ) {
		var consent = {
			date:      new Date().toISOString(),
			version:   CONSENT_VER,
			necessary: true,
			analytics: analytics,
			marketing: marketing
		};
		setCookie( COOKIE_NAME, JSON.stringify( consent ), COOKIE_EXPIRY );
	}

	/**
	 * Parse stored consent cookie.
	 *
	 * @returns {Object|null}
	 */
	function getStoredConsent() {
		var raw = getCookie( COOKIE_NAME );
		if ( ! raw ) {
			return null;
		}
		try {
			return JSON.parse( raw );
		} catch ( e ) {
			return null;
		}
	}

	// -------------------------------------------------------------------------
	// Banner Visibility
	// -------------------------------------------------------------------------

	/**
	 * Hide the consent banner.
	 */
	function hideBanner() {
		if ( ! banner ) {
			return;
		}
		banner.classList.add( 'is-hidden' );
		banner.setAttribute( 'aria-hidden', 'true' );
	}

	/**
	 * Show the consent banner.
	 */
	function showBanner() {
		if ( ! banner ) {
			return;
		}
		banner.classList.remove( 'is-hidden' );
		banner.removeAttribute( 'aria-hidden' );
	}

	// -------------------------------------------------------------------------
	// Preferences Modal
	// -------------------------------------------------------------------------

	/**
	 * Open the preferences modal.
	 */
	function openModal() {
		if ( ! modal ) {
			return;
		}

		// Pre-fill checkboxes from stored consent (if any).
		var stored = getStoredConsent();
		if ( stored ) {
			if ( chkAnalytics ) {
				chkAnalytics.checked = !! stored.analytics;
			}
			if ( chkMarketing ) {
				chkMarketing.checked = !! stored.marketing;
			}
		}

		modal.removeAttribute( 'hidden' );
		modal.removeAttribute( 'aria-hidden' );
		document.body.style.overflow = 'hidden';

		// Move focus to modal title for accessibility.
		var title = document.getElementById( 'cookie-modal-title' );
		if ( title ) {
			title.setAttribute( 'tabindex', '-1' );
			title.focus();
		}
	}

	/**
	 * Close the preferences modal.
	 */
	function closeModal() {
		if ( ! modal ) {
			return;
		}
		modal.setAttribute( 'hidden', '' );
		modal.setAttribute( 'aria-hidden', 'true' );
		document.body.style.overflow = '';
	}

	// -------------------------------------------------------------------------
	// User Actions
	// -------------------------------------------------------------------------

	/**
	 * Accept all cookie categories.
	 */
	function acceptAll() {
		saveConsent( true, true );
		hideBanner();
		closeModal();
	}

	/**
	 * Reject optional cookies (necessary only).
	 */
	function rejectOptional() {
		saveConsent( false, false );
		hideBanner();
		closeModal();
	}

	/**
	 * Save the user's custom preferences from the modal.
	 */
	function savePreferences() {
		var analytics = chkAnalytics ? chkAnalytics.checked : false;
		var marketing = chkMarketing ? chkMarketing.checked : false;
		saveConsent( analytics, marketing );
		hideBanner();
		closeModal();
	}

	// -------------------------------------------------------------------------
	// Keyboard Trap (Modal Accessibility)
	// -------------------------------------------------------------------------

	/**
	 * Trap focus inside the modal while it is open.
	 *
	 * @param {KeyboardEvent} e
	 */
	function trapFocus( e ) {
		if ( ! modal || modal.hasAttribute( 'hidden' ) ) {
			return;
		}

		var focusable = modal.querySelectorAll(
			'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
		);

		if ( ! focusable.length ) {
			return;
		}

		var first = focusable[ 0 ];
		var last  = focusable[ focusable.length - 1 ];

		if ( e.key === 'Tab' ) {
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

		if ( e.key === 'Escape' ) {
			closeModal();
		}
	}

	// -------------------------------------------------------------------------
	// Initialisation
	// -------------------------------------------------------------------------

	function init() {
		if ( ! banner ) {
			return;
		}

		// If user already has a consent cookie, hide the banner immediately.
		if ( getCookie( COOKIE_NAME ) ) {
			hideBanner();
			return;
		}

		// Wire up buttons.
		if ( btnAcceptAll ) {
			btnAcceptAll.addEventListener( 'click', acceptAll );
		}

		if ( btnReject ) {
			btnReject.addEventListener( 'click', rejectOptional );
		}

		if ( btnPreferences ) {
			btnPreferences.addEventListener( 'click', openModal );
		}

		if ( btnModalClose ) {
			btnModalClose.addEventListener( 'click', closeModal );
		}

		if ( overlay ) {
			overlay.addEventListener( 'click', closeModal );
		}

		if ( btnSavePrefs ) {
			btnSavePrefs.addEventListener( 'click', savePreferences );
		}

		// Keyboard handling.
		document.addEventListener( 'keydown', trapFocus );
	}

	// Run after DOM is ready.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
