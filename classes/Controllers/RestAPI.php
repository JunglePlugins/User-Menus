<?php
/**
 * RestAPI blocks setup.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package UserMenus
 */

namespace UserMenus\Controllers;

defined( 'ABSPATH' ) || exit;

use UserMenus\Base\Controller;

/**
 * RestAPI function initialization.
 */
class RestAPI extends Controller {
	/**
	 * Initiate rest api integrations.
	 */
	public function init() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register Rest API routes.
	 */
	public function register_routes() {
		( new \UserMenus\RestAPI\BlockTypes() )->register_routes();
		( new \UserMenus\RestAPI\License() )->register_routes();
		( new \UserMenus\RestAPI\ObjectSearch() )->register_routes();
		( new \UserMenus\RestAPI\Settings() )->register_routes();
	}
}
