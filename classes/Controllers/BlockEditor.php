<?php
/**
 * Frontend general setup.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package UserMenus
 */

namespace UserMenus\Controllers;

use UserMenus\Base\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class BlockEditor
 *
 * @version 2.0.0
 */
class BlockEditor extends Controller {

	/**
	 * Initiate hooks & filter.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue block editor assets.
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'user-menus-block-editor' );
	}

}
