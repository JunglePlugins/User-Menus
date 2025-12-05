<?php
/**
 * Settings REST API.
 *
 * @package UserMenus
 */

namespace UserMenus\RestAPI;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings
 */
class Settings {

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
			'/settings',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_settings' ],
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
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get settings.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_settings() {
		$options = \UserMenus\plugin( 'options' );

		return rest_ensure_response( [
			'version' => \UserMenus\config( 'version' ),
		] );
	}

	/**
	 * Update settings.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function update_settings( $request ) {
		$options = \UserMenus\plugin( 'options' );
		$params  = $request->get_json_params();

		foreach ( $params as $key => $value ) {
			$options->set( $key, $value );
		}

		return rest_ensure_response( [
			'success' => true,
		] );
	}
}
