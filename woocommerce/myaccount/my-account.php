<?php
/**
 * My Account template
 *
 * Displays the WooCommerce My Account area with Jasanika design system styling.
 * Uses WooCommerce native navigation and content functions.
 *
 * @package Jasanika
 */

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'woocommerce_before_account_navigation' ); ?>

<div class="jasanika-account">

	<nav class="jasanika-account__nav" aria-label="<?php esc_attr_e( 'Account navigation', 'jasanika' ); ?>">
		<?php woocommerce_account_navigation(); ?>
	</nav>

	<div class="jasanika-account__content">
		<?php
		/**
		 * woocommerce_account_content hook.
		 *
		 * @hooked woocommerce_account_content - 10
		 */
		do_action( 'woocommerce_account_content' );
		?>
	</div><!-- .jasanika-account__content -->

</div><!-- .jasanika-account -->
