<?php
/**
 * Main plugin core.
 *
 * @package UserMenus
 */

namespace UserMenus\Plugin;

use UserMenus\Base\Container;
use UserMenus\Interfaces\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class Core
 *
 * @package UserMenus\Plugin
 */
class Core {

	/**
	 * Exposed container.
	 *
	 * @var Container
	 */
	public $container;

	/**
	 * Array of controllers.
	 *
	 * @var Container
	 */
	public $controllers;

	/**
	 * Initiate the plugin.
	 *
	 * @param array<string,string|bool> $config Configuration variables.
	 */
	public function __construct( $config ) {
		$this->container   = new Container( $config );
		$this->controllers = new Container();

		$this->register_services();
		$this->define_paths();
		$this->initiate_controllers();

		add_action( 'init', [ $this, 'load_textdomain' ] );
	}

	/**
	 * Load text domain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( $this->container['text_domain'], false, $this->get_path( 'languages' ) );
	}

	/**
	 * Add default services to our Container.
	 *
	 * @return void
	 */
	public function register_services() {
		$this->container['plugin'] = $this;
		$GLOBALS[ $this->get( 'option_prefix' ) ] = $this->container;

		$this->container['options'] = function ( $c ) {
			return new Options( $c->get( 'option_prefix' ) );
		};

		$this->container['menu_items'] = function () {
			return new \UserMenus\Services\MenuItems();
		};

		$this->container['user_codes'] = function () {
			return new \UserMenus\Services\UserCodes();
		};

		$this->container['menu_importer'] = function () {
			$importer = new \UserMenus\Services\MenuImporter();
			$importer->init();
			return $importer;
		};
	}

	/**
	 * Get registered controllers.
	 *
	 * @return array<string,Controller>
	 */
	protected function registered_controllers() {
		return [
			'Assets'       => new \UserMenus\Controllers\Assets( $this ),
			'Admin'        => new \UserMenus\Controllers\Admin( $this ),
			'RestAPI'      => new \UserMenus\Controllers\RestAPI( $this ),
			'Frontend'     => new \UserMenus\Controllers\Frontend( $this ),
			'MenuEditor'   => new \UserMenus\Controllers\Admin\MenuEditor( $this ),
			'Reviews'      => new \UserMenus\Controllers\Admin\Reviews( $this ),
			'Migration'    => new \UserMenus\Controllers\Admin\Migration( $this ),
			'PrivatePages' => new \UserMenus\Controllers\Admin\PrivatePages( $this ),
		];
	}

	/**
	 * Initiate internal controllers.
	 *
	 * @return void
	 */
	protected function initiate_controllers() {
		$this->register_controllers( $this->registered_controllers() );
	}

	/**
	 * Register controllers.
	 *
	 * @param array<string,Controller> $controllers Array of controllers.
	 * @return void
	 */
	public function register_controllers( $controllers = [] ) {
		foreach ( $controllers as $name => $controller ) {
			if ( $controller instanceof Controller ) {
				if ( $controller->controller_enabled() ) {
					$controller->init();
				}
				$this->controllers->set( $name, $controller );
			}
		}
	}

	/**
	 * Define paths.
	 *
	 * @return void
	 */
	protected function define_paths() {
		$this->container['get_path'] = [ $this, 'get_path' ];
		$this->container['get_url']  = [ $this, 'get_url' ];
		$this->container['dist_path'] = $this->get_path( 'dist' ) . '/';
	}

	/**
	 * Get path.
	 *
	 * @param string $path Subpath.
	 * @return string
	 */
	public function get_path( $path = '' ) {
		return $this->container['path'] . $path;
	}

	/**
	 * Get URL.
	 *
	 * @param string $path Sub URL.
	 * @return string
	 */
	public function get_url( $path = '' ) {
		return $this->container['url'] . $path;
	}

	/**
	 * Get item from container.
	 *
	 * @param string $id Key for the item.
	 * @return mixed
	 */
	public function get( $id ) {
		if ( $this->container->offsetExists( $id ) ) {
			return $this->container->get( $id );
		}

		if ( $this->controllers->offsetExists( $id ) ) {
			return $this->controllers->get( $id );
		}

		return null;
	}

	/**
	 * Set item in container.
	 *
	 * @param string $id    Key.
	 * @param mixed  $value Value.
	 */
	public function set( $id, $value ) {
		$this->container->set( $id, $value );
	}

	/**
	 * Get plugin option.
	 *
	 * @param string $key           Option key.
	 * @param mixed  $default_value Default value.
	 * @return mixed
	 */
	public function get_option( $key, $default_value = false ) {
		return $this->get( 'options' )->get( $key, $default_value );
	}
}
