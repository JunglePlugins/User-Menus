<?php
/**
 * Private Pages Controller.
 *
 * Allows private pages to be added to menus in the admin.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers\Admin;

use UserMenus\Base\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class PrivatePages
 */
class PrivatePages extends Controller {

	/**
	 * Check if controller is enabled.
	 *
	 * @return bool
	 */
	public function controller_enabled() {
		return is_admin();
	}

	/**
	 * Initialize the controller.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'pre_get_posts', [ $this, 'include_private_pages_in_menu_editor' ] );
	}

	/**
	 * Include private pages in the menu editor pages list.
	 *
	 * @param \WP_Query $query The WP_Query instance.
	 * @return void
	 */
	public function include_private_pages_in_menu_editor( $query ) {
		global $pagenow;

		// Only on nav-menus.php admin page.
		if ( 'nav-menus.php' !== $pagenow ) {
			return;
		}

		// Only for page queries.
		if ( 'page' !== $query->get( 'post_type' ) ) {
			return;
		}

		// Check if user can read private pages.
		if ( ! current_user_can( 'read_private_pages' ) ) {
			return;
		}

		// Get current post_status query.
		$post_status = $query->get( 'post_status' );

		// If post_status is not set or only 'publish', add 'private'.
		if ( empty( $post_status ) || 'publish' === $post_status ) {
			$query->set( 'post_status', [ 'publish', 'private' ] );
		} elseif ( is_array( $post_status ) && ! in_array( 'private', $post_status, true ) ) {
			$post_status[] = 'private';
			$query->set( 'post_status', $post_status );
		}
	}
}
