<?php
/**
 * Plugin controller.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 *
 * @package UserMenus
 */

namespace UserMenus\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Localized container class.
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
	 * @return void
	 */
	public function __construct( $container ) {
		$this->container = $container;
	}

}
