<?php
/**
 * REST API Controller.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers;

use UserMenus\Base\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class RestAPI
 */
class RestAPI extends Controller {

	/**
	 * Initialize the controller.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		( new \UserMenus\RestAPI\Settings() )->register_routes();
		( new \UserMenus\RestAPI\MenuItems() )->register_routes();
	}
}
