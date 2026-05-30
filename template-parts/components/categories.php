<?php

/**
 * Categories
 *
 * Displays up to 6 post categories ordered by post count on the homepage.
 */

$categories = get_categories(
	array(
		'orderby'    => 'count',
		'order'      => 'DESC',
		'hide_empty' => true,
		'number'     => 6,
	)
);

if ( empty( $categories ) ) {
	return;
}
?>

<section class="categories">
	<div class="categories__container">

		<h2 class="categories__heading"><?php esc_html_e( 'Browse Categories', 'jasanika' ); ?></h2>

		<div class="categories__grid">

			<?php foreach ( $categories as $category ) : ?>

				<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" class="categories__card">

					<span class="categories__name"><?php echo esc_html( $category->name ); ?></span>

					<span class="categories__count">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: number of articles in category */
								_n( '%d Article', '%d Articles', $category->count, 'jasanika' ),
								$category->count
							)
						);
						?>
					</span>

				</a>

			<?php endforeach; ?>

		</div>

	</div>
</section>
