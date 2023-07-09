<?php
/**
 * User Menus Migrations
 *
 * @package UserMenus\Plugin
 */

namespace UserMenus\Upgrades;

defined( 'ABSPATH' ) || exit;

use function __;
use function get_option;
use function update_option;
use function delete_option;
use function \UserMenus\update_option as update_plugin_option;

/**
 * Settings v2 migration.
 */
class Settings_2 extends \UserMenus\Base\Upgrade {

	const TYPE    = 'settings';
	const VERSION = 2;

	/**
	 * Get the label for the upgrade.
	 *
	 * @return string
	 */
	public function label() {
		return __( 'Update plugin settings', 'user-menus' );
	}

	/**
	 * Get the dependencies for this upgrade.
	 *
	 * @return string[]
	 */
	public function get_dependencies() {
		return [
			'restrictions-2',
		];
	}

	/**
	 * Run the migration.
	 *
	 * @return void|WP_Error|false
	 */
	public function run() {
		// Gets a stream or mock stream for sending events.
		$stream = $this->stream();

		$stream->start_task( __( 'Migrating plugin settings', 'user-menus' ) );

		$settings               = get_option( 'ca_um_settings', [] );
		$default_denial_message = isset( $settings['default_denial_message'] ) ? $settings['default_denial_message'] : '';

		if ( ! empty( $default_denial_message ) ) {
			update_plugin_option( 'defaultDenialMessage', $default_denial_message );
		}

		unset( $settings['default_denial_message'] );

		if ( ! empty( $settings ) ) {
			update_option( 'ca_um_settings', $settings );
		} else {
			delete_option( 'ca_um_settings' );
		}

		$stream->complete_task( __( 'Plugin settings migrated', 'user-menus' ) );
	}

}
