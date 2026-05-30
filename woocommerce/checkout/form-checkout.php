<?php

/**
 * Checkout Form
 *
 * Custom WooCommerce checkout template for the Jasanika theme.
 * Two-column layout: customer details (left) + order summary (right).
 *
 * @package Jasanika
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// Guest checkout: if registration is disabled and required, block access.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'Musíte být přihlášeni, abyste mohli pokračovat v objednávce.', 'jasanika' ) ) );
	return;
}

?>

<form
	name="checkout"
	method="post"
	class="checkout woocommerce-checkout checkout-layout"
	action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
	enctype="multipart/form-data"
>

	<!-- Left column: customer information -->
	<div class="checkout-layout__customer">

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div id="customer_details">

			<!-- Billing Details -->
			<div class="checkout-card">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<!-- Shipping Details -->
			<div class="checkout-card">
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>

		</div><!-- #customer_details -->

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	</div><!-- .checkout-layout__customer -->

	<!-- Right column: order summary -->
	<div class="checkout-layout__summary">

		<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

		<div class="checkout-card checkout-order-review">

			<h3 class="checkout-order-review__title">
				<?php esc_html_e( 'Shrnutí objednávky', 'jasanika' ); ?>
			</h3>

			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			</div>

			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

		</div><!-- .checkout-card.checkout-order-review -->

	</div><!-- .checkout-layout__summary -->

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
