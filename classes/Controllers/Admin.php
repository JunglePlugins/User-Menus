<?php
/**
 * Admin controller.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers;

use UserMenus\Base\Controller;
use UserMenus\Controllers\Admin\Reviews;
use UserMenus\Controllers\Admin\SettingsPage;
use UserMenus\Controllers\Admin\Upgrades;
use UserMenus\Controllers\Admin\WidgetEditor;

defined( 'ABSPATH' ) || exit;

/**
 * Admin controller  class.
 *
 * @package UserMenus
 */
class Admin extends Controller {

	/**
	 * Initialize admin controller.
	 *
	 * @return void
	 */
	public function init() {
		$this->container->register_controllers( [
			'Admin\Reviews'      => new Reviews( $this->container ),
			'Admin\Settings'     => new SettingsPage( $this->container ),
			'Admin\Upgrades'     => new Upgrades( $this->container ),
			'Admin\WidgetEditor' => new WidgetEditor( $this->container ),
		] );
	}

}
