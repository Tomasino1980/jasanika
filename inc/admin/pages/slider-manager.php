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
add_action( 'admin_enqueue_scripts', 'jasanika_slider_manager_admin_enqueue' );

/**
 * Enqueue Slider Manager admin assets only on the Slider Manager admin page.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_slider_manager_admin_enqueue( string $hook ): void {
	if ( 'jasanika_page_jasanika-slider-manager' !== $hook ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'jasanika-slider-manager-admin',
		get_template_directory_uri() . '/assets/css/admin/slider-manager-admin.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'jasanika-slider-manager-admin',
		get_template_directory_uri() . '/assets/js/admin/slider-manager.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);
}

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

	// sanitize and validate inputs
	$sanitized = array(
		'id'          => $slide_id,
		'title'       => sanitize_text_field( $data['slide_title'] ?? '' ),
		'description' => sanitize_textarea_field( $data['slide_description'] ?? '' ),
		'image_url'   => esc_url_raw( $data['slide_image_url'] ?? '' ),
		'button_text' => sanitize_text_field( $data['slide_button_text'] ?? '' ),
		'button_url'  => esc_url_raw( $data['slide_button_url'] ?? '' ),
		'sort_order'  => absint( $data['slide_sort_order'] ?? 0 ),
		'active'      => isset( $data['slide_active'] ) ? 1 : 0,

		// Layout / content
		'content_position'          => in_array( ( $data['slide_content_position'] ?? '' ), array( 'left', 'center', 'right' ), true ) ? $data['slide_content_position'] : 'center',
		'text_alignment'            => in_array( ( $data['slide_text_alignment'] ?? '' ), array( 'left', 'center', 'right' ), true ) ? $data['slide_text_alignment'] : 'left',
		'content_width'             => absint( $data['slide_content_width'] ?? 60 ), // percent
		'content_vertical_position' => in_array( ( $data['slide_content_vertical_position'] ?? '' ), array( 'top', 'center', 'bottom' ), true ) ? $data['slide_content_vertical_position'] : 'center',

		// Heights (px)
		'height_desktop' => absint( $data['slide_height_desktop'] ?? 600 ),
		'height_tablet'  => absint( $data['slide_height_tablet'] ?? 400 ),
		'height_mobile'  => absint( $data['slide_height_mobile'] ?? 300 ),

		// Overlay
		'overlay_color'  => function_exists( 'sanitize_hex_color' ) ? sanitize_hex_color( $data['slide_overlay_color'] ?? '' ) : sanitize_text_field( $data['slide_overlay_color'] ?? '' ),
		'overlay_opacity' => min( 100, max( 0, absint( $data['slide_overlay_opacity'] ?? 50 ) ) ),

		// Image behaviour
		'image_fit'      => in_array( ( $data['slide_image_fit'] ?? '' ), array( 'cover', 'contain', 'stretch' ), true ) ? $data['slide_image_fit'] : 'cover',
		'image_position' => in_array( ( $data['slide_image_position'] ?? '' ), array( 'center', 'top', 'bottom', 'left', 'right' ), true ) ? $data['slide_image_position'] : 'center',

		// Button
		'button_alignment' => in_array( ( $data['slide_button_alignment'] ?? '' ), array( 'left', 'center', 'right' ), true ) ? $data['slide_button_alignment'] : 'left',
		'button_width'     => absint( $data['slide_button_width'] ?? 200 ),
		'button_style'     => in_array( ( $data['slide_button_style'] ?? '' ), array( 'solid', 'outline', 'ghost' ), true ) ? $data['slide_button_style'] : 'solid',
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

	// new fields with defaults
	$content_position = esc_attr( $slide['content_position'] ?? 'center' );
	$text_alignment = esc_attr( $slide['text_alignment'] ?? 'left' );
	$content_width = esc_attr( $slide['content_width'] ?? 60 );
	$content_vertical_position = esc_attr( $slide['content_vertical_position'] ?? 'center' );

	$height_desktop = esc_attr( $slide['height_desktop'] ?? 600 );
	$height_tablet  = esc_attr( $slide['height_tablet'] ?? 400 );
	$height_mobile  = esc_attr( $slide['height_mobile'] ?? 300 );

	$overlay_color = esc_attr( $slide['overlay_color'] ?? '' );
	$overlay_opacity = esc_attr( $slide['overlay_opacity'] ?? 50 );

	$image_fit = esc_attr( $slide['image_fit'] ?? 'cover' );
	$image_position = esc_attr( $slide['image_position'] ?? 'center' );

	$button_alignment = esc_attr( $slide['button_alignment'] ?? 'left' );
	$button_width = esc_attr( $slide['button_width'] ?? 200 );
	$button_style = esc_attr( $slide['button_style'] ?? 'solid' );
	?>
	<table class="form-table" role="presentation">

		<tr>
			<th scope="row">
				<label for="slide_title"><?php esc_html_e( 'Title', 'jasanika' ); ?></label>
			</th>
			<td>
				<input type="text" id="slide_title" name="slide_title" value="<?php echo $title; ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Vítejte na Jasanika', 'jasanika' ); ?>">
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_description"><?php esc_html_e( 'Description', 'jasanika' ); ?></label>
			</th>
			<td>
				<textarea id="slide_description" name="slide_description" class="large-text" rows="3" placeholder="<?php esc_attr_e( 'Ručně tvořené výrobky a originální dekorace.', 'jasanika' ); ?>"><?php echo $description; ?></textarea>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_image_url"><?php esc_html_e( 'Image URL', 'jasanika' ); ?></label>
			</th>
			<td>
				<input type="url" id="slide_image_url" name="slide_image_url" value="<?php echo $image_url; ?>" class="large-text" placeholder="https://">
				<p class="description"><?php esc_html_e( 'You can paste an image URL or use the media uploader.', 'jasanika' ); ?>
					<button type="button" class="button" id="jasanika-slide-image-select"><?php esc_html_e( 'Select from Media', 'jasanika' ); ?></button>
				</p>
				<?php if ( $image_url ) : ?>
					<p>
						<img src="<?php echo esc_url( $slide['image_url'] ); ?>" alt="" style="max-width:200px;max-height:120px;margin-top:6px;display:block;">
					</p>
				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_image_fit"><?php esc_html_e( 'Image Fit', 'jasanika' ); ?></label>
			</th>
			<td>
				<select id="slide_image_fit" name="slide_image_fit">
					<option value="cover" <?php selected( $image_fit, 'cover' ); ?>><?php esc_html_e( 'Cover', 'jasanika' ); ?></option>
					<option value="contain" <?php selected( $image_fit, 'contain' ); ?>><?php esc_html_e( 'Contain', 'jasanika' ); ?></option>
					<option value="stretch" <?php selected( $image_fit, 'stretch' ); ?>><?php esc_html_e( 'Stretch', 'jasanika' ); ?></option>
				</select>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_image_position"><?php esc_html_e( 'Image Position', 'jasanika' ); ?></label>
			</th>
			<td>
				<select id="slide_image_position" name="slide_image_position">
					<option value="center" <?php selected( $image_position, 'center' ); ?>><?php esc_html_e( 'Center', 'jasanika' ); ?></option>
					<option value="top" <?php selected( $image_position, 'top' ); ?>><?php esc_html_e( 'Top', 'jasanika' ); ?></option>
					<option value="bottom" <?php selected( $image_position, 'bottom' ); ?>><?php esc_html_e( 'Bottom', 'jasanika' ); ?></option>
					<option value="left" <?php selected( $image_position, 'left' ); ?>><?php esc_html_e( 'Left', 'jasanika' ); ?></option>
					<option value="right" <?php selected( $image_position, 'right' ); ?>><?php esc_html_e( 'Right', 'jasanika' ); ?></option>
				</select>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_content_position"><?php esc_html_e( 'Content Horizontal Position', 'jasanika' ); ?></label>
			</th>
			<td>
				<select id="slide_content_position" name="slide_content_position">
					<option value="left" <?php selected( $content_position, 'left' ); ?>><?php esc_html_e( 'Left', 'jasanika' ); ?></option>
					<option value="center" <?php selected( $content_position, 'center' ); ?>><?php esc_html_e( 'Center', 'jasanika' ); ?></option>
					<option value="right" <?php selected( $content_position, 'right' ); ?>><?php esc_html_e( 'Right', 'jasanika' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Controls where the content block sits horizontally.', 'jasanika' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_content_vertical_position"><?php esc_html_e( 'Content Vertical Position', 'jasanika' ); ?></label>
			</th>
			<td>
				<select id="slide_content_vertical_position" name="slide_content_vertical_position">
					<option value="top" <?php selected( $content_vertical_position, 'top' ); ?>><?php esc_html_e( 'Top', 'jasanika' ); ?></option>
					<option value="center" <?php selected( $content_vertical_position, 'center' ); ?>><?php esc_html_e( 'Center', 'jasanika' ); ?></option>
					<option value="bottom" <?php selected( $content_vertical_position, 'bottom' ); ?>><?php esc_html_e( 'Bottom', 'jasanika' ); ?></option>
				</select>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_content_width"><?php esc_html_e( 'Content Width (percent)', 'jasanika' ); ?></label>
			</th>
			<td>
				<input type="number" id="slide_content_width" name="slide_content_width" value="<?php echo $content_width; ?>" class="small-text" min="10" max="100"> %
				<p class="description"><?php esc_html_e( 'Width of the content block in percent.', 'jasanika' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_text_alignment"><?php esc_html_e( 'Text Alignment', 'jasanika' ); ?></label>
			</th>
			<td>
				<select id="slide_text_alignment" name="slide_text_alignment">
					<option value="left" <?php selected( $text_alignment, 'left' ); ?>><?php esc_html_e( 'Left', 'jasanika' ); ?></option>
					<option value="center" <?php selected( $text_alignment, 'center' ); ?>><?php esc_html_e( 'Center', 'jasanika' ); ?></option>
					<option value="right" <?php selected( $text_alignment, 'right' ); ?>><?php esc_html_e( 'Right', 'jasanika' ); ?></option>
				</select>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_height_desktop"><?php esc_html_e( 'Desktop Height (px)', 'jasanika' ); ?></label>
			</th>
			<td>
				<input type="number" id="slide_height_desktop" name="slide_height_desktop" value="<?php echo $height_desktop; ?>" class="small-text" min="0"> px
				<p class="description"><?php esc_html_e( 'Height used on desktop devices.', 'jasanika' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_height_tablet"><?php esc_html_e( 'Tablet Height (px)', 'jasanika' ); ?></label>
			</th>
			<td>
				<input type="number" id="slide_height_tablet" name="slide_height_tablet" value="<?php echo $height_tablet; ?>" class="small-text" min="0"> px
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_height_mobile"><?php esc_html_e( 'Mobile Height (px)', 'jasanika' ); ?></label>
			</th>
			<td>
				<input type="number" id="slide_height_mobile" name="slide_height_mobile" value="<?php echo $height_mobile; ?>" class="small-text" min="0"> px
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_overlay_color"><?php esc_html_e( 'Overlay Color', 'jasanika' ); ?></label>
			</th>
			<td>
				<input type="text" id="slide_overlay_color" name="slide_overlay_color" value="<?php echo $overlay_color; ?>" class="regular-text" placeholder="#000000">
				<p class="description"><?php esc_html_e( 'Hex color used as an overlay to improve text readability.', 'jasanika' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_overlay_opacity"><?php esc_html_e( 'Overlay Opacity', 'jasanika' ); ?></label>
			</th>
			<td>
				<input type="number" id="slide_overlay_opacity" name="slide_overlay_opacity" value="<?php echo $overlay_opacity; ?>" class="small-text" min="0" max="100"> %
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_button_alignment"><?php esc_html_e( 'Button Alignment', 'jasanika' ); ?></label>
			</th>
			<td>
				<select id="slide_button_alignment" name="slide_button_alignment">
					<option value="left" <?php selected( $button_alignment, 'left' ); ?>><?php esc_html_e( 'Left', 'jasanika' ); ?></option>
					<option value="center" <?php selected( $button_alignment, 'center' ); ?>><?php esc_html_e( 'Center', 'jasanika' ); ?></option>
					<option value="right" <?php selected( $button_alignment, 'right' ); ?>><?php esc_html_e( 'Right', 'jasanika' ); ?></option>
				</select>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_button_width"><?php esc_html_e( 'Button Width (px)', 'jasanika' ); ?></label>
			</th>
			<td>
				<input type="number" id="slide_button_width" name="slide_button_width" value="<?php echo $button_width; ?>" class="small-text" min="0"> px
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_button_style"><?php esc_html_e( 'Button Style', 'jasanika' ); ?></label>
			</th>
			<td>
				<select id="slide_button_style" name="slide_button_style">
					<option value="solid" <?php selected( $button_style, 'solid' ); ?>><?php esc_html_e( 'Solid', 'jasanika' ); ?></option>
					<option value="outline" <?php selected( $button_style, 'outline' ); ?>><?php esc_html_e( 'Outline', 'jasanika' ); ?></option>
					<option value="ghost" <?php selected( $button_style, 'ghost' ); ?>><?php esc_html_e( 'Ghost', 'jasanika' ); ?></option>
				</select>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="slide_sort_order"><?php esc_html_e( 'Sort Order', 'jasanika' ); ?></label>
			</th>
			<td>
				<input type="number" id="slide_sort_order" name="slide_sort_order" value="<?php echo esc_attr( $sort_order ); ?>" class="small-text" min="0" step="1">
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

	<!-- Live Preview -->
	<div id="jasanika-slide-preview" class="jasanika-slide-preview" data-image="<?php echo esc_attr( $image_url ); ?>" style="max-width:900px;margin-top:18px;">
		<div class="jasanika-slide-preview__image" style="background-image:url('<?php echo esc_url( $image_url ); ?>');">
			<div class="jasanika-slide-preview__overlay"></div>
			<div class="jasanika-slide-preview__content">
				<h3 class="preview-title"><?php echo $title; ?></h3>
				<p class="preview-desc"><?php echo $description; ?></p>
				<p class="preview-button"><a class="button"><?php echo $button_text; ?></a></p>
			</div>
		</div>
	</div>
	<?php
}
