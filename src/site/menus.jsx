/**
 * Site menu component.
 */

// namespace CA\UM\Site;

// if statement to check if 'ABSPATH' exists

/**
 * Function CA\UM\Site\Menus
 */
export default Menus = () => {

    /**
	 * Init
	 */
    const init = () => {
        // add_filter()
    }

    /**
     * Exclude menu items via wp_get_nav_menu_items filter.
     */
    const exclude_menu_items = ( $items = [] ) => {
        // if statement to check if items is empty then returns items.

        // const logged_in = is_user_logged_in();
        // const excluded = [];

        // foreach loop to loop through items returns $exclude = in_array().

        /**
         * if statement to check if logout is an item which returns $exclude is equal to not logged_in.
         * elseif 'login' === $item or 'register' === $item which returns $exclude is equal to logged_in.
         * else --> if the item is an object and is set to a user returns a switch statement:
         * case 'logged_in' returns if statement is not logged_in returns $exclude = true;
         * elseif item roles is not empty
         * returns $can_see = 'yes' === $item->can_see;
         * then $allowed_by_role = ! $can_see;
         * 
         * foreach statement to loop through item roles returns if current_user_can(role)
         * 
         * if not allowed_by_role then returns $exclude = true; then break;
         * 
         * case 'logged_out' returns $exclude = $logged_in;
         * break;
         */

        // return $items;
    }
}

// Initialize Menus
 