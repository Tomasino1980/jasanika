<?php

/**
 * Front Page
 *
 * Template for the static front page.
 */

get_header();
?>

<main id="main" class="site-main">

	<?php foreach ( jasanika_get_enabled_homepage_sections() as $key => $section ) : ?>
		<?php jasanika_render_homepage_section( $key ); ?>
	<?php endforeach; ?>

</main>

<?php
get_footer();
