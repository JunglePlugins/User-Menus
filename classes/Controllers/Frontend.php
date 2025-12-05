<?php
/**
 * Frontend Controller.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers;

use UserMenus\Base\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class Frontend
 */
class Frontend extends Controller {

	/**
	 * Check if controller is enabled.
	 *
	 * @return bool
	 */
	public function controller_enabled() {
		return ! is_admin();
	}

	/**
	 * Initialize the controller.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'wp_setup_nav_menu_item', [ $this, 'merge_item_data' ] );
		add_filter( 'wp_get_nav_menu_items', [ $this, 'filter_menu_items' ] );
	}

	/**
	 * Merge item data into the menu item object.
	 *
	 * @param object $item Menu item object.
	 * @return object
	 */
	public function merge_item_data( $item ) {
		$menu_items = $this->container->get( 'menu_items' );
		$user_codes = $this->container->get( 'user_codes' );

		// Merge options into item.
		foreach ( $menu_items->get_options( $item->ID ) as $key => $value ) {
			$item->$key = $value;
		}

		// Handle special user link types.
		if ( isset( $item->object ) && in_array( $item->object, [ 'login', 'register', 'logout' ], true ) ) {
			$item->type_label = __( 'User Link', 'user-menus' );

			$redirect = \UserMenus\get_redirect_url(
				isset( $item->redirect_type ) ? $item->redirect_type : 'current',
				isset( $item->redirect_url ) ? $item->redirect_url : ''
			);

			switch ( $item->object ) {
				case 'login':
					$item->url = wp_login_url( $redirect );
					break;

				case 'register':
					$item->url = add_query_arg( [ 'redirect_to' => $redirect ], wp_registration_url() );
					break;

				case 'logout':
					$item->url = wp_logout_url( $redirect );
					break;
			}
		}

		// Process user codes in title and URL.
		$user_codes->set_current_item( $item );
		$item->title = $user_codes->process_title( $item->title );

		// Process user codes in URL (for dynamic links like ?user_id={user_id}).
		if ( ! empty( $item->url ) ) {
			$item->url = $user_codes->process_title( $item->url );
		}

		return $item;
	}

	/**
	 * Filter menu items based on visibility settings.
	 *
	 * @param array $items Menu items.
	 * @return array
	 */
	public function filter_menu_items( $items = [] ) {
		if ( empty( $items ) ) {
			return $items;
		}

		$menu_items = $this->container->get( 'menu_items' );
		$excluded   = [];

		foreach ( $items as $key => $item ) {
			// Exclude children of excluded items.
			$exclude = in_array( (int) $item->menu_item_parent, $excluded, true );

			if ( ! $exclude ) {
				$exclude = ! $menu_items->is_visible( $item );
			}

			/**
			 * Filter whether to exclude a menu item.
			 *
			 * @param bool   $exclude Whether to exclude.
			 * @param object $item    Menu item object.
			 */
			$exclude = apply_filters( 'user_menus_exclude_item', $exclude, $item );

			if ( $exclude ) {
				$excluded[] = $item->ID;
				unset( $items[ $key ] );
			}
		}

		return $items;
	}
}
