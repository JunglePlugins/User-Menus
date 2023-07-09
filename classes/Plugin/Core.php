<?php
/**
 * Main plugin.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 *
 * @package UserMenus
 */

namespace UserMenus\Plugin;

use UserMenus\Base\Container;
use UserMenus\Plugin\Options;
use UserMenus\Interfaces\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
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
	 * Useful to unhook actions/filters from global space.
	 *
	 * @var Container
	 */
	public $controllers = [];

	/**
	 * Initiate the plugin.
	 *
	 * @param array $config Configuration variables passed from main plugin file.
	 */
	public function __construct( $config ) {
		$this->container   = new Container( $config );
		$this->controllers = new Container();

		$this->register_services();
		$this->initiate_controllers();

		$this->check_version();

		add_action( 'init', [ $this, 'load_textdomain' ] );
	}

	/**
	 * Update & track version info.
	 */
	private function check_version() {
		$version    = $this->get( 'version' );
		$option_key = 'user_menus_version';

		$current_data = get_option( $option_key, false );

		$data = wp_parse_args(
			false !== $current_data ? $current_data : [],
			[
				'version'         => $version,
				'upgraded_from'   => null,
				'initial_version' => $version,
				'installed_on'    => gmdate( 'Y-m-d H:i:s' ),
			]
		);

		// Process old version data storage.
		if ( false === $current_data ) {
			$data = $this->process_version_data_migration( $data );
		}

		if ( version_compare( $data['version'], (string) $version, '<' ) ) {
			// Allow processing of small core upgrades.

			/**
			 * Fires when the plugin version is updated.
			 *
			 * Note: Old version is still available in options.
			 *
			 * @param string $version The new version.
			 */
			do_action( 'user_menus/update_version', $data['version'] );

			// Save Upgraded From option.
			$data['upgraded_from'] = $data['version'];
			$data['version']       = $version;
		}

		if ( $current_data !== $data ) {
			update_option( $option_key, $data );
		}
	}

	/**
	 * Process old version data.
	 *
	 * @param array $data Array of data.
	 * @return array
	 */
	private function process_version_data_migration( $data ) {
		$has_old_settings_data = get_option( 'ca_um_settings', false );
		$has_old_install_date  = get_option( 'ca_um_reviews_installed_on', false );

		// Check if old settings exist.
		if ( false !== $has_old_settings_data || false !== $has_old_install_date ) {
			$old_data = [
				'version'         => '1.1.9',
				'upgraded_from'   => null,
				'initial_version' => '1.1.9',
			];
		}

		if ( empty( $data['initial_version'] ) ) {
			$oldest_known = $data['version'];

			if ( $data['upgraded_from'] && version_compare( $data['upgraded_from'], $oldest_known, '<' ) ) {
				$oldest_known = $data['upgraded_from'];
			}

			$data['initial_version'] = $oldest_known;
		}

		return $data;
	}

	/**
	 * Internationalization
	 */
	public function load_textdomain() {
		load_plugin_textdomain( $this->container['text_domain'], false, $this->get_path( 'languages' ) );
	}

	/**
	 * Add default services to our Container
	 */
	public function register_services() {
		/**
		 * Self reference for deep DI lookup.
		 */
		$this->container['plugin'] = $this;

		/**
		 * Attach our container to the global.
		 */
		$GLOBALS['user_menus'] = $this->container;

		$this->container['options'] = function( $c ) {
			return new Options( $c->get( 'option_prefix' ) );
		};

		$this->container['connect'] = function ( $c ) {
			return new \UserMenus\Plugin\Connect( $c );
		};

		$this->container['license'] = function ( $c ) {
			return new \UserMenus\Plugin\License( $c );
		};

		$this->container['logging'] = function ( $c ) {
			return new \UserMenus\Plugin\Logging( $c );
		};

		$this->container['upgrader'] = function( $c ) {
			return new \UserMenus\Plugin\Upgrader( $c );
		};

		$this->container['rules'] = function () {
			return new \UserMenus\RuleEngine\Rules();
		};

		$this->container['restrictions'] = function () {
			return new \UserMenus\Services\Restrictions();
		};
	}

	/**
	 * Initiate internal components.
	 */
	private function initiate_controllers() {
		$this->define_paths();

		$this->register_controllers( [
			'PostTypes'    => new \UserMenus\Controllers\PostTypes( $this ),
			'Assets'       => new \UserMenus\Controllers\Assets( $this ),
			'Admin'        => new \UserMenus\Controllers\Admin( $this ),
			'RestAPI'      => new \UserMenus\Controllers\RestAPI( $this ),
			'BlockEditor'  => new \UserMenus\Controllers\BlockEditor( $this ),
			'Frontend'     => new \UserMenus\Controllers\Frontend( $this ),
			'Shortcodes'   => new \UserMenus\Controllers\Shortcodes( $this ),
			'TrustedLogin' => new \UserMenus\Controllers\TrustedLogin( $this ),
		] );
	}

	/**
	 * Register controllers.
	 *
	 * @param array $controllers Array of controllers.
	 * @return void
	 */
	public function register_controllers( $controllers = [] ) {
		foreach ( $controllers as $name => $controller ) {
			if ( $controller instanceof Controller ) {
				$controller->init();
				$this->controllers->set( $name, $controller );
			}
		}
	}

	/**
	 * Initiate internal paths.
	 */
	private function define_paths() {
		/**
		 * Attach utility functions.
		 */
		$this->container['get_path'] = [ $this, 'get_path' ];
		$this->container['get_url']  = [ $this, 'get_url' ];

		// Define paths.
		$this->container['dist_path'] = $this->get_path( 'dist' ) . '/';
	}

	/**
	 * Utility method to get a path.
	 *
	 * @param string $path Subpath to return.
	 * @return string
	 */
	public function get_path( $path ) {
		return $this->container['path'] . $path;
	}

	/**
	 * Utility method to get a url.
	 *
	 * @param string $path Sub url to return.
	 * @return string
	 */
	public function get_url( $path = '' ) {
		return $this->container['url'] . $path;
	}

	/**
	 * Get item from container
	 *
	 * @param string $id Key for the item.
	 *
	 * @return mixed Current value of the item.
	 */
	public function get( $id ) {
		if ( ! $this->container->offsetExists( $id ) ) {
			return $this->controllers->get( $id );
		}

		return $this->container->get( $id );
	}

	/**
	 * Set item in container
	 *
	 * @param string $id Key for the item.
	 * @param mixed  $value Value to set.
	 *
	 * @return void
	 */
	public function set( $id, $value ) {
		$this->container->set( $id, $value );
	}

	/**
	 * Get plugin option.
	 *
	 * @param string  $key Option key.
	 * @param boolean $default_value Default value.
	 * @return mixed
	 */
	public function get_option( $key, $default_value = false ) {
		return $this->get( 'options' )->get( $key, $default_value );
	}

	/**
	 * Get plugin permissions.
	 *
	 * @return array Array of permissions.
	 */
	public function get_permissions() {
		$permissions = \UserMenus\get_default_permissions();

		$user_permisions = $this->get( 'options' )->get( 'permissions', [] );

		if ( ! empty( $user_permisions ) ) {
			foreach ( $user_permisions as $cap => $user_permission ) {
				if ( ! empty( $user_permission ) ) {
					$permissions[ $cap ] = $user_permission;
				}
			}
		}

		return $permissions;
	}

	/**
	 * Get plugin permission for capability.
	 *
	 * @param string $cap Permission key.
	 *
	 * @return string User role or cap required.
	 */
	public function get_permission( $cap ) {
		$permissions = $this->get_permissions();

		return isset( $permissions[ $cap ] ) ? $permissions[ $cap ] : 'manage_options';
	}

	/**
	 * Check if pro version is installed.
	 *
	 * @return boolean
	 */
	public function is_pro_installed() {
		return class_exists( '\UserMenusPro\Plugin\Core' ) || file_exists( WP_PLUGIN_DIR . '/user-menus-pro/user-menus-pro.php' );
	}
}
