<?php

/**
 * Feature Blocks
 *
 * Homepage feature section with three editable cards.
 * Content is managed from Jasanika → Theme Settings → Homepage Content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block1 = jasanika_get_feature_block( 1 );
$block2 = jasanika_get_feature_block( 2 );
$block3 = jasanika_get_feature_block( 3 );
?>

<section class="feature-blocks">
	<div class="feature-blocks__container">
		<div class="feature-blocks__grid">

			<?php foreach ( array( $block1, $block2, $block3 ) as $block ) : ?>

			<article class="feature-blocks__card">
				<div class="feature-blocks__icon" aria-hidden="true">
					<span class="feature-blocks__icon-placeholder"></span>
				</div>
				<h2 class="feature-blocks__heading"><?php echo esc_html( $block['title'] ); ?></h2>
				<p class="feature-blocks__description"><?php echo esc_html( $block['description'] ); ?></p>
				<?php if ( ! empty( $block['button_text'] ) ) : ?>
				<a href="<?php echo esc_url( $block['button_url'] ); ?>" class="btn btn-outline"><?php echo esc_html( $block['button_text'] ); ?></a>
				<?php endif; ?>
			</article>

			<?php endforeach; ?>

		</div>
	</div>
</section>
