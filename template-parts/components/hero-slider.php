<?php

/**
 * Hero Slider Component
 *
 * Displays the homepage hero slider section.
 * Currently renders a single slide; structure is ready for multiple slides.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hero_slides = array(
	array(
		'heading'     => __( 'Vítejte na Jasanika', 'jasanika' ),
		'description' => __( 'Ručně tvořený WordPress obchod a blog.', 'jasanika' ),
		'cta_label'   => __( 'Zjistit více', 'jasanika' ),
		'cta_url'     => get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ),
		'image_url'   => get_template_directory_uri() . '/assets/images/hero/hero-placeholder.svg',
		'image_alt'   => __( 'Jasanika – ručně tvořené dekorace', 'jasanika' ),
	),
);
?>

<section class="hero-slider" aria-label="<?php esc_attr_e( 'Hero slider', 'jasanika' ); ?>">

	<div class="hero-slider__track">

		<?php foreach ( $hero_slides as $index => $slide ) : ?>

			<article
				class="hero-slider__slide<?php echo 0 === $index ? ' hero-slider__slide--active' : ''; ?>"
				aria-hidden="<?php echo 0 === $index ? 'false' : 'true'; ?>"
			>

				<div class="hero-slider__content">

					<h1 class="hero-slider__heading">
						<?php echo esc_html( $slide['heading'] ); ?>
					</h1>

					<p class="hero-slider__description">
						<?php echo esc_html( $slide['description'] ); ?>
					</p>

					<a
						href="<?php echo esc_url( $slide['cta_url'] ); ?>"
						class="btn btn-primary btn-lg hero-slider__cta"
					>
						<?php echo esc_html( $slide['cta_label'] ); ?>
					</a>

				</div>

				<div class="hero-slider__media">
					<img
						src="<?php echo esc_url( $slide['image_url'] ); ?>"
						alt="<?php echo esc_attr( $slide['image_alt'] ); ?>"
						class="hero-slider__image"
						width="800"
						height="600"
						loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
					/>
				</div>

			</article>

		<?php endforeach; ?>

	</div>

</section>
