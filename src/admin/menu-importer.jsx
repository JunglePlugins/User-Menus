/**
 * Menu importer component.
 */

import admin from '../user-menus.php';
import ca_user_menus from '../user-menus.php';

// if statement to check if 'ABSPATH' exists

/**
 * Function Menu_Importer
 */
export default function Menu_Importer() {

    /**
	 * Init
	 */
    const init = () => {
        add_action( 'admin_enqueue_scripts', 'enqueue_scripts' );
    }

    /**
     * Register a new menu importer.
     */
    const register_importer = () => {
        // if statement to check if 'WP_LOAD_IMPORTERS' is defined. --> nested if statement to check if 'CA\UM\Importer\Menu' exists & return a require_once of importer class.

        // $importer

        // register_importer();
    }

}

// Initialize Menu_Importer
