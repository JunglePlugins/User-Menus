<?php
/**
 * Menu importer class.
 *
 * @package User Menus
 */

namespace CA\UM\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class CA\UM\Admin\Menu_Importer
 */
class Menu_Importer {

	/**
	 * Init
	 */
	public static function init() {
		add_action( 'admin_init', [ __CLASS__, 'register_importer' ] );
	}

	/**
	 * Register a new menu importer.
	 *
	 * The WordPress Core Importer skips post meta for the menu items.
	 *
	 * @access private
	 * @return void
	 */
	public static function register_importer() {
		if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
			if ( ! class_exists( 'CA\UM\Importer\Menu' ) ) {
				require_once \CA_User_Menus::$DIR . 'includes/classes/importer/menu.php';
			}

			$importer = new \CA\UM\Importer\Menu();

			register_importer(
				'caum_nav_menu_importer',
				__( 'WP Nav Menus', 'user-menus' ),
				__( 'Import nav menus and other menu item meta skipped by the default importer', 'user-menus' ),
				[ $importer, 'dispatch' ]
			);
		}
	}
}

Menu_Importer::init();
