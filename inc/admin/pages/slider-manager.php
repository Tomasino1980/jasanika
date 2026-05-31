<?php

/**
 * Jasanika Admin – Slider Manager Page
 *
 * Provides a full CRUD interface for managing hero slider slides.
 * Slides are stored in the jasanika_slides WordPress option.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'jasanika_slider_process_actions' );

// ---------------------------------------------------------------------------
// Action Processing
// ---------------------------------------------------------------------------

/**
 * Processes GET and POST slider actions before the page renders.
 * Hooked to admin_init so redirects can be issued cleanly.
 */
function jasanika_slider_process_actions(): void {
	if ( ! isset( $_GET['page'] ) || 'jasanika-slider-manager' !== $_GET['page'] ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$redirect = admin_url( 'admin.php?page=jasanika-slider-manager' );

	// --- GET actions: delete, toggle ---
	if ( isset( $_GET['slider_action'] ) ) {
		$action   = sanitize_key( $_GET['slider_action'] );
		$slide_id = isset( $_GET['slide_id'] ) ? absint( $_GET['slide_id'] ) : 0;

		if ( 'delete' === $action && $slide_id > 0 ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'jasanika_slider_delete_' . $slide_id ) ) {
				wp_die( esc_html__( 'Security check failed.', 'jasanika' ) );
			}
			jasanika_slider_delete_slide( $slide_id );
			wp_safe_redirect( add_query_arg( 'message', 'deleted', $redirect ) );
			exit;
		}

		if ( 'toggle' === $action && $slide_id > 0 ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'jasanika_slider_toggle_' . $slide_id ) ) {
				wp_die( esc_html__( 'Security check failed.', 'jasanika' ) );
			}
			jasanika_slider_toggle_slide( $slide_id );
			wp_safe_redirect( add_query_arg( 'message', 'toggled', $redirect ) );
			exit;
		}
	}

	// --- POST actions: add, edit ---
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['jasanika_slider_action'] ) ) {
		$post_action = sanitize_key( $_POST['jasanika_slider_action'] );

		if ( 'add' === $post_action ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'jasanika_slider_add' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'jasanika' ) );
			}
			jasanika_slider_save_slide( 0, $_POST );
			wp_safe_redirect( add_query_arg( 'message', 'added', $redirect ) );
			exit;
		}

		if ( 'edit' === $post_action ) {
			$slide_id = isset( $_POST['slide_id'] ) ? absint( $_POST['slide_id'] ) : 0;
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'jasanika_slider_edit_' . $slide_id ) ) {
				wp_die( esc_html__( 'Security check failed.', 'jasanika' ) );
			}
			if ( $slide_id > 0 ) {
				jasanika_slider_save_slide( $slide_id, $_POST );
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
 * Saves a slide. If slide_id is 0, creates a new slide; otherwise updates.
 *
 * @param int   $slide_id  0 for new, existing ID for update.
 * @param array $data      Raw POST data.
 */
function jasanika_slider_save_slide( int $slide_id, array $data ): void {
	$slides = get_option( 'jasanika_slides', array() );
	if ( ! is_array( $slides ) ) {
		$slides = array();
	}

	$sanitized = array(
		'id'          => $slide_id,
		'title'       => sanitize_text_field( $data['slide_title'] ?? '' ),
		'description' => sanitize_textarea_field( $data['slide_description'] ?? '' ),
		'image_url'   => esc_url_raw( $data['slide_image_url'] ?? '' ),
		'button_text' => sanitize_text_field( $data['slide_button_text'] ?? '' ),
		'button_url'  => esc_url_raw( $data['slide_button_url'] ?? '' ),
		'sort_order'  => absint( $data['slide_sort_order'] ?? 0 ),
		'active'      => isset( $data['slide_active'] ) ? 1 : 0,
	);

	if ( 0 === $slide_id ) {
		// Auto-increment ID.
		$max_id = 0;
		foreach ( $slides as $slide ) {
			if ( (int) ( $slide['id'] ?? 0 ) > $max_id ) {
				$max_id = (int) $slide['id'];
			}
		}
		$sanitized['id'] = $max_id + 1;
		$slides[]        = $sanitized;
	} else {
		$found = false;
		foreach ( $slides as &$slide ) {
			if ( (int) ( $slide['id'] ?? 0 ) === $slide_id ) {
				$slide = $sanitized;
				$found = true;
				break;
			}
		}
		unset( $slide );
		// Slide not found – append as new.
		if ( ! $found ) {
			$slides[] = $sanitized;
		}
	}

	update_option( 'jasanika_slides', $slides );
}

/**
 * Deletes a slide by ID.
 *
 * @param int $slide_id
 */
function jasanika_slider_delete_slide( int $slide_id ): void {
	$slides = get_option( 'jasanika_slides', array() );
	if ( ! is_array( $slides ) ) {
		return;
	}
	$slides = array_values(
		array_filter(
			$slides,
			function ( array $slide ) use ( $slide_id ): bool {
				return (int) ( $slide['id'] ?? 0 ) !== $slide_id;
			}
		)
	);
	update_option( 'jasanika_slides', $slides );
}

/**
 * Toggles the active status of a slide by ID.
 *
 * @param int $slide_id
 */
function jasanika_slider_toggle_slide( int $slide_id ): void {
	$slides = get_option( 'jasanika_slides', array() );
	if ( ! is_array( $slides ) ) {
		return;
	}
	foreach ( $slides as &$slide ) {
		if ( (int) ( $slide['id'] ?? 0 ) === $slide_id ) {
			$slide['active'] = empty( $slide['active'] ) ? 1 : 0;
			break;
		}
	}
	unset( $slide );
	update_option( 'jasanika_slides', $slides );
}

/**
 * Returns a single slide by ID, or null if not found.
 *
 * @param int $slide_id
 * @return array|null
 */
function jasanika_slider_get_by_id( int $slide_id ): ?array {
	$slides = get_option( 'jasanika_slides', array() );
	if ( ! is_array( $slides ) ) {
		return null;
	}
	foreach ( $slides as $slide ) {
		if ( (int) ( $slide['id'] ?? 0 ) === $slide_id ) {
			return $slide;
		}
	}
	return null;
}

// ---------------------------------------------------------------------------
// Page Renderer
// ---------------------------------------------------------------------------

/**
 * Renders the Slider Manager admin page.
 */
function jasanika_admin_page_slider_manager(): void {
	$message  = isset( $_GET['message'] ) ? sanitize_key( $_GET['message'] ) : '';
	$action   = isset( $_GET['slider_action'] ) ? sanitize_key( $_GET['slider_action'] ) : 'list';
	$slide_id = isset( $_GET['slide_id'] ) ? absint( $_GET['slide_id'] ) : 0;
	?>
	<div class="wrap">

		<h1><?php esc_html_e( 'Jasanika – Slider Manager', 'jasanika' ); ?></h1>

		<?php jasanika_slider_render_notices( $message ); ?>

		<?php if ( 'edit' === $action && $slide_id > 0 ) : ?>
			<?php jasanika_slider_render_edit_form( $slide_id ); ?>
		<?php else : ?>
			<?php jasanika_slider_render_list(); ?>
			<hr>
			<?php jasanika_slider_render_add_form(); ?>
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
function jasanika_slider_render_notices( string $message ): void {
	$labels = array(
		'added'   => __( 'Slide added successfully.', 'jasanika' ),
		'updated' => __( 'Slide updated successfully.', 'jasanika' ),
		'deleted' => __( 'Slide deleted successfully.', 'jasanika' ),
		'toggled' => __( 'Slide status updated.', 'jasanika' ),
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
 * Renders the slides list table.
 */
function jasanika_slider_render_list(): void {
	$slides   = jasanika_get_slides();
	$base_url = admin_url( 'admin.php?page=jasanika-slider-manager' );
	?>
	<h2><?php esc_html_e( 'Slides', 'jasanika' ); ?></h2>

	<?php if ( empty( $slides ) ) : ?>
		<p><?php esc_html_e( 'No slides found. Add your first slide below.', 'jasanika' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col" style="width:60px;"><?php esc_html_e( 'Order', 'jasanika' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Title', 'jasanika' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Description', 'jasanika' ); ?></th>
					<th scope="col" style="width:80px;"><?php esc_html_e( 'Active', 'jasanika' ); ?></th>
					<th scope="col" style="width:180px;"><?php esc_html_e( 'Actions', 'jasanika' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $slides as $slide ) : ?>
					<?php
					$id         = (int) ( $slide['id'] ?? 0 );
					$is_active  = ! empty( $slide['active'] );
					$edit_url   = add_query_arg( array( 'slider_action' => 'edit', 'slide_id' => $id ), $base_url );
					$toggle_url = wp_nonce_url(
						add_query_arg( array( 'slider_action' => 'toggle', 'slide_id' => $id ), $base_url ),
						'jasanika_slider_toggle_' . $id
					);
					$delete_url = wp_nonce_url(
						add_query_arg( array( 'slider_action' => 'delete', 'slide_id' => $id ), $base_url ),
						'jasanika_slider_delete_' . $id
					);
					?>
					<tr>
						<td><?php echo esc_html( $slide['sort_order'] ?? 0 ); ?></td>
						<td>
							<strong>
								<a href="<?php echo esc_url( $edit_url ); ?>">
									<?php echo esc_html( $slide['title'] ?: __( '(no title)', 'jasanika' ) ); ?>
								</a>
							</strong>
						</td>
						<td><?php echo esc_html( wp_trim_words( $slide['description'] ?? '', 10, '…' ) ); ?></td>
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
								onclick="return confirm( '<?php esc_attr_e( 'Are you sure you want to delete this slide?', 'jasanika' ); ?>' )"
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
 * Renders the Add New Slide form.
 */
function jasanika_slider_render_add_form(): void {
	?>
	<h2><?php esc_html_e( 'Add New Slide', 'jasanika' ); ?></h2>
	<form method="post" action="">
		<?php wp_nonce_field( 'jasanika_slider_add', '_wpnonce' ); ?>
		<input type="hidden" name="jasanika_slider_action" value="add">
		<?php jasanika_slider_render_form_fields( array() ); ?>
		<p class="submit">
			<input
				type="submit"
				class="button button-primary"
				value="<?php esc_attr_e( 'Add Slide', 'jasanika' ); ?>"
			>
		</p>
	</form>
	<?php
}

// ---------------------------------------------------------------------------
// Edit Form
// ---------------------------------------------------------------------------

/**
 * Renders the Edit Slide form for the given slide ID.
 *
 * @param int $slide_id
 */
function jasanika_slider_render_edit_form( int $slide_id ): void {
	$slide    = jasanika_slider_get_by_id( $slide_id );
	$base_url = admin_url( 'admin.php?page=jasanika-slider-manager' );

	if ( null === $slide ) {
		echo '<p>' . esc_html__( 'Slide not found.', 'jasanika' ) . '</p>';
		echo '<p><a href="' . esc_url( $base_url ) . '">' . esc_html__( '← Back to Slides', 'jasanika' ) . '</a></p>';
		return;
	}
	?>
	<h2><?php esc_html_e( 'Edit Slide', 'jasanika' ); ?></h2>
	<p>
		<a href="<?php echo esc_url( $base_url ); ?>">&larr; <?php esc_html_e( 'Back to Slides', 'jasanika' ); ?></a>
	</p>
	<form method="post" action="">
		<?php wp_nonce_field( 'jasanika_slider_edit_' . $slide_id, '_wpnonce' ); ?>
		<input type="hidden" name="jasanika_slider_action" value="edit">
		<input type="hidden" name="slide_id" value="<?php echo esc_attr( $slide_id ); ?>">
		<?php jasanika_slider_render_form_fields( $slide ); ?>
		<p class="submit">
			<input
				type="submit"
				class="button button-primary"
				value="<?php esc_attr_e( 'Update Slide', 'jasanika' ); ?>"
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
 * @param array $slide  Slide data for pre-population; empty array for a new slide.
 */
function jasanika_slider_render_form_fields( array $slide ): void {
	$title       = esc_attr( $slide['title'] ?? '' );
	$description = esc_textarea( $slide['description'] ?? '' );
	$image_url   = esc_attr( $slide['image_url'] ?? '' );
	$button_text = esc_attr( $slide['button_text'] ?? '' );
	$button_url  = esc_attr( $slide['button_url'] ?? '' );
	$sort_order  = isset( $slide['sort_order'] ) ? (int) $slide['sort_order'] : 0;
	$active      = ! empty( $slide['active'] );
	?>
	<table class="form-table" role="presentation">

		<tr>
			<th scope="row">
				<label for="slide_title"><?php esc_html_e( 'Title', 'jasanika' ); ?></label>
			</th>
			<td>
				<input
					type="text"
					id="slide_title"
					name="slide_title"
					value="<?php echo $title; ?>"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'Vítejte na Jasanika', 'jasanika' ); ?>"
				>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_description"><?php esc_html_e( 'Description', 'jasanika' ); ?></label>
			</th>
			<td>
				<textarea
					id="slide_description"
					name="slide_description"
					class="large-text"
					rows="3"
					placeholder="<?php esc_attr_e( 'Ručně tvořené výrobky a originální dekorace.', 'jasanika' ); ?>"
				><?php echo $description; ?></textarea>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_image_url"><?php esc_html_e( 'Image URL', 'jasanika' ); ?></label>
			</th>
			<td>
				<input
					type="url"
					id="slide_image_url"
					name="slide_image_url"
					value="<?php echo $image_url; ?>"
					class="large-text"
					placeholder="https://"
				>
				<?php if ( $image_url ) : ?>
					<p>
						<img
							src="<?php echo esc_url( $slide['image_url'] ); ?>"
							alt=""
							style="max-width:200px;max-height:120px;margin-top:6px;display:block;"
						>
					</p>
				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_button_text"><?php esc_html_e( 'Button Text', 'jasanika' ); ?></label>
			</th>
			<td>
				<input
					type="text"
					id="slide_button_text"
					name="slide_button_text"
					value="<?php echo $button_text; ?>"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'Zjistit více', 'jasanika' ); ?>"
				>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_button_url"><?php esc_html_e( 'Button URL', 'jasanika' ); ?></label>
			</th>
			<td>
				<input
					type="text"
					id="slide_button_url"
					name="slide_button_url"
					value="<?php echo $button_url; ?>"
					class="large-text"
					placeholder="/obchod"
				>
				<p class="description"><?php esc_html_e( 'Relative paths (e.g. /obchod) and full URLs are both accepted.', 'jasanika' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_sort_order"><?php esc_html_e( 'Sort Order', 'jasanika' ); ?></label>
			</th>
			<td>
				<input
					type="number"
					id="slide_sort_order"
					name="slide_sort_order"
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
					<input type="checkbox" name="slide_active" value="1" <?php checked( $active ); ?>>
					<?php esc_html_e( 'Display this slide on the homepage', 'jasanika' ); ?>
				</label>
			</td>
		</tr>

	</table>
	<?php
}
