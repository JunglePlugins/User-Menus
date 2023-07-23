/**
 * Nav menu custom importer component.
 */

import admin from '../user-menus.php';

// namespace CA\UM\Importer;

// if statement to check if 'ABSPATH' exists

// TODO Extend \WP_Importer
// The original class extended \WP_Importer
/**
 * Function Menu
 */
export default Menu = () => {

    // const max_wxr_version = 1.2;

    // const id;

    // const version;

    // const posts = [];

    // const base_url = '';

    // const invalid_meta_keys = [
	// 	'_wp_attached_file',
	// 	'_wp_attachment_metadata',
	// 	'_edit_lock',
	// ];

    /**
     * Registered callback function for the WordPress Importer
     */
    const dispatch = () => {
        // header();

        // const step = isset( $_GET['step'] ) ? 0 : (int) $_GET['step'];

        // switch statement with parameter $step -- case 0: greet(); break; case 1: check_admin_referer(); -- if statement with parameter of handle_upload() -- returns $file=get_attached_file($id) and set_time_limit(0) and import(file) - break;

        // footer();
    }

    /**
     * Page header.
     */
    const header = () => {
        
        // html to import Nave Menus

        // const updates = get_plugin_updates();

        // const basename = plugin_basename( __FILE__ );

        // if statement with parameter of $updates[$basename]-- returns $update = $updates[$basename] and html advising the user to update the plugin for newer exporter files.
        
    }

    /**
     * Display introductory text and file upload form
     */
    const greet = () => {
        // html to import Nave Menus

        // wp_import_upload_form( 'admin.php?import=nav_menu' );
    }

    /**
     * Handles the WXR upload and initial parsing of the file to prepare for displaying author import options
     */
    const handle_upload = () => {
        // const file = wp_import_handle_upload();

        // if statement to check if file['error] is set and returns html with error message and then returns false elseif $file['file] doesn't exist and returns html with error message and esc_html($file['file]) then returns false

        // id = (int) $file['id'];
        // const import_data = parse( $file['file'] );

        // if statement to check if import_data is_wp_error and returns html with error message and then returns false

        // version = import_data['version'];

        // if statement to check if version is not set and returns html with error message and then esc_html($import_data['version]).

        // return true;
    }

    /**
     * The main controller for the actual import stage.
     */
    /**
     * import is not allowed as a variable declaration name.
    const import = ( $file ) => {
        // add_filter();
        // add_filter();

        // import_start( $file );

        // wp_suspend_cache_invalidation( true );
		// process_nav_menu_meta();
		// wp_suspend_cache_invalidation( false );

		// $this->import_end();
    }
    */

    /**
     * Render the page footer.
     */
    const footer = () => {
        // echos a closing div tag. 
    }

    /**
     * Parse a WXR file
     */
    const parse = ( file ) => {
        // $parser = new \WXR_Parser();

        // return $parser->parse( $file );
    }

    /**
     * Parses the WXR file and prepares us for the task of processing parsed data.
     */
    const import_start = ( file ) => {
        // if statement to check if $file is not is_file then returns html with error message followed by footer() and die().

        // import_data = parse( file );

        // if statement to check if import_data is_wp_error and returns html with error message and then closing p tag and footer() and die().

        // $this->version  = $import_data['version'];
		// $this->posts    = $import_data['posts'];
		// $this->base_url = esc_url( $import_data['base_url'] );

		// do_action( 'import_start' );
    }

    /**
     * Create new menu items based on import information
     */
    const process_nav_menu_meta = () => {
        // foreach loop with parameter of $this->posts as $post -- if statement to check if $post['post_type'] is not nav_menu_item then continue

        // $post_id = $this->process_nav_menu_item( $post );

        // if statement to check if $post['postmeta'] is set then continue -- foreach loop with parameter of $post['postmeta'] as $meta returns $key=apply_filters() and sets value to false -- if statement to check if $key -- nested if statement if not value returns value = maybe_serialize() --> out of nested if statement update_post_meta() and do_action()

        // unset( $this->posts );
    }

    /**
	 * Performs post-import cleanup of files and the cache.
	 */
    const import_end = () => {
        // wp_import_cleanup( $this->id );

		// wp_cache_flush();

        // html with admin_url

        // do_action( 'import_end' );
    }

    /**
     * Decide if the given meta key maps to information we will want to import.
     */
    const is_valid_meta_key = ( key ) => {
        // if statement to check if $key is in_array() and returns false

        // return key;
    }
}
