<?php

/**
 * Comments
 *
 * WordPress comments template.
 * Delegates rendering to the reusable comments section component.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'template-parts/comments/comments-section' );
