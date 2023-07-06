<?php
/**
 * Plugin assets controller.
 *
 * @package UserMenus\Admin
 * @copyright (c) 2023 Code Atlantic LLC.
 */

namespace UserMenus\Controllers;

use UserMenus\Base\Controller;

use function UserMenus\Rules\allowed_user_roles;

defined( 'ABSPATH' ) || exit;

/**
 * Admin assets controller.
 *
 * @package UserMenus\Admin
 */
class Assets extends Controller {

	/**
	 * Initialize the assets controller.
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_print_scripts', [ $this, 'autoload_styles_for_scripts' ], 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );
		add_action( 'admin_print_scripts', [ $this, 'autoload_styles_for_scripts' ], 0 );
	}

	/**
	 * Get list of plugin packages.
	 *
	 * @return array
	 */
	public function get_packages() {
		$permissions = $this->container->get_permissions();

		foreach ( $permissions as $permission => $cap ) {
			$permissions[ $permission ] = current_user_can( $cap );
		}

		$packages = [
			'block-editor'  => [
				'handle'   => 'user-menus-block-editor',
				'styles'   => true,
				'varsName' => 'userMenusBlockEditor',
				'vars'     => [
					'adminUrl'       => admin_url(),
					'pluginUrl'      => $this->container->get_url(),
					'advancedMode'   => $this->container->get_option( 'advanced_mode', false ),
					'allowedBlocks'  => [],
					'userRoles'      => allowed_user_roles(),
					'excludedBlocks' => [
						'core/nextpage',
						'core/freeform',
					],
					'permissions'    => $permissions,
				],
			],
			'components'    => [
				'handle' => 'user-menus-components',
				'styles' => true,
			],
			'core-data'     => [
				'handle' => 'user-menus-core-data',
				'deps'   => [
					'wp-api',
				],
			],
			'data'          => [
				'handle' => 'user-menus-data',
			],
			'fields'        => [
				'handle' => 'user-menus-fields',
			],
			'icons'         => [
				'handle' => 'user-menus-icons',
				'styles' => true,
			],
			'rule-engine'   => [
				'handle'   => 'user-menus-rule-engine',
				'varsName' => 'userMenusRuleEngine',
				'vars'     => [
					'adminUrl'        => admin_url(),
					'registeredRules' => $this->container->get( 'rules' )->get_block_editor_rules(),
				],
				'styles'   => true,
			],
			'settings-page' => [
				'handle'   => 'user-menus-settings-page',
				'varsName' => 'userMenusSettingsPage',
				'vars'     => [
					'pluginUrl'    => $this->container->get( 'url' ),
					'adminUrl'     => admin_url(),
					'restBase'     => 'user-menus/v2',
					'userRoles'    => allowed_user_roles(),
					'logUrl'       => current_user_can( 'manage_options' ) ? $this->container->get( 'logging' )->get_file_url() : false,
					'rolesAndCaps' => wp_roles()->roles,
					'version'      => $this->container->get( 'version' ),
					'permissions'  => $permissions,
				],
				'styles'   => true,
			],
			'utils'         => [
				'handle' => 'user-menus-utils',
			],
			'widget-editor' => [
				'handle' => 'user-menus-widget-editor',
				'styles' => true,
			],
		];

		return $packages;
	}

	/**
	 * Register all package scripts & styles.
	 */
	public function register_scripts() {
		$packages = $this->get_packages();

		// Register front end block styles.
		wp_register_style( 'user-menus-block-styles', $this->container->get_url( 'dist/style-block-editor.css' ), [], $this->container->get( 'version' ) );

		foreach ( $packages as $package => $package_data ) {
			$handle = $package_data['handle'];
			$meta   = $this->get_asset_meta( $package );

			$js_deps = isset( $package_data['deps'] ) ? $package_data['deps'] : [];

			wp_register_script( $handle, $this->container->get_url( "dist/$package.js" ), array_merge( $meta['dependencies'], $js_deps ), $meta['version'], true );

			if ( isset( $package_data['styles'] ) && $package_data['styles'] ) {
				wp_register_style( $handle, $this->container->get_url( "dist/$package.css" ), [ 'wp-components', 'wp-block-editor', 'dashicons' ], $meta['version'] );
			}

			if ( isset( $package_data['varsName'] ) && ! empty( $package_data['vars'] ) ) {
				$localized_vars = apply_filters( "user-menus/{$package}_localized_vars", $package_data['vars'] );
				wp_localize_script( $handle, $package_data['varsName'], $localized_vars );
			}

			/**
			 * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
			 * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
			 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
			 */
			wp_set_script_translations( $handle, 'user-menus' );
		}
	}

	/**
	 * Auto load styles if scripts are enqueued.
	 */
	public function autoload_styles_for_scripts() {
		$packages = $this->get_packages();

		foreach ( $packages as $package => $package_data ) {
			if ( wp_script_is( $package_data['handle'], 'enqueued' ) ) {
				if ( isset( $package_data['styles'] ) && $package_data['styles'] ) {
					wp_enqueue_style( $package_data['handle'] );
				}
			}
		}
	}

	/**
	 * Get asset meta from generated files.
	 *
	 * @param string $package Package name.
	 * @return array
	 */
	public function get_asset_meta( $package ) {
		$meta_path = $this->container->get_path( "dist/$package.asset.php" );
		return file_exists( $meta_path ) ? require $meta_path : [
			'dependencies' => [],
			'version'      => $this->container->get( 'version' ),
		];
	}

}
