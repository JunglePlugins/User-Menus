<?php
/**
 * Plugin container.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 *
 * @package UserMenus
 */

namespace UserMenus\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Localized controller class.
 */
interface Controller {

	/**
	 * Handle hooks & filters or various other init tasks.
	 *
	 * @return void
	 */
	public function init();
}
