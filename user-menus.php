<?php

/**
 * Plugin Name: User Menus
 * Plugin URI: https://code-atlantic.com/
 * Description: Quickly customize your menus with a user's name & avatar, or show items based on user role.
 * Version: 2.0.0
 * Author: Code Atlantic
 * Author URI: https://code-atlantic.com/
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: user-menus
 *
 * Minimum PHP: 7.4
 * Minimum WP: 6.0
 *
 * @package UserMenus
 * @author Code Atlantic
 * @copyright Copyright (c) 2025, Code Atlantic LLC.
 */

namespace UserMenus;

defined( 'ABSPATH' ) || exit;

/**
 * Define plugin's global configuration.
 *
 * @return array<string,string|bool>
 */
function get_plugin_config() {
	return [
		'name'          => 'User Menus',
		'slug'          => 'user-menus',
		'version'       => '2.0.0',
		'option_prefix' => 'user_menus',
		'text_domain'   => 'user-menus',
		'fullname'      => 'User Menus',
		'min_php_ver'   => '7.4.0',
		'min_wp_ver'    => '6.0.0',
		'file'          => __FILE__,
		'basename'      => \plugin_basename( __FILE__ ),
		'url'           => \plugin_dir_url( __FILE__ ),
		'path'          => __DIR__ . \DIRECTORY_SEPARATOR,
	];
}

/**
 * Get config or config property.
 *
 * @param string|null $key Key of config item to return.
 *
 * @return mixed
 */
function config( $key = null ) {
	$config = get_plugin_config();

	if ( ! isset( $key ) ) {
		return $config;
	}

	return isset( $config[ $key ] ) ? $config[ $key ] : false;
}

/**
 * Register autoloader.
 */
require_once __DIR__ . '/classes/Plugin/Autoloader.php';

if ( ! Plugin\Autoloader::init( config( 'name' ), config( 'path' ) ) ) {
	return;
}

/**
 * Check plugin prerequisites.
 *
 * @return bool
 */
function check_prerequisites() {
	global $wp_version;

	$errors = [];

	if ( version_compare( PHP_VERSION, config( 'min_php_ver' ), '<' ) ) {
		$errors[] = sprintf(
			/* translators: 1: Required PHP version */
			__( 'User Menus requires PHP version %s or higher.', 'user-menus' ),
			config( 'min_php_ver' )
		);
	}

	if ( version_compare( $wp_version, config( 'min_wp_ver' ), '<' ) ) {
		$errors[] = sprintf(
			/* translators: 1: Required WordPress version */
			__( 'User Menus requires WordPress version %s or higher.', 'user-menus' ),
			config( 'min_wp_ver' )
		);
	}

	if ( ! empty( $errors ) ) {
		add_action( 'admin_notices', function() use ( $errors ) {
			foreach ( $errors as $error ) {
				printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $error ) );
			}
		} );
		return false;
	}

	return true;
}

add_action(
	'plugins_loaded',
	function () {
		if ( check_prerequisites() ) {
			plugin_instance();
		}
	},
	11
);

/**
 * Initiates and/or retrieves an encapsulated container for the plugin.
 *
 * @return \UserMenus\Plugin\Core
 */
function plugin_instance() {
	static $plugin;

	if ( ! $plugin instanceof \UserMenus\Plugin\Core ) {
		require_once __DIR__ . '/inc/functions.php';
		$plugin = new Plugin\Core( get_plugin_config() );
	}

	return $plugin;
}

/**
 * Easy access to all plugin services from the container.
 *
 * @param string|null $service_or_config Key of service or config to fetch.
 * @return \UserMenus\Plugin\Core|mixed
 */
function plugin( $service_or_config = null ) {
	if ( ! isset( $service_or_config ) ) {
		return plugin_instance();
	}

	return plugin_instance()->get( $service_or_config );
}

\register_activation_hook( __FILE__, '\UserMenus\Plugin\Install::activate_plugin' );
\register_deactivation_hook( __FILE__, '\UserMenus\Plugin\Install::deactivate_plugin' );
