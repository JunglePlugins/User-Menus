<?php
/**
 * Shortcode setup.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package UserMenus
 */

namespace UserMenus\Controllers;

use UserMenus\Base\Controller;

use function UserMenus\user_meets_requirements;

defined( 'ABSPATH' ) || exit;

/**
 * Class Shortcodes
 *
 * @package UserMenus
 */
class Shortcodes extends Controller {

	/**
	 * Initialize Widgets
	 */
	public function init() {
		add_shortcode( 'user_menus', [ $this, 'user_menus' ] );
	}

	/**
	 * Process the [user_menus] shortcode.
	 *
	 * @param array  $atts Array or shortcode attributes.
	 * @param string $content Content inside shortcode.
	 *
	 * @return string
	 */
	public function user_menus( $atts, $content = '' ) {
		$atts = shortcode_atts( [
			'status'         => null, // 'logged_in' or 'logged_out
			'allowed_roles'  => null,
			'excluded_roles' => null,
			'class'          => '',
			'message'        => $this->container->get_option( 'defaultDenialMessage', '' ),
			// Deprecated.
			'logged_out'     => null, // @deprecated 2.0.0
			'roles'          => '', // @deprecated 2.0.0
		], $this->normalize_empty_atts( $atts ), 'user_menus' );

		// Handle old args.
		if ( null === $atts['status'] && isset( $atts['logged_out]'] ) && (bool) $atts['logged_out'] ) {
			// @deprecated 2.0.0
			$atts['status'] = 'logged_out';
			unset( $atts['logged_out'] );
		}

		if ( isset( $atts['roles'] ) && ! empty( $atts['roles'] ) ) {
			// @deprecated 2.0.
			$atts['allowed_roles'] = $atts['roles'];
			unset( $atts['roles'] );
		}

		if ( isset( $atts['allowed_roles'] ) && ! is_array( $atts['allowed_roles'] ) ) {
			$atts['allowed_roles'] = explode( ',', $atts['allowed_roles'] );
		}

		if ( isset( $atts['excluded_roles'] ) && ! is_array( $atts['excluded_roles'] ) ) {
			$atts['excluded_roles'] = explode( ',', $atts['excluded_roles'] );
		}

		if ( is_array( $atts['excluded_roles'] ) && count( $atts['excluded_roles'] ) ) {
			$user_roles = array_map( 'trim', $atts['excluded_roles'] );
			$match_type = 'exclude';
		} elseif ( is_array( $atts['allowed_roles'] ) && count( $atts['allowed_roles'] ) ) {
			$user_roles = array_map( 'trim', $atts['allowed_roles'] );
			$match_type = 'match';
		} else {
			$user_roles = [];
			$match_type = 'any';
		}

		$user_status = $atts['status'];
		$user_roles  = array_map( 'trim', $atts['roles'] );

		$classes = $atts['class'];

		if ( ! is_array( $classes ) ) {
			$classes = explode( ' ', $classes );
		}

		$classes[] = 'user-menus-container';
		// @deprecated 2.0.0
		$classes[] = 'ca-um';

		if ( user_meets_requirements( $user_status, $user_roles, $match_type ) ) {
			$classes[] = 'user-menus-accessible';
			// @deprecated 2.0.0
			$classes[] = 'ca-um-accessible';
			$container = '<div class="%1$s">%2$s</div>';
		} else {
			$classes[] = 'user-menus-not-accessible';
			// @deprecated 2.0.0
			$classes[] = 'ca-um-not-accessible';
			$container = '<div class="%1$s">%3$s</div>';
		}

		$classes = implode( ' ', $classes );

		return sprintf( $container, esc_attr( $classes ), do_shortcode( $content ), do_shortcode( $atts['message'] ) );
	}

	/**
	 * Takes set but empty attributes and sets them to true.
	 *
	 * These are typically valueless boolean attributes.
	 *
	 * @param array $atts Array of shortcode attributes.
	 *
	 * @return mixed
	 */
	public function normalize_empty_atts( $atts = [] ) {
		if ( ! is_array( $atts ) ) {
			if ( empty( $atts ) ) {
				$atts = [];
			}
		}

		foreach ( $atts as $attribute => $value ) {
			if ( is_int( $attribute ) ) {
				$atts[ strtolower( $value ) ] = true;
				unset( $atts[ $attribute ] );
			}
		}

		return $atts;
	}

}
