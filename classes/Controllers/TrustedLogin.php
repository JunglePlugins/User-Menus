<?php
/**
 * TrustedLogin.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers;

use UserMenus\Base\Controller;
use UserMenus\Vendor\TrustedLogin\Client;
use UserMenus\Vendor\TrustedLogin\Config;

defined( 'ABSPATH' ) || exit;

/**
 * TrustedLogin.
 *
 * @package UserMenus
 */
class TrustedLogin extends Controller {

	/**
	 * TrustedLogin init.
	 */
	public function init() {
		$this->hooks();

		$config = [
			'auth'        => [
				'api_key'     => 'f97f5be6e02d1565',
				'license_key' => $this->container->get( 'license' )->get_license_key(),
			],

            TODO: /* Update the logo_url, email, website, and support_url values.*/

			'vendor'      => [
				'namespace'    => 'user-menus',
				'title'        => 'User Menus',
				'display_name' => 'User Menus Support',
				'logo_url'     => $this->container->get_url( 'assets/images/logo.svg' ),
				// 'email'        => '',
				'website'      => 'https://wordpress.org/plugins/user-menus/',
				'support_url'  => 'https://wordpress.org/support/plugin/user-menus/',
			],
			'role'        => 'administrator',
			'caps'        => [
				'add'    => [
					$this->container->get_permission( 'manage_settings' ) => __( 'This allows us to check your global restrictions and plugin settings.', 'user-menus' ),
					$this->container->get_permission( 'edit_block_controls' ) => __( 'This allows us to check your block control settings.', 'user-menus' ),
				],
				'remove' => [
					// 'delete_published_pages' => 'Your published posts cannot and will not be deleted by support staff',
					// 'manage_woocommerce'     => 'We don\'t need to manage your shop!',
				],
			],
			'decay'       => WEEK_IN_SECONDS,
			'menu'        => [
				'slug' => false,
			],
			'logging'     => [
				'enabled' => false,
			],
			'require_ssl' => false,
			'webhook'     => [
				'url'           => null,
				'debug_data'    => false,
				'create_ticket' => false,
			],
			'paths'       => [
				'js'  => $this->container->get_url( 'vendor-prefixed/trustedlogin/client/src/assets/trustedlogin.js' ),
				'css' => $this->container->get_url( 'dist/settings-page.css' ),
			],
		];

		try {
			new Client(
				new Config( $config )
			);
		} catch ( \Exception $exception ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			\error_log( $exception->getMessage() );
		}
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	/**
	 * Admin menu.
	 */
	public function admin_menu() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['page'] ) || 'grant-user-menus-access' !== $_GET['page'] ) {
			return;
		}

		add_options_page(
			__( 'User Menus Support Access', 'user-menus' ),
			__( 'User Menus Support Access', 'user-menus' ),
			$this->container->get_permission( 'manage_settings' ),
			'grant-user-menus-access',
			function() {
				// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
				do_action( 'trustedlogin/user-menus/auth_screen' );
			}
		);
	}
}
