<?php

/**
 * Cart Page
 *
 * Custom WooCommerce cart template for the Jasanika theme.
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_cart' );

?>

<?php if ( WC()->cart->is_empty() ) : ?>

	<div class="cart-empty">
		<p class="cart-empty__message"><?php esc_html_e( 'Košík je prázdný.', 'jasanika' ); ?></p>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary">
			<?php esc_html_e( 'Pokračovat v nákupu', 'jasanika' ); ?>
		</a>
	</div>

<?php else : ?>

	<div class="cart-layout">

		<div class="cart-layout__table">

			<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">

				<?php do_action( 'woocommerce_before_cart_table' ); ?>

				<table class="cart-table woocommerce-cart-form__contents" cellspacing="0">

					<thead>
						<tr>
							<th class="cart-table__col cart-table__col--image">
								<span class="screen-reader-text"><?php esc_html_e( 'Obrázek produktu', 'jasanika' ); ?></span>
							</th>
							<th class="cart-table__col cart-table__col--name"><?php esc_html_e( 'Produkt', 'jasanika' ); ?></th>
							<th class="cart-table__col cart-table__col--price"><?php esc_html_e( 'Cena', 'jasanika' ); ?></th>
							<th class="cart-table__col cart-table__col--quantity"><?php esc_html_e( 'Množství', 'jasanika' ); ?></th>
							<th class="cart-table__col cart-table__col--subtotal"><?php esc_html_e( 'Mezisoučet', 'jasanika' ); ?></th>
							<th class="cart-table__col cart-table__col--remove">
								<span class="screen-reader-text"><?php esc_html_e( 'Odebrat', 'jasanika' ); ?></span>
							</th>
						</tr>
					</thead>

					<tbody>

						<?php do_action( 'woocommerce_before_cart_contents' ); ?>

						<?php
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

							$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

							if ( ! $_product || ! $_product->exists() || 0 === $cart_item['quantity'] || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
								continue;
							}

							$product_permalink = apply_filters(
								'woocommerce_cart_item_permalink',
								$_product->is_visible() ? $_product->get_permalink( $cart_item ) : '',
								$cart_item,
								$cart_item_key
							);
							?>

							<tr class="cart-table__row woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

								<td class="cart-table__cell cart-table__cell--image" data-label="<?php esc_attr_e( 'Obrázek', 'jasanika' ); ?>">
									<?php
									$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
									if ( $product_permalink ) {
										echo '<a href="' . esc_url( $product_permalink ) . '">' . $thumbnail . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									} else {
										echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
									?>
								</td>

								<td class="cart-table__cell cart-table__cell--name" data-label="<?php esc_attr_e( 'Produkt', 'jasanika' ); ?>">
									<?php
									if ( $product_permalink ) {
										echo '<a href="' . esc_url( $product_permalink ) . '" class="cart-table__product-name">' . wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ) . '</a>';
									} else {
										echo '<span class="cart-table__product-name">' . wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ) . '</span>';
									}
									do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
									echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</td>

								<td class="cart-table__cell cart-table__cell--price" data-label="<?php esc_attr_e( 'Cena', 'jasanika' ); ?>">
									<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</td>

								<td class="cart-table__cell cart-table__cell--quantity" data-label="<?php esc_attr_e( 'Množství', 'jasanika' ); ?>">
									<?php
									if ( $_product->is_sold_individually() ) {
										echo '<span class="cart-table__quantity-single">1</span>';
										echo '<input type="hidden" name="cart[' . esc_attr( $cart_item_key ) . '][qty]" value="1" />';
									} else {
										woocommerce_quantity_input(
											array(
												'input_name'   => 'cart[' . $cart_item_key . '][qty]',
												'input_value'  => $cart_item['quantity'],
												'max_value'    => $_product->get_max_purchase_quantity(),
												'min_value'    => '0',
												'product_name' => $_product->get_name(),
											),
											$_product
										);
									}
									?>
								</td>

								<td class="cart-table__cell cart-table__cell--subtotal" data-label="<?php esc_attr_e( 'Mezisoučet', 'jasanika' ); ?>">
									<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</td>

								<td class="cart-table__cell cart-table__cell--remove">
									<?php
									echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										'woocommerce_cart_item_remove_link',
										sprintf(
											'<a href="%s" class="cart-table__remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
											esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
											/* translators: %s: product name */
											esc_attr( sprintf( __( 'Odebrat %s z košíku', 'jasanika' ), wp_strip_all_tags( $_product->get_name() ) ) ),
											esc_attr( $product_id ),
											esc_attr( $_product->get_sku() )
										),
										$cart_item_key
									);
									?>
								</td>

							</tr>

							<?php
						}
						?>

						<?php do_action( 'woocommerce_after_cart_contents' ); ?>

					</tbody>

					<tfoot>
						<tr>
							<td colspan="6" class="cart-table__actions">

								<?php do_action( 'woocommerce_cart_coupon' ); ?>

								<button type="submit" class="btn btn-ghost cart-table__update" name="update_cart" value="<?php esc_attr_e( 'Aktualizovat košík', 'jasanika' ); ?>">
									<?php esc_html_e( 'Aktualizovat košík', 'jasanika' ); ?>
								</button>

								<?php do_action( 'woocommerce_cart_actions' ); ?>

								<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>

							</td>
						</tr>
					</tfoot>

				</table>

				<?php do_action( 'woocommerce_after_cart_table' ); ?>

			</form>

		</div><!-- .cart-layout__table -->

		<div class="cart-layout__summary">
			<?php do_action( 'woocommerce_cart_collaterals' ); ?>
		</div><!-- .cart-layout__summary -->

	</div><!-- .cart-layout -->

<?php endif; ?>

<?php do_action( 'woocommerce_after_cart' ); ?>
