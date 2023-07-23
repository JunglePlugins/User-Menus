/**
 * Menu item component.
 */

import React from 'react';
import { MenuItem } from 'react-bootstrap';
import admin from '../user-menus.php';

// namespace CA\UM\Menu;

// if statement to check if 'ABSPATH' exists

export default Item = () => {
    /**
     * Get item options.
     */
    const get_options = ( item_id = 0 ) => {

        // Fetch all rules for this menu item.
        // const item_options = get_post_meta();

        // return static::parse_options( $item_options );
    }

    /**
     * Parse options.
     */
    const parse_options = ( options = [] ) => {
        // if statement to check if $options is not an array then returns options = [];

        // return wp_parse_args( $options, [] );
    }
}
