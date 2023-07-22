/**
 * Admin menu editor component.
 */

import admin from '../user-menus.php';
import ca_user_menus from '../user-menus.php';

// if statement to check if 'ABSPATH' exists

export default function Menu_Editor() {
  const admin = new Admin();
  
  const init = () => {
    admin.init();
  }

  // const { walker } = {}
  /**
   * Override the Admin Menu Walker
   */
  const nav_menu_walker = ( walker ) => {
    // $wp_version

    // $bail_early

    // if statement to $bail_early
    // if statement to check if class exists

    // returns 'Walker_Nav_Menu_Edit_Custom_Fields'
    
  }

  /**
	 * Register metaboxes.
	 */
  const register_metaboxes = () => {
    // adds metabox
  }

  /**
   * Render nav menu metabox.
   */
  const nav_menu_metaobox = () => {
    // $nav_menu_placeholder
    // $nav_menu_selected_id

    // $link_types = []

    // foreach loop as $key => link

    // $walker

    // $removed_args = []; ?>

    // html for user-menus-div
    // <?
  }

  // const { hook } = {}
  /**
   * Enqueue scripts and styles.
   */
  const enqueue_scripts = ( hook ) => {
    // if statement to check if hook is 'nav-menus.php'
    
    // add_action

    // $suffix

    // wp_enqueue_script();
  }

  /**
   * Render media templates.
   */
  const media_templates = () => {
    // ?> html  <?
  }

}

// initialize Menu_Editor
