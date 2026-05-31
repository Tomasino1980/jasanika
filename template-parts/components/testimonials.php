<?php

/**
 * Testimonials Section
 *
 * Homepage testimonials section displaying customer reviews.
 * Content is managed from Jasanika → Testimonials Manager.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$testimonials = jasanika_get_active_testimonials();

if ( empty( $testimonials ) ) {
	return;
}
?>

<section class="testimonials">
	<div class="testimonials__container">

		<h2 class="testimonials__heading"><?php esc_html_e( 'What Our Customers Say', 'jasanika' ); ?></h2>

		<div class="testimonials__grid">

			<?php foreach ( $testimonials as $testimonial ) : ?>
				<?php
				$rating      = min( 5, max( 1, (int) ( $testimonial['rating'] ?? 5 ) ) );
				$stars_full  = str_repeat( '★', $rating );
				$stars_empty = str_repeat( '☆', 5 - $rating );
				?>

				<article class="testimonials__card">

					<div class="testimonials__rating" aria-label="<?php echo esc_attr( sprintf( _n( '%d star', '%d stars', $rating, 'jasanika' ), $rating ) ); ?>">
						<span class="testimonials__stars-full" aria-hidden="true"><?php echo esc_html( $stars_full ); ?></span><span class="testimonials__stars-empty" aria-hidden="true"><?php echo esc_html( $stars_empty ); ?></span>
					</div>

					<blockquote class="testimonials__text">
						<p><?php echo esc_html( $testimonial['text'] ?? '' ); ?></p>
					</blockquote>

					<footer class="testimonials__author">

						<?php if ( ! empty( $testimonial['image_url'] ) ) : ?>
							<div class="testimonials__avatar">
								<img
									src="<?php echo esc_url( $testimonial['image_url'] ); ?>"
									alt="<?php echo esc_attr( $testimonial['name'] ?? '' ); ?>"
									class="testimonials__avatar-img"
									loading="lazy"
								>
							</div>
						<?php else : ?>
							<div class="testimonials__avatar testimonials__avatar--placeholder" aria-hidden="true"></div>
						<?php endif; ?>

						<div class="testimonials__author-info">
							<span class="testimonials__name"><?php echo esc_html( $testimonial['name'] ?? '' ); ?></span>
							<?php if ( ! empty( $testimonial['position'] ) ) : ?>
								<span class="testimonials__position"><?php echo esc_html( $testimonial['position'] ); ?></span>
							<?php endif; ?>
						</div>

					</footer>

				</article>

			<?php endforeach; ?>

		</div>

	</div>
</section>
