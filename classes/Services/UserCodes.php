<?php
/**
 * User Codes Service.
 *
 * @package UserMenus
 */

namespace UserMenus\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Class UserCodes
 */
class UserCodes {

	/**
	 * Current menu item being processed.
	 *
	 * @var object|null
	 */
	protected $current_item = null;

	/**
	 * Get valid user codes.
	 *
	 * @return array<string,string>
	 */
	public function get_valid_codes() {
		$codes = [
			'avatar'       => __( 'Avatar', 'user-menus' ),
			'first_name'   => __( 'First Name', 'user-menus' ),
			'last_name'    => __( 'Last Name', 'user-menus' ),
			'username'     => __( 'Username', 'user-menus' ),
			'display_name' => __( 'Display Name', 'user-menus' ),
			'nickname'     => __( 'Nickname', 'user-menus' ),
			'email'        => __( 'Email', 'user-menus' ),
			'user_id'      => __( 'User ID', 'user-menus' ),
			'user_login'   => __( 'User Login', 'user-menus' ),
			'role'         => __( 'User Role', 'user-menus' ),
			'roles'        => __( 'User Roles', 'user-menus' ),
		];

		/**
		 * Filter valid user codes.
		 *
		 * @param array $codes Array of code => label pairs.
		 */
		return apply_filters( 'user_menus_valid_codes', $codes );
	}

	/**
	 * Set current item.
	 *
	 * @param object $item Menu item.
	 */
	public function set_current_item( $item ) {
		$this->current_item = $item;
	}

	/**
	 * Process title with user codes.
	 *
	 * @param string $title Menu item title.
	 * @return string
	 */
	public function process_title( $title ) {
		preg_match_all( '/{(.*?)}/', $title, $found );

		if ( count( $found[1] ) ) {
			foreach ( $found[1] as $match ) {
				$title = $this->replace_code( $title, $match );
			}
		}

		return $title;
	}

	/**
	 * Replace a user code in text.
	 *
	 * @param string $title Text to search.
	 * @param string $match Code to replace.
	 * @return string
	 */
	protected function replace_code( $title, $match ) {
		if ( empty( $match ) ) {
			return $title;
		}

		// Handle fallback syntax: {first_name||Guest}.
		if ( strpos( $match, '||' ) !== false ) {
			$matches = explode( '||', $match );
		} else {
			$matches = [ $match ];
		}

		$current_user = wp_get_current_user();
		$valid_codes  = $this->get_valid_codes();
		$replace      = '';

		foreach ( $matches as $string ) {
			$string = trim( $string );

			// Parse truncation syntax: {code|length} or {code|length...}
			$truncate_length = null;
			$truncate_ellipsis = false;
			$code_part = $string;

			if ( strpos( $string, '|' ) !== false ) {
				$parts = explode( '|', $string, 2 );
				$code_part = $parts[0];

				if ( isset( $parts[1] ) ) {
					$length_str = $parts[1];
					if ( substr( $length_str, -3 ) === '...' ) {
						$truncate_ellipsis = true;
						$length_str = substr( $length_str, 0, -3 );
					}
					$truncate_length = (int) $length_str;
				}
			}

			if ( ! array_key_exists( $code_part, $valid_codes ) ) {
				// Not a valid code - likely a fallback string.
				$replace = $string;
			} elseif ( 0 === $current_user->ID ) {
				// User not logged in - skip code.
				$replace = '';
			} else {
				$replace = $this->get_code_value( $code_part, $current_user );

				// Apply truncation if specified.
				if ( $truncate_length && strlen( $replace ) > $truncate_length ) {
					$replace = substr( $replace, 0, $truncate_length );
					if ( $truncate_ellipsis ) {
						$replace .= '...';
					}
				}
			}

			// Stop if we found a replacement.
			if ( ! empty( $replace ) ) {
				break;
			}
		}

		return str_replace( '{' . $match . '}', $replace, $title );
	}

	/**
	 * Get the value for a user code.
	 *
	 * @param string   $code User code.
	 * @param \WP_User $user User object.
	 * @return string
	 */
	protected function get_code_value( $code, $user ) {
		switch ( $code ) {
			case 'avatar':
				$size = 24;
				if ( $this->current_item && isset( $this->current_item->avatar_size ) ) {
					$size = (int) $this->current_item->avatar_size;
				}
				return get_avatar( $user->ID, $size );

			case 'first_name':
				return $user->user_firstname;

			case 'last_name':
				return $user->user_lastname;

			case 'username':
			case 'user_login':
				return $user->user_login;

			case 'display_name':
				return $user->display_name;

			case 'nickname':
				return $user->nickname;

			case 'email':
				return $user->user_email;

			case 'user_id':
				return (string) $user->ID;

			case 'role':
				$roles = $user->roles;
				if ( ! empty( $roles ) ) {
					$role_names = wp_roles()->role_names;
					$first_role = reset( $roles );
					return isset( $role_names[ $first_role ] ) ? $role_names[ $first_role ] : $first_role;
				}
				return '';

			case 'roles':
				$role_names = wp_roles()->role_names;
				$user_roles = array_map(
					function ( $role ) use ( $role_names ) {
						return isset( $role_names[ $role ] ) ? $role_names[ $role ] : $role;
					},
					$user->roles
				);
				return implode( ', ', $user_roles );

			default:
				/**
				 * Filter custom user code value.
				 *
				 * @param string   $value Default value (empty).
				 * @param string   $code  The user code.
				 * @param \WP_User $user  The current user.
				 */
				return apply_filters( 'user_menus_custom_code_value', '', $code, $user );
		}
	}

	/**
	 * Get user codes for JavaScript.
	 *
	 * @return array<int,array<string,string>>
	 */
	public function get_codes_for_js() {
		$codes = [];
		foreach ( $this->get_valid_codes() as $code => $label ) {
			$codes[] = [
				'code'  => $code,
				'label' => $label,
			];
		}
		return $codes;
	}
}
