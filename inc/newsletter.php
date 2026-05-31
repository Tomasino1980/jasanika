<?php

/**
 * Jasanika – Newsletter
 *
 * Subscriber storage, AJAX handler, and data helper functions.
 * Subscribers are stored in the jasanika_newsletter_subscribers WordPress option.
 *
 * Architecture is designed for future integration with Mailchimp, Brevo,
 * SendGrid or SMTP systems. No external integrations in this milestone.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Option Key Constant
// ---------------------------------------------------------------------------

define( 'JASANIKA_NEWSLETTER_OPTION', 'jasanika_newsletter_subscribers' );

// ---------------------------------------------------------------------------
// Data Helpers
// ---------------------------------------------------------------------------

/**
 * Return all newsletter subscribers.
 *
 * @return array<int, array{ id: int, email: string, date: string, status: string }>
 */
function jasanika_newsletter_get_subscribers(): array {
	$data = get_option( JASANIKA_NEWSLETTER_OPTION, array() );

	return is_array( $data ) ? $data : array();
}

/**
 * Check whether an email address is already subscribed.
 *
 * @param string $email
 * @return bool
 */
function jasanika_newsletter_email_exists( string $email ): bool {
	$email       = strtolower( trim( $email ) );
	$subscribers = jasanika_newsletter_get_subscribers();

	foreach ( $subscribers as $subscriber ) {
		if ( strtolower( trim( $subscriber['email'] ?? '' ) ) === $email ) {
			return true;
		}
	}

	return false;
}

/**
 * Store a new subscriber.
 *
 * @param string $email Sanitized and validated email address.
 * @return bool True on success, false on failure.
 */
function jasanika_newsletter_add_subscriber( string $email ): bool {
	$email = sanitize_email( $email );

	if ( ! is_email( $email ) ) {
		return false;
	}

	$subscribers = jasanika_newsletter_get_subscribers();

	// Auto-increment ID.
	$max_id = 0;
	foreach ( $subscribers as $subscriber ) {
		if ( (int) ( $subscriber['id'] ?? 0 ) > $max_id ) {
			$max_id = (int) $subscriber['id'];
		}
	}

	$subscribers[] = array(
		'id'     => $max_id + 1,
		'email'  => $email,
		'date'   => gmdate( 'Y-m-d H:i:s' ),
		'status' => 'active',
	);

	return (bool) update_option( JASANIKA_NEWSLETTER_OPTION, $subscribers );
}

/**
 * Delete a subscriber by ID.
 *
 * @param int $id
 * @return bool
 */
function jasanika_newsletter_delete_subscriber( int $id ): bool {
	$subscribers = jasanika_newsletter_get_subscribers();

	$filtered = array_values(
		array_filter(
			$subscribers,
			function ( array $s ) use ( $id ): bool {
				return (int) ( $s['id'] ?? 0 ) !== $id;
			}
		)
	);

	if ( count( $filtered ) === count( $subscribers ) ) {
		return false;
	}

	return (bool) update_option( JASANIKA_NEWSLETTER_OPTION, $filtered );
}

// ---------------------------------------------------------------------------
// AJAX Handler
// ---------------------------------------------------------------------------

add_action( 'wp_ajax_jasanika_newsletter_subscribe',        'jasanika_ajax_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_jasanika_newsletter_subscribe', 'jasanika_ajax_newsletter_subscribe' );

/**
 * Handle newsletter subscription AJAX request.
 */
function jasanika_ajax_newsletter_subscribe(): void {

	// Verify nonce.
	$nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );

	if ( ! wp_verify_nonce( $nonce, 'jasanika_newsletter_subscribe' ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Security check failed. Please reload the page and try again.', 'jasanika' ) )
		);
	}

	// Validate consent.
	if ( empty( $_POST['consent'] ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Please accept the privacy policy to subscribe.', 'jasanika' ) )
		);
	}

	// Validate email.
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

	if ( ! $email || ! is_email( $email ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Please enter a valid email address.', 'jasanika' ) )
		);
	}

	// Duplicate check.
	if ( jasanika_newsletter_email_exists( $email ) ) {
		wp_send_json_error(
			array( 'message' => __( 'This email address is already subscribed.', 'jasanika' ) )
		);
	}

	// Store subscriber.
	if ( ! jasanika_newsletter_add_subscriber( $email ) ) {
		wp_send_json_error(
			array( 'message' => __( 'An error occurred. Please try again later.', 'jasanika' ) )
		);
	}

	$success_message = jasanika_get_newsletter_success();

	wp_send_json_success( array( 'message' => $success_message ) );
}
