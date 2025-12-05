<?php
/**
 * Admin Controller.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers;

use UserMenus\Base\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin
 *
 * Note: This plugin does NOT have a settings page.
 * All functionality is within the menu editor (nav-menus.php).
 */
class Admin extends Controller {

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
		// No settings page needed - all functionality is in the menu editor.
	}
}
