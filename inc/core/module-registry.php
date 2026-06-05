<?php

/**
 * Jasanika Module Registry
 *
 * Responsible for registering and tracking modules for the Jasanika theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Registry storage
$GLOBALS['jasanika_module_registry'] = $GLOBALS['jasanika_module_registry'] ?? array();
$GLOBALS['jasanika_module_loaded']   = $GLOBALS['jasanika_module_loaded'] ?? array();

/**
 * Register a module.
 *
 * @param string $id   Unique module ID (lowercase, alphanumeric, dash/underscore allowed).
 * @param array  $meta Module metadata: name, version, description, enabled.
 * @return bool True on success, false on failure or duplicate.
 */
function jasanika_register_module( string $id, array $meta ): bool {
	$id = strtolower( trim( $id ) );
	// sanitize id
	$id = preg_replace( '/[^a-z0-9_\-]/', '', $id );
	if ( '' === $id ) {
		return false;
	}

	if ( isset( $GLOBALS['jasanika_module_registry'][ $id ] ) ) {
		// Already registered
		return false;
	}

	$defaults = array(
		'name'        => $id,
		'version'     => '0.0.0',
		'description' => '',
		'enabled'     => true,
	);

	$meta = array_merge( $defaults, $meta );

	$meta['id']     = $id;
	$meta['loaded'] = false;

	$GLOBALS['jasanika_module_registry'][ $id ] = $meta;

	return true;
}

/**
 * Get all registered modules.
 *
 * @return array List of module metadata arrays.
 */
function jasanika_get_registered_modules(): array {
	return array_values( $GLOBALS['jasanika_module_registry'] );
}

/**
 * Load (mark as loaded) all registered modules that are enabled.
 *
 * Note: This function currently marks modules as loaded for diagnostics compatibility.
 * Actual file-based loading will be implemented in future milestones.
 *
 * @return array List of loaded module IDs.
 */
function jasanika_load_modules(): array {
	$loaded = array();
	foreach ( $GLOBALS['jasanika_module_registry'] as $id => &$meta ) {
		if ( ! empty( $meta['enabled'] ) && empty( $meta['loaded'] ) ) {
			$meta['loaded'] = true;
			$GLOBALS['jasanika_module_loaded'][] = $meta;
			$loaded[] = $id;
		}
	}
	return $loaded;
}

/**
 * Get modules marked as loaded.
 *
 * @return array List of loaded module metadata arrays.
 */
function jasanika_get_loaded_modules(): array {
	return array_values( $GLOBALS['jasanika_module_loaded'] );
}

/**
 * Check if a module is loaded.
 *
 * @param string $id Module ID.
 * @return bool
 */
function jasanika_module_is_loaded( string $id ): bool {
	$modules = jasanika_get_loaded_modules();
	foreach ( $modules as $m ) {
		if ( isset( $m['id'] ) && $m['id'] === $id ) {
			return true;
		}
	}
	return false;
}
