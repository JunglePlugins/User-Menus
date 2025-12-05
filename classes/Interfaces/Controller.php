<?php
/**
 * Controller Interface.
 *
 * @package UserMenus
 */

namespace UserMenus\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Controller Interface.
 */
interface Controller {

	/**
	 * Initialize the controller.
	 *
	 * @return void
	 */
	public function init();

	/**
	 * Check if controller is enabled.
	 *
	 * @return bool
	 */
	public function controller_enabled();
}
