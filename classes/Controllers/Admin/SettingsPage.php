<?php
/**
 * Settings Page Controller Class.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers\Admin;

use UserMenus\Base\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Settings Page Controller.
 *
 * @package UserMenus\Admin
 */
class SettingsPage extends Controller {

	/**
	 * Initialize the settings page.
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'register_page' ], 999 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Register admin options pages.
	 */
	public function register_page() {
		add_options_page(
			__( 'User Menus', 'user-menus' ),
			__( 'User Menus', 'user-menus' ),
			'manage_options',
			'user-menus-settings',
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Render settings page title & container..
	 */
	public function render_page() {
		?>
			<div id="user-menus-root-container"></div>
			<script>jQuery(() => window.userMenus.settingsPage.init());</script>
		<?php
	}

	/**
	 * Enqueue assets for the settings page.
	 *
	 * @param string $hook Page hook name.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'settings_page_user-menus-settings' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'user-menus-settings-page' );
	}

}
