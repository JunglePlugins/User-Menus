<?php
/**
 * Frontend feed setup.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package UserMenus
 */

namespace UserMenus\Controllers\Frontend;

defined( 'ABSPATH' ) || exit;

use UserMenus\Base\Controller;

use WP_Customize_Manager;

use function UserMenus\user_meets_requirements;
use function UserMenus\Widgets\get_options as get_widget_options;

/**
 * Class UserMenus\Frontend\Widgets
 */
class Widgets extends Controller {

	/**
	 * Initialize Widgets Frontend.
	 */
	public function init() {
		add_action( 'sidebars_widgets', [ $this, 'exclude_widgets' ] );
	}

	/**
	 * Checks for and excludes widgets based on their chosen options.
	 *
	 * @param array $widget_areas An array of widget areas and their widgets.
	 *
	 * @return array The modified $widget_area array.
	 */
	public function exclude_widgets( $widget_areas ) {
		if ( is_admin() || $this->is_customize_preview() ) {
			return $widget_areas;
		}

		foreach ( $widget_areas as $widget_area => $widgets ) {
			if ( ! empty( $widgets ) && 'wp_inactive_widgets' !== $widget_area ) {
				foreach ( $widgets as $position => $widget_id ) {
					$options = get_widget_options( $widget_id );

					// If not accessible then exclude this item.

					/**
					 * Filter whether to exclude a widget.
					 *
					 * @param bool   $exclude   Whether to exclude the widget.
					 * @param array  $options   Widget options.
					 * @param string $widget_id Widget ID.
					 *
					 * @return bool
					 */
					$exclude = apply_filters(
						'user_menus/should_exclude_widget',
						! user_meets_requirements( $options['which_users'], $options['roles'] ),
						$options,
						$widget_id
					);

					// unset non-visible item.
					if ( $exclude ) {
						unset( $widget_areas[ $widget_area ][ $position ] );
					}
				}
			}
		}

		return $widget_areas;
	}

	/**
	 * Is customizer.
	 *
	 * @return boolean
	 */
	public function is_customize_preview() {
		global $wp_customize;

		return ( $wp_customize instanceof WP_Customize_Manager ) && $wp_customize->is_preview();
	}

}
