<?php

/**
 * Jasanika Admin – Newsletter Manager Page
 *
 * Provides a management interface for newsletter subscribers.
 * Subscribers are stored in the jasanika_newsletter_subscribers WordPress option.
 *
 * Features:
 * - View all subscribers (email, date, status)
 * - Delete individual subscribers
 * - Export all subscribers to CSV
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'jasanika_newsletter_process_actions' );

// ---------------------------------------------------------------------------
// Action Processing
// ---------------------------------------------------------------------------

/**
 * Processes GET actions (delete, export) before the page renders.
 * Hooked to admin_init so redirects and file downloads can be issued cleanly.
 */
function jasanika_newsletter_process_actions(): void {
	if ( ! isset( $_GET['page'] ) || 'jasanika-newsletter-manager' !== $_GET['page'] ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! isset( $_GET['newsletter_action'] ) ) {
		return;
	}

	$action = sanitize_key( $_GET['newsletter_action'] );

	// --- Delete subscriber ---
	if ( 'delete' === $action ) {
		$subscriber_id = isset( $_GET['subscriber_id'] ) ? absint( $_GET['subscriber_id'] ) : 0;

		if ( $subscriber_id < 1 ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'jasanika_newsletter_delete_' . $subscriber_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'jasanika' ) );
		}

		jasanika_newsletter_delete_subscriber( $subscriber_id );

		wp_safe_redirect(
			add_query_arg( 'message', 'deleted', admin_url( 'admin.php?page=jasanika-newsletter-manager' ) )
		);
		exit;
	}

	// --- Export to CSV ---
	if ( 'export' === $action ) {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'jasanika_newsletter_export' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'jasanika' ) );
		}

		jasanika_newsletter_export_csv();
		exit;
	}
}

// ---------------------------------------------------------------------------
// CSV Export
// ---------------------------------------------------------------------------

/**
 * Outputs all subscribers as a CSV file download.
 */
function jasanika_newsletter_export_csv(): void {
	$subscribers = jasanika_newsletter_get_subscribers();
	$filename    = 'jasanika-newsletter-subscribers-' . gmdate( 'Y-m-d' ) . '.csv';

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	$output = fopen( 'php://output', 'w' );

	// BOM for Excel UTF-8 compatibility.
	fputs( $output, "\xEF\xBB\xBF" );

	// Header row.
	fputcsv( $output, array( 'Email', 'Date', 'Status' ) );

	// Data rows.
	foreach ( $subscribers as $subscriber ) {
		fputcsv(
			$output,
			array(
				$subscriber['email']  ?? '',
				$subscriber['date']   ?? '',
				$subscriber['status'] ?? '',
			)
		);
	}

	fclose( $output );
}

// ---------------------------------------------------------------------------
// Page Renderer
// ---------------------------------------------------------------------------

/**
 * Renders the Newsletter Manager admin page.
 */
function jasanika_admin_page_newsletter_manager(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$message     = isset( $_GET['message'] ) ? sanitize_key( $_GET['message'] ) : '';
	$subscribers = jasanika_newsletter_get_subscribers();
	$base_url    = admin_url( 'admin.php?page=jasanika-newsletter-manager' );
	$export_url  = wp_nonce_url(
		add_query_arg( 'newsletter_action', 'export', $base_url ),
		'jasanika_newsletter_export'
	);
	?>
	<div class="wrap">

		<h1><?php esc_html_e( 'Jasanika – Newsletter Manager', 'jasanika' ); ?></h1>

		<?php jasanika_newsletter_render_notices( $message ); ?>

		<p>
			<a href="<?php echo esc_url( $export_url ); ?>" class="button button-secondary">
				<?php esc_html_e( 'Export Subscribers (CSV)', 'jasanika' ); ?>
			</a>
		</p>

		<h2><?php esc_html_e( 'Subscribers', 'jasanika' ); ?></h2>

		<?php if ( empty( $subscribers ) ) : ?>

			<p><?php esc_html_e( 'No subscribers yet.', 'jasanika' ); ?></p>

		<?php else : ?>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Email', 'jasanika' ); ?></th>
						<th scope="col" style="width:200px;"><?php esc_html_e( 'Subscription Date', 'jasanika' ); ?></th>
						<th scope="col" style="width:100px;"><?php esc_html_e( 'Status', 'jasanika' ); ?></th>
						<th scope="col" style="width:100px;"><?php esc_html_e( 'Actions', 'jasanika' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $subscribers as $subscriber ) :
						$id         = (int) ( $subscriber['id'] ?? 0 );
						$email      = $subscriber['email']  ?? '';
						$date       = $subscriber['date']   ?? '';
						$status     = $subscriber['status'] ?? 'active';
						$delete_url = wp_nonce_url(
							add_query_arg(
								array(
									'newsletter_action' => 'delete',
									'subscriber_id'     => $id,
								),
								$base_url
							),
							'jasanika_newsletter_delete_' . $id
						);
					?>
					<tr>
						<td><strong><?php echo esc_html( $email ?: __( '(no email)', 'jasanika' ) ); ?></strong></td>
						<td><?php echo esc_html( $date ); ?></td>
						<td>
							<?php if ( 'active' === $status ) : ?>
								<span style="color:#46b450;">&#9679; <?php esc_html_e( 'Active', 'jasanika' ); ?></span>
							<?php else : ?>
								<span style="color:#dc3232;">&#9679; <?php echo esc_html( ucfirst( $status ) ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<a
								href="<?php echo esc_url( $delete_url ); ?>"
								onclick="return confirm( '<?php esc_attr_e( 'Are you sure you want to delete this subscriber?', 'jasanika' ); ?>' )"
								style="color:#dc3232;"
							><?php esc_html_e( 'Delete', 'jasanika' ); ?></a>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p class="description" style="margin-top:12px;">
				<?php
				printf(
					/* translators: %d: number of subscribers */
					esc_html( _n( '%d subscriber', '%d subscribers', count( $subscribers ), 'jasanika' ) ),
					count( $subscribers )
				);
				?>
			</p>

		<?php endif; ?>

	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Notices
// ---------------------------------------------------------------------------

/**
 * Renders an admin notice based on the message query parameter.
 *
 * @param string $message
 */
function jasanika_newsletter_render_notices( string $message ): void {
	$labels = array(
		'deleted' => __( 'Subscriber deleted successfully.', 'jasanika' ),
	);

	if ( isset( $labels[ $message ] ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $labels[ $message ] ); ?></p>
		</div>
		<?php
	}
}
