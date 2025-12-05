<?php
/**
 * Plugin Install.
 *
 * @package UserMenus
 */

namespace UserMenus\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Install
 */
class Install {

	/**
	 * Activate plugin.
	 *
	 * @return void
	 */
	public static function activate_plugin() {
		// Set default options.
		$defaults = [
			'version'      => \UserMenus\config( 'version' ),
			'installed_on' => current_time( 'mysql' ),
		];

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( 'user_menus_' . $key ) ) {
				update_option( 'user_menus_' . $key, $value );
			}
		}

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate plugin.
	 *
	 * @return void
	 */
	public static function deactivate_plugin() {
		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
