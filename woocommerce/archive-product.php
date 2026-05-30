<?php

/**
 * WooCommerce Product Archive
 *
 * Custom archive template for WooCommerce products.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

?>

<?php do_action( 'woocommerce_before_main_content' ); ?>

	<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
		<header class="product-archive__header">
			<h1 class="product-archive__title">
				<?php woocommerce_page_title(); ?>
			</h1>

			<?php
			$shop_page_id = wc_get_page_id( 'shop' );
			if ( $shop_page_id ) {
				$shop_description = wc_format_content( wp_kses_post( (string) get_post_field( 'post_content', $shop_page_id ) ) );
				if ( $shop_description ) {
					echo '<div class="product-archive__description">' . $shop_description . '</div>';
				}
			}
			?>
		</header><!-- .product-archive__header -->
	<?php endif; ?>

	<?php do_action( 'woocommerce_before_shop_loop' ); ?>

	<?php if ( woocommerce_product_loop() ) : ?>

		<ul class="product-archive__grid products">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/woocommerce/product-card' );
			endwhile;
			?>
		</ul><!-- .product-archive__grid -->

		<?php do_action( 'woocommerce_after_shop_loop' ); ?>

		<div class="product-archive__pagination">
			<?php
			woocommerce_pagination();
			?>
		</div><!-- .product-archive__pagination -->

	<?php else : ?>

		<div class="product-archive__empty">
			<p><?php esc_html_e( 'Momentálně nejsou dostupné žádné produkty.', 'jasanika' ); ?></p>
		</div><!-- .product-archive__empty -->

	<?php endif; ?>

	<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>

<?php do_action( 'woocommerce_after_main_content' ); ?>

<?php
get_footer();
