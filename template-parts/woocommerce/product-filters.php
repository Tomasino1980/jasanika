<?php

/**
 * Product Filters
 *
 * Reusable product filter form for shop archive, product categories and product search.
 * Supports sorting, category filtering, price filtering, stock filtering and filter reset.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Build the base (reset) URL for the current archive context.
if ( is_shop() ) {
	$base_url = wc_get_page_permalink( 'shop' );
} elseif ( is_product_category() ) {
	$term     = get_queried_object();
	$base_url = ( $term instanceof WP_Term ) ? get_term_link( $term ) : wc_get_page_permalink( 'shop' );
} elseif ( jasanika_is_product_search() ) {
	$base_url = add_query_arg(
		array(
			's'         => get_search_query(),
			'post_type' => 'product',
		),
		home_url( '/' )
	);
} else {
	$base_url = wc_get_page_permalink( 'shop' );
}

// Sanitize current filter values from GET.
$current_min_price = isset( $_GET['min_price'] ) && '' !== $_GET['min_price'] ? absint( $_GET['min_price'] ) : '';
$current_max_price = isset( $_GET['max_price'] ) && '' !== $_GET['max_price'] ? absint( $_GET['max_price'] ) : '';
$current_orderby   = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : '';
$current_instock   = isset( $_GET['instock'] ) && '1' === $_GET['instock'];

// Determine whether any filters are active (excluding ordering).
$has_active_filters = '' !== $current_min_price || '' !== $current_max_price || $current_instock;

// Show category filter on shop and product search pages only.
$show_category_filter = is_shop() || jasanika_is_product_search();
$product_categories   = array();

if ( $show_category_filter ) {
	$product_categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'orderby'    => 'name',
		)
	);

	if ( is_wp_error( $product_categories ) ) {
		$product_categories = array();
	}
}

// Supported sorting options (WooCommerce native values).
$orderby_options = array(
	''           => __( 'Výchozí řazení', 'jasanika' ),
	'date'       => __( 'Nejnovější', 'jasanika' ),
	'price'      => __( 'Cena vzestupně', 'jasanika' ),
	'price-desc' => __( 'Cena sestupně', 'jasanika' ),
	'popularity' => __( 'Popularita', 'jasanika' ),
	'rating'     => __( 'Hodnocení', 'jasanika' ),
);

?>
<div class="product-filters" id="product-filters">

	<div class="product-filters__toggle-wrap">
		<button
			type="button"
			class="product-filters__toggle btn btn-outline btn-sm"
			aria-expanded="false"
			aria-controls="product-filters-body"
		>
			<svg class="product-filters__toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
				<line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>
			</svg>
			<?php esc_html_e( 'Filtry', 'jasanika' ); ?>
			<?php if ( $has_active_filters ) : ?>
				<span class="product-filters__active-badge" aria-label="<?php esc_attr_e( 'Aktivní filtry', 'jasanika' ); ?>"></span>
			<?php endif; ?>
		</button>
	</div><!-- .product-filters__toggle-wrap -->

	<div class="product-filters__body" id="product-filters-body">

		<form method="get" action="<?php echo esc_url( $base_url ); ?>" class="product-filters__form">

			<?php if ( jasanika_is_product_search() ) : ?>
				<input type="hidden" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
				<input type="hidden" name="post_type" value="product">
			<?php endif; ?>

			<!-- Sorting -->
			<div class="product-filters__section">
				<h3 class="product-filters__section-title"><?php esc_html_e( 'Řazení', 'jasanika' ); ?></h3>
				<div class="product-filters__field">
					<label for="pf-orderby" class="screen-reader-text"><?php esc_html_e( 'Řadit dle', 'jasanika' ); ?></label>
					<select name="orderby" id="pf-orderby" class="product-filters__select form-select">
						<?php foreach ( $orderby_options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_orderby, $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div><!-- .product-filters__section -->

			<?php if ( $show_category_filter && ! empty( $product_categories ) ) : ?>
			<!-- Category filter -->
			<div class="product-filters__section">
				<h3 class="product-filters__section-title"><?php esc_html_e( 'Kategorie', 'jasanika' ); ?></h3>
				<ul class="product-filters__category-list">
					<li class="product-filters__category-item <?php echo is_shop() && ! isset( $_GET['product_cat'] ) ? 'is-active' : ''; ?>">
						<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="product-filters__category-link">
							<?php esc_html_e( 'Vše', 'jasanika' ); ?>
						</a>
					</li>
					<?php foreach ( $product_categories as $cat ) : ?>
						<?php
						$cat_url = get_term_link( $cat );
						if ( is_wp_error( $cat_url ) ) {
							continue;
						}
						?>
						<li class="product-filters__category-item">
							<a href="<?php echo esc_url( $cat_url ); ?>" class="product-filters__category-link">
								<span class="product-filters__category-name"><?php echo esc_html( $cat->name ); ?></span>
								<span class="product-filters__category-count"><?php echo esc_html( number_format_i18n( $cat->count ) ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div><!-- .product-filters__section -->
			<?php endif; ?>

			<!-- Price filter -->
			<div class="product-filters__section">
				<h3 class="product-filters__section-title"><?php esc_html_e( 'Cena', 'jasanika' ); ?></h3>
				<div class="product-filters__price-range">
					<div class="product-filters__price-field">
						<label for="pf-min-price" class="product-filters__price-label"><?php esc_html_e( 'Od', 'jasanika' ); ?></label>
						<input
							type="number"
							id="pf-min-price"
							name="min_price"
							class="product-filters__price-input form-input"
							value="<?php echo esc_attr( $current_min_price ); ?>"
							min="0"
							step="1"
							placeholder="0"
						>
					</div>
					<span class="product-filters__price-separator" aria-hidden="true">&ndash;</span>
					<div class="product-filters__price-field">
						<label for="pf-max-price" class="product-filters__price-label"><?php esc_html_e( 'Do', 'jasanika' ); ?></label>
						<input
							type="number"
							id="pf-max-price"
							name="max_price"
							class="product-filters__price-input form-input"
							value="<?php echo esc_attr( $current_max_price ); ?>"
							min="0"
							step="1"
							placeholder="&infin;"
						>
					</div>
				</div>
			</div><!-- .product-filters__section -->

			<!-- Availability filter -->
			<div class="product-filters__section">
				<h3 class="product-filters__section-title"><?php esc_html_e( 'Dostupnost', 'jasanika' ); ?></h3>
				<label class="product-filters__checkbox-label">
					<input
						type="checkbox"
						name="instock"
						value="1"
						class="product-filters__checkbox"
						<?php checked( $current_instock ); ?>
					>
					<span class="product-filters__checkbox-text"><?php esc_html_e( 'Skladem', 'jasanika' ); ?></span>
				</label>
			</div><!-- .product-filters__section -->

			<!-- Actions -->
			<div class="product-filters__actions">
				<button type="submit" class="btn btn-primary btn-sm product-filters__submit">
					<?php esc_html_e( 'Filtrovat', 'jasanika' ); ?>
				</button>
				<a href="<?php echo esc_url( $base_url ); ?>" class="btn btn-ghost btn-sm product-filters__reset">
					<?php esc_html_e( 'Vymazat filtry', 'jasanika' ); ?>
				</a>
			</div><!-- .product-filters__actions -->

		</form><!-- .product-filters__form -->

	</div><!-- .product-filters__body -->

</div><!-- .product-filters -->
