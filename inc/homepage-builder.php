<?php

/**
 * Jasanika – Homepage Builder
 *
 * Registration, configuration and dynamic rendering of homepage sections.
 * The get_option() call is gated behind a static cache and is only triggered
 * when jasanika_get_homepage_sections() is first invoked (front page only).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Section Registry
// ---------------------------------------------------------------------------

/**
 * Returns the canonical homepage section registry.
 *
 * Keys are the stable section identifiers used as option key prefixes.
 * Each entry defines the human-readable label, the template-part path,
 * the default sort order and the default enabled state.
 *
 * @return array<string, array{ label: string, template: string, default_order: int, default_enabled: bool }>
 */
function jasanika_homepage_sections_registry(): array {
	return array(
		'hero_slider' => array(
			'label'           => __( 'Hero Slider', 'jasanika' ),
			'template'        => 'template-parts/components/hero-slider',
			'default_order'   => 1,
			'default_enabled' => true,
		),
		'feature_blocks' => array(
			'label'           => __( 'Feature Blocks', 'jasanika' ),
			'template'        => 'template-parts/components/feature-blocks',
			'default_order'   => 2,
			'default_enabled' => true,
		),
		'product_categories' => array(
			'label'           => __( 'Product Categories', 'jasanika' ),
			'template'        => 'template-parts/components/categories',
			'default_order'   => 3,
			'default_enabled' => true,
		),
		'latest_posts' => array(
			'label'           => __( 'Latest Posts', 'jasanika' ),
			'template'        => 'template-parts/components/latest-posts',
			'default_order'   => 4,
			'default_enabled' => true,
		),
		'cta_section' => array(
			'label'           => __( 'CTA Section', 'jasanika' ),
			'template'        => 'template-parts/components/cta-section',
			'default_order'   => 5,
			'default_enabled' => true,
		),
		'testimonials' => array(
			'label'           => __( 'Testimonials', 'jasanika' ),
			'template'        => 'template-parts/components/testimonials',
			'default_order'   => 6,
			'default_enabled' => true,
		),
	);
}

// ---------------------------------------------------------------------------
// Public API
// ---------------------------------------------------------------------------

/**
 * Returns all homepage sections with their resolved configuration.
 *
 * Configuration is read from jasanika_settings and merged with registry
 * defaults. The result is cached in a static variable so get_option() is
 * called at most once per request.
 *
 * @return array<string, array{ label: string, template: string, order: int, enabled: bool }>
 */
function jasanika_get_homepage_sections(): array {
	static $sections = null;

	if ( null !== $sections ) {
		return $sections;
	}

	$registry = jasanika_homepage_sections_registry();
	$settings = get_option( 'jasanika_settings', array() );
	$sections = array();

	foreach ( $registry as $key => $defaults ) {
		$enabled_key = 'hb_' . $key . '_enabled';
		$order_key   = 'hb_' . $key . '_order';

		$enabled = isset( $settings[ $enabled_key ] )
			? (bool) $settings[ $enabled_key ]
			: $defaults['default_enabled'];

		$order = ( isset( $settings[ $order_key ] ) && '' !== $settings[ $order_key ] )
			? (int) $settings[ $order_key ]
			: $defaults['default_order'];

		$sections[ $key ] = array(
			'label'    => $defaults['label'],
			'template' => $defaults['template'],
			'order'    => $order,
			'enabled'  => $enabled,
		);
	}

	return $sections;
}

/**
 * Returns only the enabled homepage sections, sorted ascending by order.
 *
 * Falls back gracefully: if configuration is missing, all sections are
 * returned in their default order so the homepage never breaks.
 *
 * @return array<string, array{ label: string, template: string, order: int, enabled: bool }>
 */
function jasanika_get_enabled_homepage_sections(): array {
	$sections = jasanika_get_homepage_sections();
	$enabled  = array_filter( $sections, fn( array $s ): bool => $s['enabled'] );

	uasort( $enabled, fn( array $a, array $b ): int => $a['order'] <=> $b['order'] );

	return $enabled;
}

/**
 * Renders a single homepage section by its registry key.
 *
 * @param string $key Section key (e.g. 'hero_slider').
 */
function jasanika_render_homepage_section( string $key ): void {
	$sections = jasanika_get_homepage_sections();

	if ( ! isset( $sections[ $key ] ) ) {
		return;
	}

	get_template_part( $sections[ $key ]['template'] );
}
