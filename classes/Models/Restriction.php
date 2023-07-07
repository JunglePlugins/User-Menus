<?php
/**
 * Restriction model.
 *
 * @package UserMenus\RuleEngine
 * @subpackage Models
 */

namespace UserMenus\Models;

use UserMenus\Models\RuleEngine\Query;

/**
 * Model for restriction sets.
 *
 * @package UserMenus\Models
 */
class Restriction {

	/**
	 * Post object.
	 *
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * Restriction id.
	 *
	 * @var int
	 */
	public $id = 0;

	/**
	 * Restriction slug.
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * Restriction label.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Restriction description.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Restriction Message.
	 *
	 * @var string
	 */
	public $message;

	/**
	 * Restriction status.
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Restriction Setting: Required user status.
	 *
	 * @var string 'logged_in' | 'logged_out';
	 */
	public $user_status;

	/**
	 * Restriction Setting: Which roles.
	 *
	 * @deprecated 2.0.0 Use user_roles instead.
	 *
	 * @var string[]
	 */
	public $roles;

	/**
	 * Restriction Setting: Chosen user roles.
	 *
	 * @var string[]
	 */
	public $user_roles;

	/**
	 * Restriction Setting: Role match method.
	 *
	 * @var string 'any' | 'match' | 'exclude';
	 */
	public $role_match;

	/**
	 * Restriction Setting: Protection method.
	 *
	 * @var string 'redirect' | 'message'
	 */
	public $protection_method;

	/**
	 * Restriction Setting: Redirect type.
	 *
	 * @var string 'login' | 'home' | 'custom'
	 */
	public $redirect_type;

	/**
	 * Restriction Setting: Redirect url.
	 *
	 * @var string
	 */
	public $redirect_url;

	/**
	 * Restriction Settings: Show Excerpts.
	 *
	 * @var bool
	 */
	public $show_excerpts;

	/**
	 * Restriction Settings: Override Default Message.
	 *
	 * @var bool
	 */
	public $override_message;

	/**
	 * Restriction Settings: Custom Message.
	 *
	 * @var string
	 */
	public $custom_message;

	/**
	 * Restriction Settings: Conditions.
	 *
	 * @var array
	 */
	public $conditions;

	/**
	 * Restriction Condition Query.
	 *
	 * @var Query
	 */
	public $query;

	/**
	 * Build a restriction.
	 *
	 * @param \WP_Post $restriction Restriction data.
	 */
	public function __construct( $restriction ) {
		if ( ! is_a( $restriction, '\WP_Post' ) ) {
			$this->setup_v1_restriction( $restriction );
		} else {
			$this->post = $restriction;

			$settings = get_post_meta( $restriction->ID, 'restriction_settings', true );

			$settings = wp_parse_args(
				is_array( $settings ) ? $settings : [],
				[
					'userStatus'       => 'logged_in',
					'userRoles'        => [],
					'roleMatch'        => 'any',
					'protectionMethod' => 'redirect',
					'redirectType'     => 'login',
					'redirectUrl'      => '',
					'showExcerpts'     => false,
					'overrideMessage'  => false,
					'customMessage'    => '',
					'conditions'       => [
						'logicalOperator' => 'and',
						'items'           => [],
					],
				]
			);

			// Convert keys to snake_case using camel_case_to_snake_case().
			$settings = array_combine(
				array_map( 'UserMenus\camel_case_to_snake_case', array_keys( $settings ) ),
				array_values( $settings )
			);

			$properties = array_merge(
				[
					'id'          => $restriction->ID,
					'slug'        => $restriction->post_name,
					'title'       => $restriction->post_title,
					'status'      => $restriction->post_status,
					// We set this late.. on first use.
					'description' => null,
					'message'     => null,
				],
				$settings
			);

			foreach ( $properties as $key => $value ) {
				$this->$key = $value;
			}

			$this->query = new Query( $this->conditions );
		}
	}

	public function setup_v1_restriction( $restriction ) {
		$restriction = \wp_parse_args( $restriction, [
			'title'                    => '',
			'who'                      => '',
			'roles'                    => [],
			'protection_method'        => 'redirect',
			'show_excerpts'            => false,
			'override_default_message' => false,
			'custom_message'           => '',
			'redirect_type'            => 'login',
			'redirect_url'             => '',
			'conditions'               => '',
		]);

		$this->id          = 0;
		$this->slug        = '';
		$this->title       = $restriction['title'];
		$this->description = '';
		$this->status      = 'publish';

		$user_roles = is_array( $restriction['roles'] ) ? $restriction['roles'] : [];

		$this->user_status       = $restriction['who'];
		$this->role_match        = count( $user_roles ) > 0 ? 'match' : 'any';
		$this->user_roles        = $user_roles;
		$this->protection_method = 'custom_message' === $restriction['protection_method'] ? 'message' : 'redirect';
		$this->redirect_type     = $restriction['redirect_type'];
		$this->redirect_url      = $restriction['redirect_url'];
		$this->override_message  = $restriction['override_default_message'];
		$this->custom_message    = $restriction['custom_message'];
		$this->show_excerpts     = $restriction['show_excerpts'];
		$this->conditions        = \UserMenus\remap_conditions_to_query( $restriction['conditions'] );

		$this->query = new Query( $this->conditions );
	}

	/**
	 * Check if this set has JS based rules.
	 *
	 * @return bool
	 */
	public function has_js_rules() {
		return $this->query->has_js_rules();
	}

	/**
	 * Check this sets rules.
	 *
	 * @return bool
	 */
	public function check_rules() {
		return $this->query->check_rules();
	}

	/**
	 * Check if this restriction applies to the current user.
	 *
	 * @return bool
	 */
	public function user_meets_requirements() {
		return \UserMenus\user_meets_requirements( $this->user_status, $this->user_roles, $this->role_match );
	}

	/**
	 * Get the description for this restriction.
	 *
	 * @return string
	 */
	public function get_description() {
		if ( ! isset( $this->description ) ) {
			$this->description = get_the_excerpt( $this->id );

			if ( empty( $this->description ) ) {
				$this->description = __( 'This content is restricted.', 'user-menus' );
			}
		}

		return $this->description;
	}

	/**
	 * Get the message for this restriction.
	 *
	 * @uses \get_the_content()
	 * @uses \UserMenus\get_default_denial_message()
	 *
	 * @return string
	 */
	public function get_message() {
		if ( ! isset( $this->message ) ) {
			if ( ! empty( $this->post->post_content ) ) {
				$message = \get_the_content( null, false, $this->id );
			} elseif ( ! empty( $this->custom_message ) ) {
				$message = $this->custom_message;
			} else {
				$message = \UserMenus\get_default_denial_message();
			}

			$this->message = ! empty( $message ) ?
				$message :
				__( 'This content is restricted.', 'user-menus' );
		}

		return $this->message;
	}

	/**
	 * Whether to show excerpts for posts that are restricted.
	 *
	 * @return bool
	 */
	public function show_excerpts() {
		return $this->show_excerpts;
	}

	/**
	 * Convert this restriction to an array.
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'id'               => $this->id,
			'slug'             => $this->slug,
			'title'            => $this->title,
			'description'      => $this->get_description(),
			'message'          => $this->get_message(),
			'status'           => $this->status,
			'userStatus'       => $this->user_status, // 'logged_in' | 'logged_out';
			'roleMatch'        => $this->role_match, // 'any' | 'match' | 'exclude'
			'userRoles'        => $this->user_roles,
			'protectionMethod' => $this->protection_method,
			'redirectType'     => $this->redirect_type,
			'redirectUrl'      => $this->redirect_url,
			'overrideMessage'  => $this->override_message,
			'customMessage'    => $this->custom_message,
			'showExcerpts'     => $this->show_excerpts,
			'conditions'       => $this->conditions,
		];
	}

	/**
	 * Convert this restriction to a v1 array.
	 *
	 * @return array
	 */
	public function to_v1_array() {
		return [
			'id'                       => $this->id,
			'title'                    => $this->title,
			'who'                      => $this->user_status,
			'roles'                    => $this->user_roles,
			'protection_method'        => $this->protection_method,
			'show_excerpts'            => $this->show_excerpts,
			'override_default_message' => $this->override_message,
			'custom_message'           => $this->custom_message,
			'redirect_type'            => $this->redirect_type,
			'redirect_url'             => $this->redirect_url,
			'conditions'               => $this->conditions,
		];
	}

}
