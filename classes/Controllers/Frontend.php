<?php
/**
 * Frontend general setup.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package UserMenus
 */

namespace UserMenus\Controllers;

use UserMenus\Base\Controller;

use UserMenus\Controllers\Frontend\Blocks;
use UserMenus\Controllers\Frontend\Feeds;
use UserMenus\Controllers\Frontend\Posts;
use UserMenus\Controllers\Frontend\Redirects;
use UserMenus\Controllers\Frontend\Widgets;

defined( 'ABSPATH' ) || exit;

/**
 * Class Frontend
 */
class Frontend extends Controller {

	/**
	 * Initialize Hooks & Filters
	 */
	public function init() {
		$this->container->register_controllers([
			'Frontend\Blocks'    => new Blocks( $this->container ),
			'Frontend\Feeds'     => new Feeds( $this->container ),
			'Frontend\Posts'     => new Posts( $this->container ),
			'Frontend\Redirects' => new Redirects( $this->container ),
			'Frontend\Widgets'   => new Widgets( $this->container ),
		]);

		$this->hooks();
	}

	/**
	 * Register general frontend hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_filter( 'user_menus/feed_restricted_message', '\UserMenus\append_post_excerpts', 9, 2 );
		add_filter( 'user_menus/feed_restricted_message', '\UserMenus\the_content_filters', 10 );

		add_filter( 'user_menus/post_restricted_content', '\UserMenus\append_post_excerpts', 9, 2 );
		add_filter( 'user_menus/post_restricted_content', '\UserMenus\the_content_filters', 10 );

		add_filter( 'user_menus/post_restricted_excerpt', '\UserMenus\append_post_excerpts', 9, 2 );
		add_filter( 'user_menus/post_restricted_excerpt', '\UserMenus\the_excerpt_filters', 10 );
	}

}
