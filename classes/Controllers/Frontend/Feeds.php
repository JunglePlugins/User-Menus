<?php
/**
 * Frontend feed setup.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package UserMenus
 */

namespace UserMenus\Controllers\Frontend;

use UserMenus\Base\Controller;

use function UserMenus\content_is_restricted;
use function UserMenus\get_restricted_content_message;

defined( 'ABSPATH' ) || exit;

/**
 * Feed content restriction management.
 */
class Feeds extends Controller {

	/**
	 * Initiate functionality.
	 */
	public function init() {
		if ( \UserMenus\is_rest() ) {
			return;
		}

		add_action( 'the_excerpt', [ $this, 'filter_feed_post_content' ] );
		add_action( 'the_content', [ $this, 'filter_feed_post_content' ] );
	}

	/**
	 * Filter feed post content when needed.
	 *
	 * @param string $content Content of post being checked.
	 *
	 * @return string
	 */
	public function filter_feed_post_content( $content ) {
		$filter_name = 'user_menus/feed_restricted_message';

		if ( doing_filter( $filter_name ) ) {
			return $content;
		}

		if ( ! is_feed() || ! content_is_restricted() ) {
			return $content;
		}

		$restriction = $this->container->get( 'restrictions' )->get_applicable_restriction();

		/**
		 * Filter the message to display when a feed is restricted.
		 *
		 * @param string $message     Message to display.
		 * @param object $restriction Restriction object.
		 *
		 * @return string
		 */
		return apply_filters(
			$filter_name,
			$restriction->get_message(),
			$restriction
		);
	}

}
