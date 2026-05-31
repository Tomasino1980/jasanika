<?php

/**
 * Featured Products
 *
 * Displays featured WooCommerce products on the homepage.
 * Requires WooCommerce to be active. Hidden when WooCommerce is inactive
 * or when no products exist.
 *
 * Section title, description and product count are managed from
 * Jasanika → Theme Settings → Featured Products.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'WC' ) ) {
	return;
}

if ( ! jasanika_has_featured_products() ) {
	return;
}

$products = jasanika_get_featured_products();

if ( empty( $products ) ) {
	return;
}

$section_title       = jasanika_get_featured_products_title();
$section_description = jasanika_get_featured_products_description();
?>

<section class="featured-products">
	<div class="featured-products__container">

		<div class="featured-products__header">
			<h2 class="featured-products__heading"><?php echo esc_html( $section_title ); ?></h2>
			<?php if ( $section_description ) : ?>
				<p class="featured-products__description"><?php echo esc_html( $section_description ); ?></p>
			<?php endif; ?>
		</div>

		<div class="featured-products__grid">

			<?php foreach ( $products as $product ) : ?>

				<?php
				$product_id          = $product->get_id();
				$product_name        = $product->get_name();
				$product_permalink   = get_permalink( $product_id );
				$product_short_desc  = $product->get_short_description();
				$product_image_id    = $product->get_image_id();
				?>

				<article class="featured-products__card">

					<a href="<?php echo esc_url( $product_permalink ); ?>" class="featured-products__image-link" tabindex="-1" aria-hidden="true">
						<?php if ( $product_image_id ) : ?>
							<?php echo wp_get_attachment_image( $product_image_id, 'woocommerce_thumbnail', false, array( 'class' => 'featured-products__image' ) ); ?>
						<?php else : ?>
							<div class="featured-products__image featured-products__image--placeholder">
								<?php echo wc_placeholder_img( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce core output. ?>
							</div>
						<?php endif; ?>
					</a>

					<div class="featured-products__body">

						<h3 class="featured-products__name">
							<a href="<?php echo esc_url( $product_permalink ); ?>" class="featured-products__name-link">
								<?php echo esc_html( $product_name ); ?>
							</a>
						</h3>

						<div class="featured-products__price">
							<?php echo wp_kses_post( $product->get_price_html() ); ?>
						</div>

						<?php if ( $product_short_desc ) : ?>
							<p class="featured-products__short-desc">
								<?php echo esc_html( wp_trim_words( wp_strip_all_tags( $product_short_desc ), 15, '…' ) ); ?>
							</p>
						<?php endif; ?>

						<div class="featured-products__actions">
							<?php
							echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce core output.
								'woocommerce_loop_add_to_cart_link',
								sprintf(
									'<a href="%s" data-quantity="1" class="btn btn-primary featured-products__add-to-cart %s" %s>%s</a>',
									esc_url( $product->add_to_cart_url() ),
									esc_attr( implode( ' ', array_filter( array( 'add_to_cart_button', $product->get_type(), $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '' ) ) ) ),
									isset( $args ) ? wc_implode_html_attributes( $args ) : '',
									esc_html( $product->add_to_cart_text() )
								),
								$product,
								array()
							);
							?>
						</div>

					</div>

				</article>

			<?php endforeach; ?>

		</div>

	</div>
</section>
