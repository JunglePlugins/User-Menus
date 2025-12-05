<?php
/**
 * Menu Items Service.
 *
 * @package UserMenus
 */

namespace UserMenus\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Class MenuItems
 */
class MenuItems {

	/**
	 * Meta key for storing menu item options.
	 *
	 * @var string
	 */
	const META_KEY = '_user_menus_options';

	/**
	 * Default options.
	 *
	 * @var array<string,mixed>
	 */
	protected $defaults = [
		'avatar_size'   => 24,
		'redirect_type' => 'current',
		'redirect_url'  => '',
		'which_users'   => '',
		'can_see'       => 'yes',
		'roles'         => [],
	];

	/**
	 * Get item options.
	 *
	 * @param int $item_id Item ID.
	 * @return array<string,mixed>
	 */
	public function get_options( $item_id = 0 ) {
		$options = get_post_meta( $item_id, self::META_KEY, true );
		return $this->parse_options( $options );
	}

	/**
	 * Save item options.
	 *
	 * @param int                 $item_id Item ID.
	 * @param array<string,mixed> $options Options to save.
	 * @return bool
	 */
	public function save_options( $item_id, $options ) {
		$options = $this->parse_options( $options );

		// Sanitize roles.
		if ( 'logged_in' === $options['which_users'] ) {
			$allowed_roles = \UserMenus\get_allowed_user_roles();
			$options['roles'] = array_filter(
				$options['roles'],
				function( $role ) use ( $allowed_roles ) {
					return array_key_exists( $role, $allowed_roles );
				}
			);
		} else {
			$options['roles'] = [];
		}

		// Remove empty/default options to save space.
		$options = array_filter( $options, function( $value, $key ) {
			return $value !== $this->defaults[ $key ];
		}, ARRAY_FILTER_USE_BOTH );

		if ( ! empty( $options ) ) {
			return update_post_meta( $item_id, self::META_KEY, $options );
		} else {
			return delete_post_meta( $item_id, self::META_KEY );
		}
	}

	/**
	 * Delete item options.
	 *
	 * @param int $item_id Item ID.
	 * @return bool
	 */
	public function delete_options( $item_id ) {
		return delete_post_meta( $item_id, self::META_KEY );
	}

	/**
	 * Parse options.
	 *
	 * @param array|mixed $options Options to parse.
	 * @return array<string,mixed>
	 */
	public function parse_options( $options = [] ) {
		if ( ! is_array( $options ) ) {
			$options = [];
		}

		return wp_parse_args( $options, $this->defaults );
	}

	/**
	 * Get default options.
	 *
	 * @return array<string,mixed>
	 */
	public function get_defaults() {
		return $this->defaults;
	}

	/**
	 * Check if menu item should be visible.
	 *
	 * @param object $item Menu item object.
	 * @return bool
	 */
	public function is_visible( $item ) {
		$logged_in = is_user_logged_in();

		// Handle special user link types.
		if ( isset( $item->object ) ) {
			if ( 'logout' === $item->object ) {
				return $logged_in;
			}

			if ( 'login' === $item->object || 'register' === $item->object ) {
				return ! $logged_in;
			}
		}

		// Handle visibility settings.
		if ( isset( $item->which_users ) ) {
			switch ( $item->which_users ) {
				case 'logged_in':
					if ( ! $logged_in ) {
						return false;
					}

					// Check roles if specified.
					if ( ! empty( $item->roles ) ) {
						$can_see         = 'yes' === $item->can_see;
						$allowed_by_role = ! $can_see;

						foreach ( $item->roles as $role ) {
							if ( current_user_can( $role ) ) {
								$allowed_by_role = $can_see;
								break;
							}
						}

						return $allowed_by_role;
					}
					break;

				case 'logged_out':
					return ! $logged_in;
			}
		}

		return true;
	}
}
