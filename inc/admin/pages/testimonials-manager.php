<?php

/**
 * Jasanika Admin – Testimonials Manager Page
 *
 * Provides a full CRUD interface for managing customer testimonials.
 * Testimonials are stored in the jasanika_testimonials WordPress option.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'jasanika_testimonials_process_actions' );

// ---------------------------------------------------------------------------
// Action Processing
// ---------------------------------------------------------------------------

/**
 * Processes GET and POST testimonial actions before the page renders.
 * Hooked to admin_init so redirects can be issued cleanly.
 */
function jasanika_testimonials_process_actions(): void {
	if ( ! isset( $_GET['page'] ) || 'jasanika-testimonials-manager' !== $_GET['page'] ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$redirect = admin_url( 'admin.php?page=jasanika-testimonials-manager' );

	// --- GET actions: delete, toggle ---
	if ( isset( $_GET['testimonial_action'] ) ) {
		$action         = sanitize_key( $_GET['testimonial_action'] );
		$testimonial_id = isset( $_GET['testimonial_id'] ) ? absint( $_GET['testimonial_id'] ) : 0;

		if ( 'delete' === $action && $testimonial_id > 0 ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'jasanika_testimonial_delete_' . $testimonial_id ) ) {
				wp_die( esc_html__( 'Security check failed.', 'jasanika' ) );
			}
			jasanika_testimonial_delete( $testimonial_id );
			wp_safe_redirect( add_query_arg( 'message', 'deleted', $redirect ) );
			exit;
		}

		if ( 'toggle' === $action && $testimonial_id > 0 ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'jasanika_testimonial_toggle_' . $testimonial_id ) ) {
				wp_die( esc_html__( 'Security check failed.', 'jasanika' ) );
			}
			jasanika_testimonial_toggle( $testimonial_id );
			wp_safe_redirect( add_query_arg( 'message', 'toggled', $redirect ) );
			exit;
		}
	}

	// --- POST actions: add, edit ---
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['jasanika_testimonial_action'] ) ) {
		$post_action = sanitize_key( $_POST['jasanika_testimonial_action'] );

		if ( 'add' === $post_action ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'jasanika_testimonial_add' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'jasanika' ) );
			}
			jasanika_testimonial_save( 0, $_POST );
			wp_safe_redirect( add_query_arg( 'message', 'added', $redirect ) );
			exit;
		}

		if ( 'edit' === $post_action ) {
			$testimonial_id = isset( $_POST['testimonial_id'] ) ? absint( $_POST['testimonial_id'] ) : 0;
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'jasanika_testimonial_edit_' . $testimonial_id ) ) {
				wp_die( esc_html__( 'Security check failed.', 'jasanika' ) );
			}
			if ( $testimonial_id > 0 ) {
				jasanika_testimonial_save( $testimonial_id, $_POST );
				wp_safe_redirect( add_query_arg( 'message', 'updated', $redirect ) );
				exit;
			}
		}
	}
}

// ---------------------------------------------------------------------------
// Data Helpers
// ---------------------------------------------------------------------------

/**
 * Saves a testimonial. If testimonial_id is 0, creates a new one; otherwise updates.
 *
 * @param int   $testimonial_id  0 for new, existing ID for update.
 * @param array $data            Raw POST data.
 */
function jasanika_testimonial_save( int $testimonial_id, array $data ): void {
	$testimonials = get_option( 'jasanika_testimonials', array() );
	if ( ! is_array( $testimonials ) ) {
		$testimonials = array();
	}

	$rating = absint( $data['testimonial_rating'] ?? 5 );
	if ( $rating < 1 ) {
		$rating = 1;
	}
	if ( $rating > 5 ) {
		$rating = 5;
	}

	$sanitized = array(
		'id'         => $testimonial_id,
		'name'       => sanitize_text_field( $data['testimonial_name'] ?? '' ),
		'position'   => sanitize_text_field( $data['testimonial_position'] ?? '' ),
		'text'       => sanitize_textarea_field( $data['testimonial_text'] ?? '' ),
		'image_url'  => esc_url_raw( $data['testimonial_image_url'] ?? '' ),
		'rating'     => $rating,
		'sort_order' => absint( $data['testimonial_sort_order'] ?? 0 ),
		'active'     => isset( $data['testimonial_active'] ) ? 1 : 0,
	);

	if ( 0 === $testimonial_id ) {
		// Auto-increment ID.
		$max_id = 0;
		foreach ( $testimonials as $testimonial ) {
			if ( (int) ( $testimonial['id'] ?? 0 ) > $max_id ) {
				$max_id = (int) $testimonial['id'];
			}
		}
		$sanitized['id'] = $max_id + 1;
		$testimonials[]  = $sanitized;
	} else {
		$found = false;
		foreach ( $testimonials as &$testimonial ) {
			if ( (int) ( $testimonial['id'] ?? 0 ) === $testimonial_id ) {
				$testimonial = $sanitized;
				$found       = true;
				break;
			}
		}
		unset( $testimonial );
		if ( ! $found ) {
			$testimonials[] = $sanitized;
		}
	}

	update_option( 'jasanika_testimonials', $testimonials );
}

/**
 * Deletes a testimonial by ID.
 *
 * @param int $testimonial_id
 */
function jasanika_testimonial_delete( int $testimonial_id ): void {
	$testimonials = get_option( 'jasanika_testimonials', array() );
	if ( ! is_array( $testimonials ) ) {
		return;
	}
	$testimonials = array_values(
		array_filter(
			$testimonials,
			function ( array $testimonial ) use ( $testimonial_id ): bool {
				return (int) ( $testimonial['id'] ?? 0 ) !== $testimonial_id;
			}
		)
	);
	update_option( 'jasanika_testimonials', $testimonials );
}

/**
 * Toggles the active status of a testimonial by ID.
 *
 * @param int $testimonial_id
 */
function jasanika_testimonial_toggle( int $testimonial_id ): void {
	$testimonials = get_option( 'jasanika_testimonials', array() );
	if ( ! is_array( $testimonials ) ) {
		return;
	}
	foreach ( $testimonials as &$testimonial ) {
		if ( (int) ( $testimonial['id'] ?? 0 ) === $testimonial_id ) {
			$testimonial['active'] = empty( $testimonial['active'] ) ? 1 : 0;
			break;
		}
	}
	unset( $testimonial );
	update_option( 'jasanika_testimonials', $testimonials );
}

/**
 * Returns a single testimonial by ID, or null if not found.
 *
 * @param int $testimonial_id
 * @return array|null
 */
function jasanika_testimonial_get_by_id( int $testimonial_id ): ?array {
	$testimonials = get_option( 'jasanika_testimonials', array() );
	if ( ! is_array( $testimonials ) ) {
		return null;
	}
	foreach ( $testimonials as $testimonial ) {
		if ( (int) ( $testimonial['id'] ?? 0 ) === $testimonial_id ) {
			return $testimonial;
		}
	}
	return null;
}

// ---------------------------------------------------------------------------
// Page Renderer
// ---------------------------------------------------------------------------

/**
 * Renders the Testimonials Manager admin page.
 */
function jasanika_admin_page_testimonials_manager(): void {
	$message        = isset( $_GET['message'] ) ? sanitize_key( $_GET['message'] ) : '';
	$action         = isset( $_GET['testimonial_action'] ) ? sanitize_key( $_GET['testimonial_action'] ) : 'list';
	$testimonial_id = isset( $_GET['testimonial_id'] ) ? absint( $_GET['testimonial_id'] ) : 0;
	?>
	<div class="wrap">

		<h1><?php esc_html_e( 'Jasanika – Testimonials Manager', 'jasanika' ); ?></h1>

		<?php jasanika_testimonials_render_notices( $message ); ?>

		<?php if ( 'edit' === $action && $testimonial_id > 0 ) : ?>
			<?php jasanika_testimonials_render_edit_form( $testimonial_id ); ?>
		<?php else : ?>
			<?php jasanika_testimonials_render_list(); ?>
			<hr>
			<?php jasanika_testimonials_render_add_form(); ?>
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
function jasanika_testimonials_render_notices( string $message ): void {
	$labels = array(
		'added'   => __( 'Testimonial added successfully.', 'jasanika' ),
		'updated' => __( 'Testimonial updated successfully.', 'jasanika' ),
		'deleted' => __( 'Testimonial deleted successfully.', 'jasanika' ),
		'toggled' => __( 'Testimonial status updated.', 'jasanika' ),
	);

	if ( isset( $labels[ $message ] ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $labels[ $message ] ); ?></p>
		</div>
		<?php
	}
}

// ---------------------------------------------------------------------------
// List View
// ---------------------------------------------------------------------------

/**
 * Renders the testimonials list table.
 */
function jasanika_testimonials_render_list(): void {
	$testimonials = jasanika_get_testimonials();
	$base_url     = admin_url( 'admin.php?page=jasanika-testimonials-manager' );
	?>
	<h2><?php esc_html_e( 'Testimonials', 'jasanika' ); ?></h2>

	<?php if ( empty( $testimonials ) ) : ?>
		<p><?php esc_html_e( 'No testimonials found. Add your first testimonial below.', 'jasanika' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col" style="width:60px;"><?php esc_html_e( 'Order', 'jasanika' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Customer', 'jasanika' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Position', 'jasanika' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Text', 'jasanika' ); ?></th>
					<th scope="col" style="width:80px;"><?php esc_html_e( 'Rating', 'jasanika' ); ?></th>
					<th scope="col" style="width:80px;"><?php esc_html_e( 'Active', 'jasanika' ); ?></th>
					<th scope="col" style="width:200px;"><?php esc_html_e( 'Actions', 'jasanika' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $testimonials as $testimonial ) : ?>
					<?php
					$id         = (int) ( $testimonial['id'] ?? 0 );
					$is_active  = ! empty( $testimonial['active'] );
					$edit_url   = add_query_arg( array( 'testimonial_action' => 'edit', 'testimonial_id' => $id ), $base_url );
					$toggle_url = wp_nonce_url(
						add_query_arg( array( 'testimonial_action' => 'toggle', 'testimonial_id' => $id ), $base_url ),
						'jasanika_testimonial_toggle_' . $id
					);
					$delete_url = wp_nonce_url(
						add_query_arg( array( 'testimonial_action' => 'delete', 'testimonial_id' => $id ), $base_url ),
						'jasanika_testimonial_delete_' . $id
					);
					$rating     = (int) ( $testimonial['rating'] ?? 5 );
					$stars      = str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating );
					?>
					<tr>
						<td><?php echo esc_html( $testimonial['sort_order'] ?? 0 ); ?></td>
						<td>
							<strong>
								<a href="<?php echo esc_url( $edit_url ); ?>">
									<?php echo esc_html( $testimonial['name'] ?: __( '(no name)', 'jasanika' ) ); ?>
								</a>
							</strong>
						</td>
						<td><?php echo esc_html( $testimonial['position'] ?? '' ); ?></td>
						<td><?php echo esc_html( wp_trim_words( $testimonial['text'] ?? '', 10, '…' ) ); ?></td>
						<td style="color:#f1c95d;"><?php echo esc_html( $stars ); ?></td>
						<td>
							<?php if ( $is_active ) : ?>
								<span style="color:#46b450;">&#9679; <?php esc_html_e( 'Yes', 'jasanika' ); ?></span>
							<?php else : ?>
								<span style="color:#dc3232;">&#9679; <?php esc_html_e( 'No', 'jasanika' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'jasanika' ); ?></a>
							&nbsp;|&nbsp;
							<a href="<?php echo esc_url( $toggle_url ); ?>">
								<?php echo $is_active ? esc_html__( 'Disable', 'jasanika' ) : esc_html__( 'Enable', 'jasanika' ); ?>
							</a>
							&nbsp;|&nbsp;
							<a
								href="<?php echo esc_url( $delete_url ); ?>"
								onclick="return confirm( '<?php esc_attr_e( 'Are you sure you want to delete this testimonial?', 'jasanika' ); ?>' )"
								style="color:#dc3232;"
							><?php esc_html_e( 'Delete', 'jasanika' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<?php
}

// ---------------------------------------------------------------------------
// Add Form
// ---------------------------------------------------------------------------

/**
 * Renders the Add New Testimonial form.
 */
function jasanika_testimonials_render_add_form(): void {
	?>
	<h2><?php esc_html_e( 'Add New Testimonial', 'jasanika' ); ?></h2>
	<form method="post" action="">
		<?php wp_nonce_field( 'jasanika_testimonial_add', '_wpnonce' ); ?>
		<input type="hidden" name="jasanika_testimonial_action" value="add">
		<?php jasanika_testimonials_render_form_fields( array() ); ?>
		<p class="submit">
			<input
				type="submit"
				class="button button-primary"
				value="<?php esc_attr_e( 'Add Testimonial', 'jasanika' ); ?>"
			>
		</p>
	</form>
	<?php
}

// ---------------------------------------------------------------------------
// Edit Form
// ---------------------------------------------------------------------------

/**
 * Renders the Edit Testimonial form for the given testimonial ID.
 *
 * @param int $testimonial_id
 */
function jasanika_testimonials_render_edit_form( int $testimonial_id ): void {
	$testimonial = jasanika_testimonial_get_by_id( $testimonial_id );
	$base_url    = admin_url( 'admin.php?page=jasanika-testimonials-manager' );

	if ( null === $testimonial ) {
		echo '<p>' . esc_html__( 'Testimonial not found.', 'jasanika' ) . '</p>';
		echo '<p><a href="' . esc_url( $base_url ) . '">' . esc_html__( '← Back to Testimonials', 'jasanika' ) . '</a></p>';
		return;
	}
	?>
	<h2><?php esc_html_e( 'Edit Testimonial', 'jasanika' ); ?></h2>
	<p>
		<a href="<?php echo esc_url( $base_url ); ?>">&larr; <?php esc_html_e( 'Back to Testimonials', 'jasanika' ); ?></a>
	</p>
	<form method="post" action="">
		<?php wp_nonce_field( 'jasanika_testimonial_edit_' . $testimonial_id, '_wpnonce' ); ?>
		<input type="hidden" name="jasanika_testimonial_action" value="edit">
		<input type="hidden" name="testimonial_id" value="<?php echo esc_attr( $testimonial_id ); ?>">
		<?php jasanika_testimonials_render_form_fields( $testimonial ); ?>
		<p class="submit">
			<input
				type="submit"
				class="button button-primary"
				value="<?php esc_attr_e( 'Update Testimonial', 'jasanika' ); ?>"
			>
			<a href="<?php echo esc_url( $base_url ); ?>" class="button">
				<?php esc_html_e( 'Cancel', 'jasanika' ); ?>
			</a>
		</p>
	</form>
	<?php
}

// ---------------------------------------------------------------------------
// Shared Form Fields
// ---------------------------------------------------------------------------

/**
 * Renders the shared form fields used by both add and edit forms.
 *
 * @param array $testimonial  Testimonial data for pre-population; empty array for a new entry.
 */
function jasanika_testimonials_render_form_fields( array $testimonial ): void {
	$name       = esc_attr( $testimonial['name'] ?? '' );
	$position   = esc_attr( $testimonial['position'] ?? '' );
	$text       = esc_textarea( $testimonial['text'] ?? '' );
	$image_url  = esc_attr( $testimonial['image_url'] ?? '' );
	$rating     = isset( $testimonial['rating'] ) ? (int) $testimonial['rating'] : 5;
	$sort_order = isset( $testimonial['sort_order'] ) ? (int) $testimonial['sort_order'] : 0;
	$active     = ! empty( $testimonial['active'] );
	?>
	<table class="form-table" role="presentation">

		<tr>
			<th scope="row">
				<label for="testimonial_name"><?php esc_html_e( 'Customer Name', 'jasanika' ); ?></label>
			</th>
			<td>
				<input
					type="text"
					id="testimonial_name"
					name="testimonial_name"
					value="<?php echo $name; ?>"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'Jana Nováková', 'jasanika' ); ?>"
				>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="testimonial_position"><?php esc_html_e( 'Position / Label', 'jasanika' ); ?></label>
			</th>
			<td>
				<input
					type="text"
					id="testimonial_position"
					name="testimonial_position"
					value="<?php echo $position; ?>"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'Spokojená zákaznice', 'jasanika' ); ?>"
				>
				<p class="description"><?php esc_html_e( 'E.g. loyal customer, craft enthusiast, etc.', 'jasanika' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="testimonial_text"><?php esc_html_e( 'Testimonial Text', 'jasanika' ); ?></label>
			</th>
			<td>
				<textarea
					id="testimonial_text"
					name="testimonial_text"
					class="large-text"
					rows="4"
					placeholder="<?php esc_attr_e( 'Nádherné ručně vyráběné výrobky, které potěší každého…', 'jasanika' ); ?>"
				><?php echo $text; ?></textarea>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="testimonial_image_url"><?php esc_html_e( 'Customer Image URL', 'jasanika' ); ?></label>
			</th>
			<td>
				<input
					type="url"
					id="testimonial_image_url"
					name="testimonial_image_url"
					value="<?php echo $image_url; ?>"
					class="large-text"
					placeholder="https://"
				>
				<?php if ( $image_url ) : ?>
					<p>
						<img
							src="<?php echo esc_url( $testimonial['image_url'] ); ?>"
							alt=""
							style="max-width:80px;max-height:80px;border-radius:50%;margin-top:6px;display:block;"
						>
					</p>
				<?php endif; ?>
				<p class="description"><?php esc_html_e( 'Optional customer avatar image.', 'jasanika' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="testimonial_rating"><?php esc_html_e( 'Rating', 'jasanika' ); ?></label>
			</th>
			<td>
				<select id="testimonial_rating" name="testimonial_rating">
					<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
						<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $rating, $i ); ?>>
							<?php echo esc_html( str_repeat( '★', $i ) . str_repeat( '☆', 5 - $i ) . ' (' . $i . ')' ); ?>
						</option>
					<?php endfor; ?>
				</select>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="testimonial_sort_order"><?php esc_html_e( 'Sort Order', 'jasanika' ); ?></label>
			</th>
			<td>
				<input
					type="number"
					id="testimonial_sort_order"
					name="testimonial_sort_order"
					value="<?php echo esc_attr( $sort_order ); ?>"
					class="small-text"
					min="0"
					step="1"
				>
				<p class="description"><?php esc_html_e( 'Lower number = displayed first.', 'jasanika' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row"><?php esc_html_e( 'Active', 'jasanika' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="testimonial_active" value="1" <?php checked( $active ); ?>>
					<?php esc_html_e( 'Display this testimonial on the homepage', 'jasanika' ); ?>
				</label>
			</td>
		</tr>

	</table>
	<?php
}
