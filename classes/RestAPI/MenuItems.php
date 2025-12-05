<?php
/**
 * Menu Items REST API.
 *
 * @package UserMenus
 */

namespace UserMenus\RestAPI;

defined( 'ABSPATH' ) || exit;

/**
 * Class MenuItems
 */
class MenuItems {

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'user-menus/v1';

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/menu-items/(?P<id>\d+)',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'check_permission' ],
					'args'                => [
						'id' => [
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
						],
					],
				],
				[
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'check_permission' ],
					'args'                => [
						'id' => [
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
						],
					],
				],
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'check_permission' ],
					'args'                => [
						'id' => [
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
						],
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/user-roles',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_user_roles' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/user-codes',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_user_codes' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);
	}

	/**
	 * Check permission.
	 *
	 * @return bool
	 */
	public function check_permission() {
		return current_user_can( 'edit_theme_options' );
	}

	/**
	 * Get menu item options.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_item( $request ) {
		$item_id = (int) $request->get_param( 'id' );
		$service = \UserMenus\plugin( 'menu_items' );

		return rest_ensure_response( [
			'id'      => $item_id,
			'options' => $service->get_options( $item_id ),
		] );
	}

	/**
	 * Update menu item options.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$item_id = (int) $request->get_param( 'id' );
		$options = $request->get_json_params();
		$service = \UserMenus\plugin( 'menu_items' );

		$result = $service->save_options( $item_id, $options );

		if ( false === $result ) {
			return new \WP_Error(
				'save_failed',
				__( 'Failed to save menu item options.', 'user-menus' ),
				[ 'status' => 500 ]
			);
		}

		return rest_ensure_response( [
			'id'      => $item_id,
			'options' => $service->get_options( $item_id ),
		] );
	}

	/**
	 * Delete menu item options.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function delete_item( $request ) {
		$item_id = (int) $request->get_param( 'id' );
		$service = \UserMenus\plugin( 'menu_items' );

		$service->delete_options( $item_id );

		return rest_ensure_response( [
			'id'      => $item_id,
			'deleted' => true,
		] );
	}

	/**
	 * Get available user roles.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_user_roles() {
		$roles = \UserMenus\get_allowed_user_roles();

		$formatted = [];
		foreach ( $roles as $role => $name ) {
			$formatted[] = [
				'value' => $role,
				'label' => $name,
			];
		}

		return rest_ensure_response( $formatted );
	}

	/**
	 * Get available user codes.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_user_codes() {
		$service = \UserMenus\plugin( 'user_codes' );
		return rest_ensure_response( $service->get_codes_for_js() );
	}
}
