<?php
/**
 * Assets Controller.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers;

use UserMenus\Base\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class Assets
 */
class Assets extends Controller {

	/**
	 * Initialize the controller.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ], 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ], 0 );
		add_action( 'wp_print_scripts', [ $this, 'autoload_styles' ], 0 );
		add_action( 'admin_print_scripts', [ $this, 'autoload_styles' ], 0 );
	}

	/**
	 * Get list of plugin packages.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function get_packages() {
		$user_roles = \UserMenus\get_allowed_user_roles();

		return [
			'components'  => [
				'handle' => 'user-menus-components',
				'styles' => true,
			],
			'data'        => [
				'handle'   => 'user-menus-data',
				'varsName' => 'userMenusData',
				'vars'     => [
					'restBase' => 'user-menus/v1',
					'nonce'    => wp_create_nonce( 'wp_rest' ),
				],
			],
			'menu-editor' => [
				'handle'   => 'user-menus-menu-editor',
				'styles'   => true,
				'varsName' => 'userMenusMenuEditor',
				'vars'     => [
					'adminUrl'  => admin_url(),
					'pluginUrl' => $this->container->get_url(),
					'userRoles' => $user_roles,
					'userCodes' => $this->container->get( 'user_codes' )->get_codes_for_js(),
					'defaults'  => $this->container->get( 'menu_items' )->get_defaults(),
					'i18n'      => $this->get_menu_editor_i18n(),
				],
			],
		];
	}

	/**
	 * Get menu editor i18n strings.
	 *
	 * @return array<string,string>
	 */
	protected function get_menu_editor_i18n() {
		return [
			'whoCanSee'         => __( 'Who can see this link?', 'user-menus' ),
			'everyone'          => __( 'Everyone', 'user-menus' ),
			'loggedInUsers'     => __( 'Logged In Users', 'user-menus' ),
			'loggedOutUsers'    => __( 'Logged Out Users', 'user-menus' ),
			'chooseCanSee'      => __( 'Choose which roles can see this link', 'user-menus' ),
			'chooseCannotSee'   => __( 'Choose which roles won\'t see this link', 'user-menus' ),
			'avatarSize'        => __( 'Avatar Size', 'user-menus' ),
			'redirectTo'        => __( 'Where should users be taken afterwards?', 'user-menus' ),
			'currentPage'       => __( 'Current Page', 'user-menus' ),
			'homePage'          => __( 'Home Page', 'user-menus' ),
			'customUrl'         => __( 'Custom URL', 'user-menus' ),
			'enterCustomUrl'    => __( 'Enter a url user should be redirected to', 'user-menus' ),
			'insertUserCode'    => __( 'Insert User Code', 'user-menus' ),
			'userLink'          => __( 'User Link', 'user-menus' ),
		];
	}

	/**
	 * Register all package scripts & styles.
	 *
	 * @return void
	 */
	public function register_scripts() {
		$packages = $this->get_packages();

		foreach ( $packages as $package => $data ) {
			$handle = $data['handle'];
			$meta   = $this->get_asset_meta( $package );

			$deps = isset( $data['deps'] ) ? $data['deps'] : [];

			wp_register_script(
				$handle,
				$this->container->get_url( "dist/$package.js" ),
				array_merge( $meta['dependencies'], $deps ),
				$meta['version'],
				true
			);

			if ( isset( $data['styles'] ) && $data['styles'] ) {
				wp_register_style(
					$handle,
					$this->container->get_url( "dist/$package.css" ),
					[ 'wp-components' ],
					$meta['version']
				);
			}

			if ( isset( $data['varsName'] ) && ! empty( $data['vars'] ) ) {
				wp_localize_script( $handle, $data['varsName'], $data['vars'] );
			}

			wp_set_script_translations( $handle, 'user-menus' );
		}
	}

	/**
	 * Auto load styles if scripts are enqueued.
	 *
	 * @return void
	 */
	public function autoload_styles() {
		$packages = $this->get_packages();

		foreach ( $packages as $data ) {
			if ( wp_script_is( $data['handle'], 'enqueued' ) ) {
				if ( isset( $data['styles'] ) && $data['styles'] ) {
					wp_enqueue_style( $data['handle'] );
				}
			}
		}
	}

	/**
	 * Get asset meta from generated files.
	 *
	 * @param string $package Package name.
	 * @return array{dependencies:string[],version:string}
	 */
	public function get_asset_meta( $package ) {
		$meta_path = $this->container->get_path( "dist/$package.asset.php" );

		return file_exists( $meta_path ) ? require $meta_path : [
			'dependencies' => [],
			'version'      => $this->container->get( 'version' ),
		];
	}
}
