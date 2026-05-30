<?php

/**
 * Front Page
 *
 * Template for the static front page.
 */

get_header();
?>

<main id="main" class="site-main">

	<?php get_template_part( 'template-parts/components/hero-slider' ); ?>

	<?php get_template_part( 'template-parts/components/feature-blocks' ); ?>

</main>

<?php
get_footer();
