<?php
/**
 * User Menus Migrations
 *
 * @package UserMenus\Plugin
 */

namespace UserMenus\Upgrades;

defined( 'ABSPATH' ) || exit;

use function UserMenus\plugin;

/**
 * User meta v2 migration.
 */
class UserMeta_2 extends \UserMenus\Base\Upgrade {

	const TYPE    = 'user_meta';
	const VERSION = 2;

	/**
	 * Get the label for the upgrade.
	 *
	 * @return string
	 */
	public function label() {
		return __( 'Migrate user meta', 'user-menus' );
	}

	/**
	 * Run the migration.
	 *
	 * @return void|WP_Error|false
	 */
	public function run() {
		global $wpdb;

		// Gets a stream or mock stream for sending events.
		$stream = $this->stream();

		$stream->start_task( __( 'Migrating user meta', 'user-menus' ) );

		$remapped_keys = [
			'_ca_um_reviews_dismissed_triggers' => 'user_menus_reviews_dismissed_triggers',
			'_ca_um_reviews_last_dismissed'     => 'user_menus_reviews_last_dismissed',
		];

		// Update all keys via $wpdb.
		foreach ( $remapped_keys as $old_key => $new_key ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->usermeta} SET meta_key = %s WHERE meta_key = %s",
					$new_key,
					$old_key
				)
			);
		}

		$stream->complete_task( __( 'User meta migrated', 'user-menus' ) );
	}

}
