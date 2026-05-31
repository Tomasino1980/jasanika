<?php

/**
 * Customer Dashboard
 *
 * Custom Jasanika dashboard template for the WooCommerce My Account area.
 * Displays welcome message, account statistics, quick navigation cards
 * and a recent orders summary.
 *
 * @package Jasanika
 * @see     https://woocommerce.com/document/template-structure/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();
$customer_id  = get_current_user_id();
$first_name   = $current_user->first_name ? $current_user->first_name : $current_user->display_name;

// Account statistics.
$all_order_ids = wc_get_orders(
	array(
		'customer' => $customer_id,
		'limit'    => -1,
		'return'   => 'ids',
	)
);
$total_orders = count( $all_order_ids );

$completed_order_ids = wc_get_orders(
	array(
		'customer' => $customer_id,
		'limit'    => -1,
		'return'   => 'ids',
		'status'   => 'completed',
	)
);
$completed_count = count( $completed_order_ids );

// Recent orders.
$recent_orders = wc_get_orders(
	array(
		'customer' => $customer_id,
		'limit'    => 5,
		'orderby'  => 'date',
		'order'    => 'DESC',
	)
);

// WooCommerce order status labels.
$order_statuses = wc_get_order_statuses();

// Navigation cards configuration.
$nav_cards = array(
	array(
		'icon'        => '📦',
		'title'       => __( 'Objednávky', 'jasanika' ),
		'description' => __( 'Přehled a detail vašich objednávek', 'jasanika' ),
		'url'         => wc_get_account_endpoint_url( 'orders' ),
		'link_label'  => __( 'Přejít na objednávky', 'jasanika' ),
	),
	array(
		'icon'        => '📍',
		'title'       => __( 'Adresy', 'jasanika' ),
		'description' => __( 'Správa fakturační a dodací adresy', 'jasanika' ),
		'url'         => wc_get_account_endpoint_url( 'edit-address' ),
		'link_label'  => __( 'Spravovat adresy', 'jasanika' ),
	),
	array(
		'icon'        => '👤',
		'title'       => __( 'Údaje účtu', 'jasanika' ),
		'description' => __( 'Úprava osobních údajů a hesla', 'jasanika' ),
		'url'         => wc_get_account_endpoint_url( 'edit-account' ),
		'link_label'  => __( 'Upravit údaje účtu', 'jasanika' ),
	),
	array(
		'icon'        => '⬇️',
		'title'       => __( 'Stažení', 'jasanika' ),
		'description' => __( 'Digitální produkty ke stažení', 'jasanika' ),
		'url'         => wc_get_account_endpoint_url( 'downloads' ),
		'link_label'  => __( 'Přejít na stažení', 'jasanika' ),
	),
);
?>

<div class="jasanika-dashboard">

	<?php /* Welcome ---------------------------------------------------- */ ?>
	<div class="jasanika-dashboard__welcome">
		<h2 class="jasanika-dashboard__welcome-heading">
			<?php
			printf(
				/* translators: %s: customer first name */
				esc_html__( 'Vítejte, %s', 'jasanika' ),
				esc_html( $first_name )
			);
			?>
		</h2>
		<p class="jasanika-dashboard__welcome-text">
			<?php esc_html_e( 'Zde naleznete přehled svého účtu, objednávek a nastavení.', 'jasanika' ); ?>
		</p>
	</div><!-- .jasanika-dashboard__welcome -->

	<?php /* Statistics ------------------------------------------------- */ ?>
	<div class="jasanika-dashboard__stats">
		<?php
		get_template_part(
			'template-parts/woocommerce/account-stat',
			null,
			array(
				'value' => $total_orders,
				'label' => __( 'Celkem objednávek', 'jasanika' ),
			)
		);

		get_template_part(
			'template-parts/woocommerce/account-stat',
			null,
			array(
				'value' => $completed_count,
				'label' => __( 'Dokončených objednávek', 'jasanika' ),
			)
		);
		?>
	</div><!-- .jasanika-dashboard__stats -->

	<?php /* Navigation cards ------------------------------------------- */ ?>
	<div class="jasanika-dashboard__nav-cards">
		<?php foreach ( $nav_cards as $card ) : ?>
			<?php
			get_template_part(
				'template-parts/woocommerce/account-card',
				null,
				$card
			);
			?>
		<?php endforeach; ?>
	</div><!-- .jasanika-dashboard__nav-cards -->

	<?php /* Recent orders ---------------------------------------------- */ ?>
	<div class="jasanika-dashboard__orders">

		<h3 class="jasanika-dashboard__section-heading">
			<?php esc_html_e( 'Nedávné objednávky', 'jasanika' ); ?>
		</h3>

		<?php if ( empty( $recent_orders ) ) : ?>

			<div class="jasanika-dashboard__empty">
				<p class="jasanika-dashboard__empty-text">
					<?php esc_html_e( 'Zatím nemáte žádné objednávky.', 'jasanika' ); ?>
				</p>
				<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
				   class="btn btn-primary">
					<?php esc_html_e( 'Přejít do obchodu', 'jasanika' ); ?>
				</a>
			</div><!-- .jasanika-dashboard__empty -->

		<?php else : ?>

			<div class="jasanika-dashboard__orders-table-wrap">
				<table class="jasanika-dashboard__orders-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Číslo objednávky', 'jasanika' ); ?></th>
							<th><?php esc_html_e( 'Datum', 'jasanika' ); ?></th>
							<th><?php esc_html_e( 'Stav', 'jasanika' ); ?></th>
							<th><?php esc_html_e( 'Celkem', 'jasanika' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_orders as $order ) : ?>
							<?php
							$status_key   = 'wc-' . $order->get_status();
							$status_label = isset( $order_statuses[ $status_key ] ) ? $order_statuses[ $status_key ] : ucfirst( $order->get_status() );
							$date_created = $order->get_date_created();
							?>
							<tr>
								<td class="jasanika-dashboard__orders-table-number">
									<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
										<?php echo esc_html( sprintf( '#%s', $order->get_order_number() ) ); ?>
									</a>
								</td>
								<td>
									<?php
									if ( $date_created ) {
										echo esc_html( $date_created->date_i18n( get_option( 'date_format' ) ) );
									}
									?>
								</td>
								<td>
									<span class="jasanika-dashboard__order-status jasanika-dashboard__order-status--<?php echo esc_attr( $order->get_status() ); ?>">
										<?php echo esc_html( $status_label ); ?>
									</span>
								</td>
								<td class="jasanika-dashboard__orders-table-total">
									<?php echo wp_kses_post( wc_price( $order->get_total() ) ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div><!-- .jasanika-dashboard__orders-table-wrap -->

			<div class="jasanika-dashboard__orders-footer">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"
				   class="btn btn-outline btn-sm">
					<?php esc_html_e( 'Všechny objednávky', 'jasanika' ); ?>
				</a>
			</div><!-- .jasanika-dashboard__orders-footer -->

		<?php endif; ?>

	</div><!-- .jasanika-dashboard__orders -->

</div><!-- .jasanika-dashboard -->
