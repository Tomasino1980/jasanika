<?php

/**
 * Jasanika SEO
 *
 * Core SEO management system.
 * Handles meta tags, title filter, canonical URLs, Open Graph,
 * Twitter Cards and the XML sitemap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Settings Helper
// ---------------------------------------------------------------------------

/**
 * Return SEO settings merged with defaults.
 *
 * @return array<string, string>
 */
function jasanika_seo_get_settings(): array {
	$defaults = array(
		'title_template'       => '{page_title} | {site_name}',
		'meta_description'     => '',
		'meta_keywords'        => '',
		'robots_index'         => '1',
		'robots_follow'        => '1',
		'homepage_title'       => '',
		'homepage_description' => '',
		'homepage_keywords'    => '',
		'og_title'             => '',
		'og_description'       => '',
		'og_image'             => '',
		'twitter_title'        => '',
		'twitter_description'  => '',
		'twitter_image'        => '',
	);

	$saved = get_option( 'jasanika_seo_settings', array() );

	return wp_parse_args( $saved, $defaults );
}

// ---------------------------------------------------------------------------
// Title Helpers
// ---------------------------------------------------------------------------

/**
 * Resolve {site_name} and {page_title} placeholders in a title template.
 *
 * @param string $template   Template string.
 * @param string $page_title Current page title.
 * @return string
 */
function jasanika_seo_resolve_title( string $template, string $page_title ): string {
	return str_replace(
		array( '{site_name}', '{page_title}' ),
		array( get_bloginfo( 'name' ), $page_title ),
		$template
	);
}

/**
 * Filter document title parts to apply SEO Manager settings.
 *
 * Returning only the 'title' key prevents WordPress from appending the site
 * name again – the template already handles the full formatted title.
 *
 * @param array<string, string> $title_parts Assembled title parts.
 * @return array<string, string>
 */
function jasanika_seo_filter_document_title_parts( array $title_parts ): array {
	$settings = jasanika_seo_get_settings();

	// Homepage override.
	if ( is_front_page() && ! empty( $settings['homepage_title'] ) ) {
		return array( 'title' => $settings['homepage_title'] );
	}

	// Per-post override.
	if ( is_singular() ) {
		$post_seo_title = get_post_meta( get_the_ID(), '_jasanika_seo_title', true );
		if ( $post_seo_title ) {
			return array( 'title' => $post_seo_title );
		}
	}

	// Apply global title template.
	if ( ! empty( $settings['title_template'] ) ) {
		$page_title = $title_parts['title'] ?? '';
		return array( 'title' => jasanika_seo_resolve_title( $settings['title_template'], $page_title ) );
	}

	return $title_parts;
}
add_filter( 'document_title_parts', 'jasanika_seo_filter_document_title_parts', 20 );

// ---------------------------------------------------------------------------
// Page Content Helpers
// ---------------------------------------------------------------------------

/**
 * Return the meta description for the current page.
 *
 * Priority: per-post meta → homepage override → global setting.
 *
 * @return string
 */
function jasanika_seo_get_description(): string {
	$settings = jasanika_seo_get_settings();

	if ( is_front_page() && ! empty( $settings['homepage_description'] ) ) {
		return $settings['homepage_description'];
	}

	if ( is_singular() ) {
		$meta = get_post_meta( get_the_ID(), '_jasanika_seo_description', true );
		if ( $meta ) {
			return $meta;
		}
	}

	return $settings['meta_description'];
}

/**
 * Return the meta keywords for the current page.
 *
 * Priority: per-post meta → homepage override → global setting.
 *
 * @return string
 */
function jasanika_seo_get_keywords(): string {
	$settings = jasanika_seo_get_settings();

	if ( is_front_page() && ! empty( $settings['homepage_keywords'] ) ) {
		return $settings['homepage_keywords'];
	}

	if ( is_singular() ) {
		$meta = get_post_meta( get_the_ID(), '_jasanika_seo_keywords', true );
		if ( $meta ) {
			return $meta;
		}
	}

	return $settings['meta_keywords'];
}

/**
 * Return the canonical URL for the current page.
 *
 * @return string
 */
function jasanika_seo_get_canonical_url(): string {
	if ( is_singular() ) {
		return (string) get_permalink();
	}

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_home() ) {
		$page_for_posts = (int) get_option( 'page_for_posts' );
		return $page_for_posts ? (string) get_permalink( $page_for_posts ) : home_url( '/' );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? '' : $link;
	}

	if ( is_author() ) {
		return (string) get_author_posts_url( get_queried_object_id() );
	}

	if ( is_search() ) {
		return (string) get_search_link();
	}

	if ( is_archive() ) {
		$link = get_post_type_archive_link( (string) get_post_type() );
		return $link ? $link : '';
	}

	return '';
}

/**
 * Resolve the effective SEO title for use in OG / Twitter tags.
 *
 * Mirrors the logic in jasanika_seo_filter_document_title_parts() but
 * returns a plain string instead of modifying the WordPress title array.
 *
 * @return string
 */
function jasanika_seo_get_effective_title(): string {
	$settings = jasanika_seo_get_settings();

	if ( is_front_page() && ! empty( $settings['homepage_title'] ) ) {
		return $settings['homepage_title'];
	}

	if ( is_singular() ) {
		$meta = get_post_meta( get_the_ID(), '_jasanika_seo_title', true );
		if ( $meta ) {
			return $meta;
		}
	}

	// Derive a natural page title for template resolution.
	$natural = '';
	if ( is_singular() ) {
		$natural = (string) get_the_title();
	} elseif ( is_front_page() || is_home() ) {
		$natural = (string) get_bloginfo( 'name' );
	} elseif ( is_category() ) {
		$natural = (string) single_cat_title( '', false );
	} elseif ( is_tag() ) {
		$natural = (string) single_tag_title( '', false );
	} elseif ( is_search() ) {
		$natural = sprintf( __( 'Search: %s', 'jasanika' ), get_search_query() );
	} elseif ( is_archive() ) {
		$natural = (string) get_the_archive_title();
	}

	if ( ! empty( $settings['title_template'] ) ) {
		return jasanika_seo_resolve_title( $settings['title_template'], $natural );
	}

	return $natural;
}

// ---------------------------------------------------------------------------
// Meta Tag Output
// ---------------------------------------------------------------------------

/**
 * Output description, keywords and robots meta tags in <head>.
 */
function jasanika_seo_output_meta_tags(): void {
	$settings = jasanika_seo_get_settings();

	$description = jasanika_seo_get_description();
	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}

	$keywords = jasanika_seo_get_keywords();
	if ( $keywords ) {
		echo '<meta name="keywords" content="' . esc_attr( $keywords ) . '">' . "\n";
	}

	$index  = ! empty( $settings['robots_index'] )  ? 'index'  : 'noindex';
	$follow = ! empty( $settings['robots_follow'] ) ? 'follow' : 'nofollow';
	echo '<meta name="robots" content="' . esc_attr( $index . ', ' . $follow ) . '">' . "\n";
}
add_action( 'wp_head', 'jasanika_seo_output_meta_tags', 2 );

// ---------------------------------------------------------------------------
// Canonical URL
// ---------------------------------------------------------------------------

/**
 * Output the canonical link tag in <head>.
 */
function jasanika_seo_output_canonical(): void {
	$canonical = jasanika_seo_get_canonical_url();

	if ( $canonical ) {
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'jasanika_seo_output_canonical', 3 );

// ---------------------------------------------------------------------------
// Open Graph
// ---------------------------------------------------------------------------

/**
 * Output Open Graph meta tags in <head>.
 */
function jasanika_seo_output_open_graph(): void {
	$settings = jasanika_seo_get_settings();

	$og_title = ! empty( $settings['og_title'] ) ? $settings['og_title'] : jasanika_seo_get_effective_title();
	$og_desc  = ! empty( $settings['og_description'] ) ? $settings['og_description'] : jasanika_seo_get_description();
	$og_image = ! empty( $settings['og_image'] ) ? $settings['og_image'] : '';
	$og_url   = jasanika_seo_get_canonical_url();

	if ( is_singular() && has_post_thumbnail() && ! $og_image ) {
		$thumb = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		if ( $thumb ) {
			$og_image = $thumb;
		}
	}

	$og_type = is_singular( 'post' ) ? 'article' : 'website';

	echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";

	if ( $og_title ) {
		echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '">' . "\n";
	}

	if ( $og_desc ) {
		echo '<meta property="og:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
	}

	if ( $og_url ) {
		echo '<meta property="og:url" content="' . esc_url( $og_url ) . '">' . "\n";
	}

	if ( $og_image ) {
		echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'jasanika_seo_output_open_graph', 4 );

// ---------------------------------------------------------------------------
// Twitter Cards
// ---------------------------------------------------------------------------

/**
 * Output Twitter Card meta tags in <head>.
 */
function jasanika_seo_output_twitter_cards(): void {
	$settings = jasanika_seo_get_settings();

	$tw_title = ! empty( $settings['twitter_title'] ) ? $settings['twitter_title'] : jasanika_seo_get_effective_title();
	$tw_desc  = ! empty( $settings['twitter_description'] ) ? $settings['twitter_description'] : jasanika_seo_get_description();
	$tw_image = ! empty( $settings['twitter_image'] ) ? $settings['twitter_image'] : '';

	if ( is_singular() && has_post_thumbnail() && ! $tw_image ) {
		$thumb = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		if ( $thumb ) {
			$tw_image = $thumb;
		}
	}

	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

	if ( $tw_title ) {
		echo '<meta name="twitter:title" content="' . esc_attr( $tw_title ) . '">' . "\n";
	}

	if ( $tw_desc ) {
		echo '<meta name="twitter:description" content="' . esc_attr( $tw_desc ) . '">' . "\n";
	}

	if ( $tw_image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $tw_image ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'jasanika_seo_output_twitter_cards', 5 );

// ---------------------------------------------------------------------------
// XML Sitemap
// ---------------------------------------------------------------------------

/**
 * Register the sitemap rewrite rule.
 */
function jasanika_seo_register_sitemap_rewrite(): void {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?jasanika_sitemap=1', 'top' );
}
add_action( 'init', 'jasanika_seo_register_sitemap_rewrite' );

/**
 * Register the sitemap query variable.
 *
 * @param array<string> $vars Existing query vars.
 * @return array<string>
 */
function jasanika_seo_register_query_var( array $vars ): array {
	$vars[] = 'jasanika_sitemap';
	return $vars;
}
add_filter( 'query_vars', 'jasanika_seo_register_query_var' );

/**
 * Intercept requests for /sitemap.xml and output the XML.
 */
function jasanika_seo_handle_sitemap_request(): void {
	if ( ! get_query_var( 'jasanika_sitemap' ) ) {
		return;
	}

	jasanika_seo_output_sitemap();
	exit;
}
add_action( 'template_redirect', 'jasanika_seo_handle_sitemap_request' );

/**
 * Output the complete XML sitemap.
 */
function jasanika_seo_output_sitemap(): void {
	header( 'Content-Type: application/xml; charset=UTF-8' );

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

	// Homepage.
	jasanika_seo_sitemap_entry( home_url( '/' ), '1.0', 'daily' );

	// Posts.
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 500,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'fields'         => 'ids',
		)
	);

	foreach ( $posts as $post_id ) {
		jasanika_seo_sitemap_entry(
			(string) get_permalink( $post_id ),
			'0.8',
			'weekly',
			(string) get_post_modified_time( 'Y-m-d', false, $post_id )
		);
	}

	// Pages.
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'fields'         => 'ids',
		)
	);

	foreach ( $pages as $page_id ) {
		jasanika_seo_sitemap_entry(
			(string) get_permalink( $page_id ),
			'0.7',
			'monthly',
			(string) get_post_modified_time( 'Y-m-d', false, $page_id )
		);
	}

	// Products (WooCommerce).
	if ( post_type_exists( 'product' ) ) {
		$products = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 500,
				'orderby'        => 'modified',
				'order'          => 'DESC',
				'fields'         => 'ids',
			)
		);

		foreach ( $products as $product_id ) {
			jasanika_seo_sitemap_entry(
				(string) get_permalink( $product_id ),
				'0.8',
				'weekly',
				(string) get_post_modified_time( 'Y-m-d', false, $product_id )
			);
		}
	}

	// Categories.
	$categories = get_categories( array( 'hide_empty' => true ) );

	foreach ( $categories as $category ) {
		$link = get_category_link( $category->term_id );
		if ( $link && ! is_wp_error( $link ) ) {
			jasanika_seo_sitemap_entry( $link, '0.6', 'weekly' );
		}
	}

	echo '</urlset>' . "\n";
}

/**
 * Output a single <url> entry in the sitemap.
 *
 * @param string $loc        Full URL.
 * @param string $priority   Priority value (0.0–1.0).
 * @param string $changefreq Change frequency keyword.
 * @param string $lastmod    Last modified date in Y-m-d format, optional.
 */
function jasanika_seo_sitemap_entry( string $loc, string $priority, string $changefreq, string $lastmod = '' ): void {
	echo "\t<url>\n";
	echo "\t\t<loc>" . esc_url( $loc ) . "</loc>\n";

	if ( $lastmod ) {
		echo "\t\t<lastmod>" . esc_html( $lastmod ) . "</lastmod>\n";
	}

	echo "\t\t<changefreq>" . esc_html( $changefreq ) . "</changefreq>\n";
	echo "\t\t<priority>" . esc_html( $priority ) . "</priority>\n";
	echo "\t</url>\n";
}

// ---------------------------------------------------------------------------
// Rewrite Flush
// ---------------------------------------------------------------------------

/**
 * Flush rewrite rules after SEO settings are saved so the sitemap rule
 * becomes active immediately.
 *
 * @param mixed $old_value Previous option value.
 * @param mixed $new_value New option value.
 */
function jasanika_seo_flush_on_settings_save( $old_value, $new_value ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	flush_rewrite_rules();
}
add_action( 'update_option_jasanika_seo_settings', 'jasanika_seo_flush_on_settings_save', 10, 2 );

/**
 * Flush rewrite rules on theme activation.
 */
function jasanika_seo_on_switch_theme(): void {
	jasanika_seo_register_sitemap_rewrite();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'jasanika_seo_on_switch_theme' );
