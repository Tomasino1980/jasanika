<?php

/**
 * Account Stat
 *
 * Reusable statistics card for the customer dashboard.
 *
 * @package Jasanika
 *
 * @param string|int $args['value'] Statistic value (number).
 * @param string     $args['label'] Statistic label.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$value = isset( $args['value'] ) ? $args['value'] : '0';
$label = isset( $args['label'] ) ? $args['label'] : '';
?>

<div class="jasanika-account-stat">
	<span class="jasanika-account-stat__value">
		<?php echo esc_html( $value ); ?>
	</span>
	<span class="jasanika-account-stat__label">
		<?php echo esc_html( $label ); ?>
	</span>
</div>
