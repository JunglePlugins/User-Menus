<?php
/**
 * Base Controller.
 *
 * @package UserMenus
 */

namespace UserMenus\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Base Controller class.
 */
abstract class Controller implements \UserMenus\Interfaces\Controller {

	/**
	 * Plugin Container.
	 *
	 * @var \UserMenus\Plugin\Core
	 */
	public $container;

	/**
	 * Initialize based on dependency injection principles.
	 *
	 * @param \UserMenus\Plugin\Core $container Plugin container.
	 */
	public function __construct( $container ) {
		$this->container = $container;
	}

	/**
	 * Check if controller is enabled.
	 *
	 * @return bool
	 */
	public function controller_enabled() {
		return true;
	}
}
