<?php

/**
 * Hero Slider Component
 *
 * Displays the homepage hero slider section.
 * Loads slides from the Slider Manager when slides exist;
 * falls back to the static placeholder when no managed slides are stored.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$managed_slides = function_exists( 'jasanika_get_active_slides' ) ? jasanika_get_active_slides() : array();

if ( ! empty( $managed_slides ) ) {
	$hero_slides = array();
	foreach ( $managed_slides as $managed_slide ) {
		$hero_slides[] = array(
			'heading'     => $managed_slide['title'],
			'description' => $managed_slide['description'],
			'cta_label'   => $managed_slide['button_text'],
			'cta_url'     => $managed_slide['button_url'],
			'image_url'   => $managed_slide['image_url'],
			'image_alt'   => $managed_slide['title'],
		);
	}
} else {
	// Fallback: use Theme Settings values (or built-in defaults when empty).
	$hero_slides = array(
		array(
			'heading'     => jasanika_get_hero_heading(),
			'description' => jasanika_get_hero_description(),
			'cta_label'   => jasanika_get_hero_button_text(),
			'cta_url'     => jasanika_get_hero_button_url(),
			'image_url'   => get_template_directory_uri() . '/assets/images/hero/hero-placeholder.svg',
			'image_alt'   => __( 'Jasanika – ručně tvořené dekorace', 'jasanika' ),
		),
	);
}
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

					<?php if ( ! empty( $slide['description'] ) ) : ?>
						<p class="hero-slider__description">
							<?php echo esc_html( $slide['description'] ); ?>
						</p>
					<?php endif; ?>

					<?php if ( ! empty( $slide['cta_label'] ) && ! empty( $slide['cta_url'] ) ) : ?>
						<a
							href="<?php echo esc_url( $slide['cta_url'] ); ?>"
							class="btn btn-primary btn-lg hero-slider__cta"
						>
							<?php echo esc_html( $slide['cta_label'] ); ?>
						</a>
					<?php endif; ?>

				</div>

				<?php if ( ! empty( $slide['image_url'] ) ) : ?>
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
				<?php endif; ?>

			</article>

		<?php endforeach; ?>

	</div>

</section>
