<?php
/**
 * Plugin helper functions.
 *
 * @package UserMenus
 */

namespace UserMenus;

defined( 'ABSPATH' ) || exit;

/**
 * Get current URL.
 *
 * @return string
 */
function get_current_url() {
	if ( isset( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) {
		$protocol = 'https://';
	} elseif ( isset( $_SERVER['SERVER_PORT'] ) && 443 === (int) $_SERVER['SERVER_PORT'] ) {
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}

	$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	return $protocol . $host . $uri;
}

/**
 * Get all plugin options.
 *
 * @return array<string,mixed>
 */
function get_all_plugin_options() {
	return plugin( 'options' )->get_all();
}

/**
 * Get allowed user roles.
 *
 * @return array<string,string>
 */
function get_allowed_user_roles() {
	global $wp_roles;

	static $roles;

	if ( ! isset( $roles ) ) {
		$roles = apply_filters( 'user_menus_user_roles', $wp_roles->role_names );

		if ( ! is_array( $roles ) || empty( $roles ) ) {
			$roles = [];
		}
	}

	return $roles;
}

/**
 * Check if current request is REST API.
 *
 * @return bool
 */
function is_rest() {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}

	if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), rest_get_url_prefix() ) !== false ) {
		return true;
	}

	return false;
}

/**
 * Check if referrer is admin.
 *
 * @return bool
 */
function check_referrer_is_admin() {
	if ( ! isset( $_SERVER['HTTP_REFERER'] ) ) {
		return false;
	}

	$referrer  = sanitize_url( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
	$admin_url = admin_url();

	return strpos( $referrer, $admin_url ) === 0;
}

/**
 * Get redirect URL based on type.
 *
 * @param string $type        Redirect type (current, home, custom).
 * @param string $custom_url  Custom URL if type is custom.
 * @return string
 */
function get_redirect_url( $type, $custom_url = '' ) {
	switch ( $type ) {
		case 'current':
			return get_current_url();

		case 'home':
			return home_url();

		case 'custom':
			return $custom_url;

		default:
			return '';
	}
}
