<?php

/**
 * Account Card
 *
 * Reusable quick-navigation card for the customer dashboard.
 *
 * @package Jasanika
 *
 * @param string $args['icon']        Icon placeholder (emoji / text character).
 * @param string $args['title']       Card title.
 * @param string $args['description'] Short description.
 * @param string $args['url']         Destination URL.
 * @param string $args['link_label']  Accessible label for the link.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon       = isset( $args['icon'] )        ? $args['icon']        : '';
$title      = isset( $args['title'] )       ? $args['title']       : '';
$desc       = isset( $args['description'] ) ? $args['description'] : '';
$url        = isset( $args['url'] )         ? $args['url']         : '#';
$link_label = isset( $args['link_label'] )  ? $args['link_label']  : $title;
?>

<a href="<?php echo esc_url( $url ); ?>"
   class="jasanika-account-card"
   aria-label="<?php echo esc_attr( $link_label ); ?>">

	<span class="jasanika-account-card__icon" aria-hidden="true">
		<?php echo esc_html( $icon ); ?>
	</span>

	<span class="jasanika-account-card__title">
		<?php echo esc_html( $title ); ?>
	</span>

	<span class="jasanika-account-card__desc">
		<?php echo esc_html( $desc ); ?>
	</span>

</a>
