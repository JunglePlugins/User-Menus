/**
 * Menu settings component.
 */

import admin from '../user-menus.php';
import ca_user_menus from '../user-menus.php';

// namespace CA\UM\Admin;

// use CA\UM\Menu\Item;

// if statement to check if 'ABSPATH' exists

/**
 * Function Menu_Settings
 */
export default function Menu_Settings() {

    /**
	 * Init
	 */
    const init = () => {
        // add_action();
    }

    // const item_id = ;
    // const item = ;
    // const depth = ;
    // const args = ;
    /**
     * Render fields for each menu item.
     */
    const fields = ( item_id, item, depth, args ) => {
        // $allowed_user_roles

        // wp_nonce_field

        // html

        // $which_users_options = [];

        // if statement to check if it's true the item is in the array and returns $redirect_types[]

        // html 

    }

    /**
     * Get array of allowed user roles.
     */
    const allowed_user_roles = () => {
        // global $wp_roles;

        // $roles = [];

        // foreach loop to check if $roles isset. --> Has nested if statement to check if $role is in an array or if it's empty, then returns $roles.

        // return $roles
    }
    
    // const menu_id = ;
    // const item_id = ;
    /**
     * Save menu item data.
     */
    const save = ( menu_id, item_id ) => {
        // $allowed_roles

        // if statement to check $_POST

        // $item_options

        // $_POST

        // if statement to check which user is ('logged_in' === $item_options['which_users']) --> has foreach loop to validate chosen roles and remove non-allowed roles. --> else unset $item_options['roles']

        // if/else statement to check if $item_options is empty or not

    }

}

// Initialize Menu_Settings
