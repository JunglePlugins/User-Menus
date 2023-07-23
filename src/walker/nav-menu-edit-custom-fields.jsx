/**
 * Nav menu walker class for Old WP Versions.
 */

// if statement to check if Walker_Nav_Menu_Edit class does not exists returns global $wp_version; -- the nested if statement ( version_compare() ) returns require_once ABSPATH . 'wp-admin/includes/class-walker-nav-menu-edit.php''; else returns require_once ABSPATH . 'wp-admin/includes/nav-menu.php'; -- closes both if statements.

/**
 * The following extended Walker_Nav_Menu_Edit
 */
/**
 * Custom Walker for Nav Menu Editor
 *
 * Add wp_nav_menu_item_custom_fields hook to the nav menu editor.
 */
const Walker_Nav_Menu_Edit_Custom_Fields = () => {

    /**
     * Start the element output.
     */
    const start_el = ( output, item, depth = 0, args = [], id ) => {
        // returns $item_output;
        // $output .= parent::start_el( $item_output, $item, $depth, $args );

        // has note to check following for updated wp Versions
        // $output.= pregreplace();
    }

    /**
     * Get custom fields for nav menu item.
     */
    const get_custom_fields = ( item, depth, args = [], id ) => {
        // ob_start()
        // $item_id

        /**
         * Get menu item custom fields from plugins/themes
         */
        // do_action();

        // return ob_get_clean();
    }
}
