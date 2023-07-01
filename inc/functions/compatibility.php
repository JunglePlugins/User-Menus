<?php
/**
 * Compatibility functions.
 *
 * @package UserMenus
 */

namespace UserMenus;

defined( 'ABSPATH' ) || exit;

/**
 * Checks whether function is disabled.
 *
 * @param string $func Name of the function.
 *
 * @return bool Whether or not function is disabled.
 */
function is_func_disabled( $func ) {
	$disabled = explode( ',', ini_get( 'disable_functions' ) );

	return in_array( $func, $disabled, true );
}

if ( ! function_exists( 'is_rest' ) ) {
	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings and check if `rest_route` starts with `/`
	 * Case #3: It can happen that WP_Rewrite is not yet initialized,
	 *          so do this (wp-settings.php)
	 * Case #4: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @returns boolean
	 * @author matzeeable
	 */
	function is_rest() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST // (#1)
				|| isset( $_GET['rest_route'] ) // (#2)
						&& strpos( sanitize_text_field( wp_unslash( $_GET['rest_route'] ) ), '/', 0 ) === 0 ) {
				return true;
		}

		// (#3)
		global $wp_rewrite;
		if ( null === $wp_rewrite ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$wp_rewrite = new \WP_Rewrite();
		}

		// (#4)
		$rest_url    = wp_parse_url( trailingslashit( rest_url() ) );
		$current_url = wp_parse_url( add_query_arg( [] ) );
		return strpos( $current_url['path'] ? $current_url['path'] : '/', $rest_url['path'], 0 ) === 0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}
}

/**
 * Change camelCase to snake_case.
 *
 * @param string $str String to convert.
 *
 * @return string Converted string.
 */
function camel_case_to_snake_case( $str ) {
	return strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $str ) );
}
