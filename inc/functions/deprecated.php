<?php
/**
 * Deprecated filters & functions.
 *
 * @package UserMenus
 * @subpackage Deprecated
 * @since 2.0.0
 * @copyright (c) 2023 Code Atlantic LLC
 */

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

use function UserMenus\plugin;

defined( 'ABSPATH' ) || exit;

require_once 'deprecated/class.is.php';
require_once 'deprecated/class.restrictions.php';

/**
 * Class CA_User_Menus
 *
 * @deprecated 2.0.0 Use \UserMenus\Plugin instead.
 */
class CA_User_Menus {}

/**
 * Get the User Menus plugin instance.
 *
 * @deprecated 2.0.0 Use \UserMenus\plugin() instead.
 *
 * @return \UserMenus\Plugin
 */
function ca_user_menus() {
	return \UserMenus\plugin();
}

add_filter( 'user_menus/old_conditions', function ( $conditions ) {
	if ( has_filter( 'ca_um_registered_conditions' ) ) {
		plugin( 'logging' )->log_deprecated_notice( 'filter:ca_um_registered_conditions', '2.0.0', 'filter:user_menus/old_conditions' );
		/**
		 * Filter the registered conditions.
		 *
		 * @deprecated 2.0.0
		 *
		 * @param boolean $conditions Registered conditions.
		 */
		return apply_filters( 'ca_um_registered_conditions', $conditions );
	}

	return $conditions;
}, 9 );

add_filter( 'user_menus/user_roles', function ( $roles ) {
	if ( has_filter( 'ca_um_user_roles' ) ) {
		plugin( 'logging' )->log_deprecated_notice( 'filter:ca_um_user_roles', '2.0.0', 'filter:user_menus/user_roles' );
		/**
		 * Filter the user roles that our plugin should consider.
		 *
		 * @deprecated 2.0.0
		 *
		 * @param array $roles Roles that our plugin should consider.
		 */
		return apply_filters( 'ca_um_user_roles', $roles );
	}

	return $roles;
}, 9 );

add_filter( 'user_menus/post_restricted_content', function ( $message ) {
	if ( has_filter( 'ca_um_restricted_message' ) ) {
		plugin( 'logging' )->log_deprecated_notice( 'filter:ca_um_restricted_message', '2.0.0', 'filter:user_menus/post_restricted_content' );
		/**
		 * Filter the restricted message.
		 *
		 * @deprecated 2.0.0
		 *
		 * @param string $message
		 */
		return apply_filters( 'ca_um_restricted_message', $message );
	}

	return $message;
}, 9 );

add_filter( 'user_menus/should_exclude_widget', function ( $should_exclude ) {
	if ( has_filter( 'ca_um_should_exclude_widget' ) ) {
		plugin( 'logging' )->log_deprecated_notice( 'filter:ca_um_should_exclude_widget', '2.0.0', 'filter:user_menus/should_exclude_widget' );
		/**
		 * Filter if the widget should be excluded.
		 *
		 * @deprecated 2.0.0
		 *
		 * @param boolean $should_exclude
		 */
		return apply_filters( 'ca_um_should_exclude_widget', $should_exclude );
	}

	return $should_exclude;
}, 9 );

add_filter( 'user_menus/excerpt_length', function ( $length = 50 ) {
	if ( has_filter( 'ca_um_filter_excerpt_length' ) ) {
		plugin( 'logging' )->log_deprecated_notice( 'filter:ca_um_filter_excerpt_length', '2.0.0', 'filter:user_menus/excerpt_length' );
		/**
		 * Filter the excerpt length.
		 *
		 * @deprecated 2.0.0
		 *
		 * @param array $settings
		 * @param int   $popup_id
		 */
		return apply_filters( 'ca_um_filter_excerpt_length', $length );
	}

	return $length;
}, 9 );
