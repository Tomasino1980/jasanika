<?php

/**
 * Jasanika SEO – Per-Post Meta Box
 *
 * Adds an SEO meta box to posts, pages and products.
 * Fields: SEO Title, SEO Description, SEO Keywords.
 * Includes a live score preview showing title and description length indicators.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Register Meta Box
// ---------------------------------------------------------------------------

/**
 * Register the SEO meta box on post, page and product edit screens.
 */
function jasanika_seo_meta_box_register(): void {
	$screens = array( 'post', 'page' );

	if ( post_type_exists( 'product' ) ) {
		$screens[] = 'product';
	}

	add_meta_box(
		'jasanika-seo-meta-box',
		__( 'SEO Settings', 'jasanika' ),
		'jasanika_seo_meta_box_render',
		$screens,
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'jasanika_seo_meta_box_register' );

// ---------------------------------------------------------------------------
// Enqueue Meta Box Assets
// ---------------------------------------------------------------------------

/**
 * Enqueue the SEO meta box stylesheet on post edit screens.
 *
 * @param string $hook Current admin page hook.
 */
function jasanika_seo_meta_box_enqueue( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	wp_enqueue_style(
		'jasanika-seo-manager',
		get_template_directory_uri() . '/assets/css/admin/seo-manager.css',
		array(),
		'0.41.0'
	);
}
add_action( 'admin_enqueue_scripts', 'jasanika_seo_meta_box_enqueue' );

// ---------------------------------------------------------------------------
// Render Meta Box
// ---------------------------------------------------------------------------

/**
 * Render the SEO meta box content.
 *
 * @param WP_Post $post Current post object.
 */
function jasanika_seo_meta_box_render( WP_Post $post ): void {
	wp_nonce_field( 'jasanika_seo_meta_box_save', 'jasanika_seo_meta_box_nonce' );

	$seo_title       = get_post_meta( $post->ID, '_jasanika_seo_title', true );
	$seo_description = get_post_meta( $post->ID, '_jasanika_seo_description', true );
	$seo_keywords    = get_post_meta( $post->ID, '_jasanika_seo_keywords', true );

	$title_len = mb_strlen( $seo_title );
	$desc_len  = mb_strlen( $seo_description );

	?>
	<div class="jasanika-seo-metabox">

		<!-- SEO Title -->
		<div class="jasanika-seo-metabox__field">
			<label for="jasanika_seo_title" class="jasanika-seo-metabox__label">
				<?php esc_html_e( 'SEO Title', 'jasanika' ); ?>
			</label>
			<input
				type="text"
				id="jasanika_seo_title"
				name="jasanika_seo_title"
				class="jasanika-seo-metabox__input widefat"
				value="<?php echo esc_attr( $seo_title ); ?>"
				placeholder="<?php esc_attr_e( 'Leave empty to use global title template', 'jasanika' ); ?>"
			>
			<div class="jasanika-seo-metabox__score" id="jasanika-title-score">
				<span class="jasanika-seo-metabox__score-count" id="jasanika-title-count"><?php echo esc_html( $title_len ); ?></span>
				<?php esc_html_e( 'characters', 'jasanika' ); ?>
				<span class="jasanika-seo-metabox__score-badge <?php echo esc_attr( jasanika_seo_get_title_badge_class( $title_len ) ); ?>" id="jasanika-title-badge">
					<?php echo esc_html( jasanika_seo_get_title_badge_label( $title_len ) ); ?>
				</span>
				<span class="jasanika-seo-metabox__score-hint">
					<?php esc_html_e( 'Recommended: 30–60 characters', 'jasanika' ); ?>
				</span>
			</div>
		</div>

		<!-- SEO Description -->
		<div class="jasanika-seo-metabox__field">
			<label for="jasanika_seo_description" class="jasanika-seo-metabox__label">
				<?php esc_html_e( 'SEO Description', 'jasanika' ); ?>
			</label>
			<textarea
				id="jasanika_seo_description"
				name="jasanika_seo_description"
				class="jasanika-seo-metabox__textarea widefat"
				rows="3"
				placeholder="<?php esc_attr_e( 'Leave empty to use global meta description', 'jasanika' ); ?>"
			><?php echo esc_textarea( $seo_description ); ?></textarea>
			<div class="jasanika-seo-metabox__score" id="jasanika-desc-score">
				<span class="jasanika-seo-metabox__score-count" id="jasanika-desc-count"><?php echo esc_html( $desc_len ); ?></span>
				<?php esc_html_e( 'characters', 'jasanika' ); ?>
				<span class="jasanika-seo-metabox__score-badge <?php echo esc_attr( jasanika_seo_get_desc_badge_class( $desc_len ) ); ?>" id="jasanika-desc-badge">
					<?php echo esc_html( jasanika_seo_get_desc_badge_label( $desc_len ) ); ?>
				</span>
				<span class="jasanika-seo-metabox__score-hint">
					<?php esc_html_e( 'Recommended: 120–160 characters', 'jasanika' ); ?>
				</span>
			</div>
		</div>

		<!-- SEO Keywords -->
		<div class="jasanika-seo-metabox__field">
			<label for="jasanika_seo_keywords" class="jasanika-seo-metabox__label">
				<?php esc_html_e( 'SEO Keywords', 'jasanika' ); ?>
			</label>
			<input
				type="text"
				id="jasanika_seo_keywords"
				name="jasanika_seo_keywords"
				class="jasanika-seo-metabox__input widefat"
				value="<?php echo esc_attr( $seo_keywords ); ?>"
				placeholder="<?php esc_attr_e( 'keyword1, keyword2, keyword3', 'jasanika' ); ?>"
			>
			<p class="jasanika-seo-metabox__hint">
				<?php esc_html_e( 'Separate keywords with commas. Leave empty to use global keywords.', 'jasanika' ); ?>
			</p>
		</div>

	</div>

	<script>
	( function () {
		'use strict';

		var titleInput = document.getElementById( 'jasanika_seo_title' );
		var descInput  = document.getElementById( 'jasanika_seo_description' );

		if ( titleInput ) {
			titleInput.addEventListener( 'input', function () {
				jasanikaSeoUpdateScore(
					this.value.length,
					'jasanika-title-count',
					'jasanika-title-badge',
					'title'
				);
			} );
		}

		if ( descInput ) {
			descInput.addEventListener( 'input', function () {
				jasanikaSeoUpdateScore(
					this.value.length,
					'jasanika-desc-count',
					'jasanika-desc-badge',
					'desc'
				);
			} );
		}

		function jasanikaSeoUpdateScore( length, countId, badgeId, type ) {
			var countEl = document.getElementById( countId );
			var badgeEl = document.getElementById( badgeId );

			if ( ! countEl || ! badgeEl ) {
				return;
			}

			countEl.textContent = length;

			var cls, label;

			if ( 'title' === type ) {
				if ( 0 === length ) {
					cls   = 'jasanika-seo-metabox__score-badge--neutral';
					label = '–';
				} else if ( length < 30 ) {
					cls   = 'jasanika-seo-metabox__score-badge--warning';
					label = '<?php echo esc_js( __( 'Too Short', 'jasanika' ) ); ?>';
				} else if ( length <= 60 ) {
					cls   = 'jasanika-seo-metabox__score-badge--good';
					label = '<?php echo esc_js( __( 'Good', 'jasanika' ) ); ?>';
				} else if ( length <= 70 ) {
					cls   = 'jasanika-seo-metabox__score-badge--warning';
					label = '<?php echo esc_js( __( 'Warning', 'jasanika' ) ); ?>';
				} else {
					cls   = 'jasanika-seo-metabox__score-badge--toolong';
					label = '<?php echo esc_js( __( 'Too Long', 'jasanika' ) ); ?>';
				}
			} else {
				if ( 0 === length ) {
					cls   = 'jasanika-seo-metabox__score-badge--neutral';
					label = '–';
				} else if ( length < 50 ) {
					cls   = 'jasanika-seo-metabox__score-badge--warning';
					label = '<?php echo esc_js( __( 'Too Short', 'jasanika' ) ); ?>';
				} else if ( length <= 160 ) {
					cls   = 'jasanika-seo-metabox__score-badge--good';
					label = '<?php echo esc_js( __( 'Good', 'jasanika' ) ); ?>';
				} else if ( length <= 180 ) {
					cls   = 'jasanika-seo-metabox__score-badge--warning';
					label = '<?php echo esc_js( __( 'Warning', 'jasanika' ) ); ?>';
				} else {
					cls   = 'jasanika-seo-metabox__score-badge--toolong';
					label = '<?php echo esc_js( __( 'Too Long', 'jasanika' ) ); ?>';
				}
			}

			badgeEl.className = 'jasanika-seo-metabox__score-badge ' + cls;
			badgeEl.textContent = label;
		}
	} () );
	</script>
	<?php
}

// ---------------------------------------------------------------------------
// Score Badge Helpers
// ---------------------------------------------------------------------------

/**
 * Return the CSS modifier class for a title length badge.
 *
 * @param int $length Character count.
 * @return string
 */
function jasanika_seo_get_title_badge_class( int $length ): string {
	if ( 0 === $length ) {
		return 'jasanika-seo-metabox__score-badge--neutral';
	}
	if ( $length < 30 || ( $length > 60 && $length <= 70 ) ) {
		return 'jasanika-seo-metabox__score-badge--warning';
	}
	if ( $length > 70 ) {
		return 'jasanika-seo-metabox__score-badge--toolong';
	}
	return 'jasanika-seo-metabox__score-badge--good';
}

/**
 * Return the label for a title length badge.
 *
 * @param int $length Character count.
 * @return string
 */
function jasanika_seo_get_title_badge_label( int $length ): string {
	if ( 0 === $length ) {
		return '–';
	}
	if ( $length < 30 ) {
		return __( 'Too Short', 'jasanika' );
	}
	if ( $length <= 60 ) {
		return __( 'Good', 'jasanika' );
	}
	if ( $length <= 70 ) {
		return __( 'Warning', 'jasanika' );
	}
	return __( 'Too Long', 'jasanika' );
}

/**
 * Return the CSS modifier class for a description length badge.
 *
 * @param int $length Character count.
 * @return string
 */
function jasanika_seo_get_desc_badge_class( int $length ): string {
	if ( 0 === $length ) {
		return 'jasanika-seo-metabox__score-badge--neutral';
	}
	if ( $length < 50 || ( $length > 160 && $length <= 180 ) ) {
		return 'jasanika-seo-metabox__score-badge--warning';
	}
	if ( $length > 180 ) {
		return 'jasanika-seo-metabox__score-badge--toolong';
	}
	return 'jasanika-seo-metabox__score-badge--good';
}

/**
 * Return the label for a description length badge.
 *
 * @param int $length Character count.
 * @return string
 */
function jasanika_seo_get_desc_badge_label( int $length ): string {
	if ( 0 === $length ) {
		return '–';
	}
	if ( $length < 50 ) {
		return __( 'Too Short', 'jasanika' );
	}
	if ( $length <= 160 ) {
		return __( 'Good', 'jasanika' );
	}
	if ( $length <= 180 ) {
		return __( 'Warning', 'jasanika' );
	}
	return __( 'Too Long', 'jasanika' );
}

// ---------------------------------------------------------------------------
// Save Meta
// ---------------------------------------------------------------------------

/**
 * Save SEO meta box data when the post is saved.
 *
 * @param int $post_id Post ID.
 */
function jasanika_seo_meta_box_save( int $post_id ): void {
	// Verify nonce.
	$nonce = isset( $_POST['jasanika_seo_meta_box_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['jasanika_seo_meta_box_nonce'] ) )
		: '';

	if ( ! wp_verify_nonce( $nonce, 'jasanika_seo_meta_box_save' ) ) {
		return;
	}

	// Skip autosaves and bulk edits.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// SEO Title.
	if ( isset( $_POST['jasanika_seo_title'] ) ) {
		$seo_title = sanitize_text_field( wp_unslash( $_POST['jasanika_seo_title'] ) );
		update_post_meta( $post_id, '_jasanika_seo_title', $seo_title );
	}

	// SEO Description.
	if ( isset( $_POST['jasanika_seo_description'] ) ) {
		$seo_description = sanitize_textarea_field( wp_unslash( $_POST['jasanika_seo_description'] ) );
		update_post_meta( $post_id, '_jasanika_seo_description', $seo_description );
	}

	// SEO Keywords.
	if ( isset( $_POST['jasanika_seo_keywords'] ) ) {
		$seo_keywords = sanitize_text_field( wp_unslash( $_POST['jasanika_seo_keywords'] ) );
		update_post_meta( $post_id, '_jasanika_seo_keywords', $seo_keywords );
	}
}
add_action( 'save_post', 'jasanika_seo_meta_box_save' );
