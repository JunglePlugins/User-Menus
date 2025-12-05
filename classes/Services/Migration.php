<?php
/**
 * Migration Service.
 *
 * Handles migrating data from User Menus v1 to User Menus.
 *
 * @package UserMenus
 */

namespace UserMenus\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Class Migration
 */
class Migration {

	/**
	 * Legacy meta keys from User Menus v1.
	 *
	 * @var array<string>
	 */
	const LEGACY_META_KEYS = [
		'_menu_item_um_options',
		'_menu_item_um_nav_role',
		'_menu_item_um_which_users',
		'_menu_item_um_can_see',
		'_menu_item_um_roles',
	];

	/**
	 * Check if migration is needed.
	 *
	 * @return bool
	 */
	public function needs_migration() {
		global $wpdb;

		// Check if there are any legacy meta keys.
		foreach ( self::LEGACY_META_KEYS as $key ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
					$key
				)
			);

			if ( $count > 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Run migration.
	 *
	 * @return array{migrated:int,errors:array}
	 */
	public function run() {
		global $wpdb;

		$migrated = 0;
		$errors   = [];

		// Get all nav menu items with legacy meta.
		$items = $wpdb->get_results(
			"SELECT DISTINCT p.ID
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'nav_menu_item'
			AND pm.meta_key IN ('" . implode( "','", self::LEGACY_META_KEYS ) . "')"
		);

		if ( empty( $items ) ) {
			return [
				'migrated' => 0,
				'errors'   => [],
			];
		}

		foreach ( $items as $item ) {
			$result = $this->migrate_item( $item->ID );

			if ( is_wp_error( $result ) ) {
				$errors[] = [
					'item_id' => $item->ID,
					'error'   => $result->get_error_message(),
				];
			} else {
				$migrated++;
			}
		}

		// Mark migration as complete.
		update_option( 'user_menus_migrated_from_v1', [
			'completed_at' => current_time( 'mysql' ),
			'migrated'     => $migrated,
			'errors'       => count( $errors ),
		] );

		return [
			'migrated' => $migrated,
			'errors'   => $errors,
		];
	}

	/**
	 * Migrate a single menu item.
	 *
	 * @param int $item_id Menu item ID.
	 * @return true|\WP_Error
	 */
	protected function migrate_item( $item_id ) {
		// Skip if already migrated.
		$existing = get_post_meta( $item_id, '_user_menus_options', true );
		if ( ! empty( $existing ) ) {
			return true;
		}

		// Get legacy options.
		$legacy_options = get_post_meta( $item_id, '_menu_item_um_options', true );

		if ( ! empty( $legacy_options ) && is_array( $legacy_options ) ) {
			$new_options = $this->convert_options( $legacy_options );
		} else {
			// Try to get individual meta keys (older format).
			$new_options = $this->convert_individual_meta( $item_id );
		}

		// Only save if there are actual options.
		if ( ! empty( $new_options ) ) {
			$defaults = [
				'which_users'   => '',
				'can_see'       => 'yes',
				'roles'         => [],
				'avatar_size'   => 24,
				'redirect_type' => 'current',
				'redirect_url'  => '',
			];

			// Remove default values.
			$filtered = array_filter( $new_options, function( $value, $key ) use ( $defaults ) {
				return isset( $defaults[ $key ] ) && $value !== $defaults[ $key ];
			}, ARRAY_FILTER_USE_BOTH );

			if ( ! empty( $filtered ) ) {
				update_post_meta( $item_id, '_user_menus_options', $filtered );
			}
		}

		// Clean up legacy meta.
		$this->cleanup_legacy_meta( $item_id );

		return true;
	}

	/**
	 * Convert legacy options to new format.
	 *
	 * @param array $legacy Legacy options array.
	 * @return array
	 */
	protected function convert_options( $legacy ) {
		$new = [];

		// Map field names (most are the same).
		$mapping = [
			'which_users'   => 'which_users',
			'can_see'       => 'can_see',
			'roles'         => 'roles',
			'avatar_size'   => 'avatar_size',
			'redirect_type' => 'redirect_type',
			'redirect_url'  => 'redirect_url',
			// Legacy aliases.
			'nav_role'      => 'which_users',
		];

		foreach ( $mapping as $old_key => $new_key ) {
			if ( isset( $legacy[ $old_key ] ) ) {
				$value = $legacy[ $old_key ];

				// Handle specific conversions.
				if ( 'roles' === $new_key && is_string( $value ) ) {
					$value = array_filter( explode( ',', $value ) );
				}

				if ( 'avatar_size' === $new_key ) {
					$value = absint( $value );
				}

				$new[ $new_key ] = $value;
			}
		}

		return $new;
	}

	/**
	 * Convert individual meta keys to options array.
	 *
	 * @param int $item_id Menu item ID.
	 * @return array
	 */
	protected function convert_individual_meta( $item_id ) {
		$options = [];

		$which_users = get_post_meta( $item_id, '_menu_item_um_which_users', true );
		if ( ! empty( $which_users ) ) {
			$options['which_users'] = $which_users;
		}

		$nav_role = get_post_meta( $item_id, '_menu_item_um_nav_role', true );
		if ( ! empty( $nav_role ) && empty( $options['which_users'] ) ) {
			$options['which_users'] = $nav_role;
		}

		$can_see = get_post_meta( $item_id, '_menu_item_um_can_see', true );
		if ( ! empty( $can_see ) ) {
			$options['can_see'] = $can_see;
		}

		$roles = get_post_meta( $item_id, '_menu_item_um_roles', true );
		if ( ! empty( $roles ) ) {
			if ( is_string( $roles ) ) {
				$roles = array_filter( explode( ',', $roles ) );
			}
			$options['roles'] = $roles;
		}

		return $options;
	}

	/**
	 * Clean up legacy meta.
	 *
	 * @param int $item_id Menu item ID.
	 * @return void
	 */
	protected function cleanup_legacy_meta( $item_id ) {
		foreach ( self::LEGACY_META_KEYS as $key ) {
			delete_post_meta( $item_id, $key );
		}
	}

	/**
	 * Check if migration has been completed.
	 *
	 * @return bool
	 */
	public function has_migrated() {
		return (bool) get_option( 'user_menus_migrated_from_v1', false );
	}

	/**
	 * Get migration status.
	 *
	 * @return array|false
	 */
	public function get_migration_status() {
		return get_option( 'user_menus_migrated_from_v1', false );
	}
}
