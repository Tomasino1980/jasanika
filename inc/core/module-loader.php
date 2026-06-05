<?php

/**
 * Jasanika Module Loader
 *
 * Helpers for module loading. Actual autoloading / file mapping will be
 * implemented in later milestones.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get module metadata by id.
 *
 * @param string $id Module ID.
 * @return array|null
 */
function jasanika_get_module( string $id ): ?array {
	$id = strtolower( preg_replace( '/[^a-z0-9_\-]/', '', trim( $id ) ) );
	$all = $GLOBALS['jasanika_module_registry'] ?? array();
	return $all[ $id ] ?? null;
}

/**
 * Simple helper to know if a module is enabled.
 *
 * @param string $id Module ID.
 * @return bool
 */
function jasanika_module_is_enabled( string $id ): bool {
	$mod = jasanika_get_module( $id );
	return ! empty( $mod ) && ! empty( $mod['enabled'] );
}
