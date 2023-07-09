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

/**
 * Version 2 migration.
 */
class PluginMeta_2 extends \UserMenus\Base\Upgrade {

	const TYPE    = 'plugin_meta';
	const VERSION = 2;

	/**
	 * Get the label for the upgrade.
	 *
	 * @return string
	 */
	public function label() {
		return __( 'Update plugin meta', 'user-menus' );
	}

	/**
	 * Run the upgrade.
	 *
	 * @return void|WP_Error|false
	 */
	public function run() {
		// Gets a stream or mock stream for sending events.
		$stream = $this->stream();

		$stream->start_task( __( 'Migrating plugin meta', 'user-menus' ) );

		$remaps = [
			'ca_um_reviews_installed_on' => 'user_menus_installed_on',
		];

		foreach ( $remaps as $key => $new_key ) {
			$value = get_option( $key, null );

			if ( null !== $value ) {
				update_option( $new_key, $value );
				delete_option( $key );
			}
		}

		$stream->complete_task( __( 'Plugin meta migrated', 'user-menus' ) );
	}

}
