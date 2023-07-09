<?php
/**
 * RestAPI Global Settings Endpoint.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package UserMenus
 */

namespace UserMenus\RestAPI;

use WP_Rest_Controller, WP_REST_Response, WP_REST_Server, WP_Error;
use function UserMenus\get_all_plugin_options;
use function UserMenus\update_options as update_plugin_options;

defined( 'ABSPATH' ) || exit;

/**
 * Rest API Settings Controller Class.
 */
class Settings extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'user-menus/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $base = 'settings';

	/**
	 * Register API endpoint routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => '__return_true', // Read only, so anyone can view.
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_settings' ],
					'permission_callback' => [ $this, 'update_settings_permissions' ],
					'args'                => $this->get_endpoint_args_for_item_schema( true ),
				],
				'schema' => [ $this, 'get_schema' ],
			]
		);
	}

	/**
	 * Get plugin settings.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_settings() {
		$settings = get_all_plugin_options();

		if ( $settings ) {
			return new WP_REST_Response( $settings, 200 );
		} else {
			return new WP_Error( '404', __( 'Something went wrong, the settings could not be found.', 'user-menus' ), [ 'status' => 404 ] );
		}
	}

	/**
	 * Update plugin settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_settings( $request ) {
		$settings = $request->get_params();

		$error_message = __( 'Something went wrong, the settings could not be updated.', 'user-menus' );

		if ( ! get_all_plugin_options() ) {
			return new WP_Error( '500', $error_message, [ 'status' => 500 ] );
		}

		update_plugin_options( $settings );
		$new_settings = get_all_plugin_options();

		if ( $new_settings ) {
			return new WP_REST_Response( $new_settings, 200 );
		} else {
			return new WP_Error( '404', $error_message, [ 'status' => 404 ] );
		}
	}

	/**
	 * Check update settings permissions.
	 *
	 * @return WP_Error|bool
	 */
	public function update_settings_permissions() {
		return current_user_can( 'manage_options' ) || current_user_can( 'activate_plugins' );
	}

	/**
	 * Get settings schema.
	 *
	 * @return array
	 */
	public function get_schema() {
		if ( $this->schema ) {
			// Bail early if already cached.
			return $this->schema;
		}

		$this->schema = apply_filters(
			'user_menus_rest_settings_schema',
			[
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'settings',
				'type'       => 'object',
				'properties' => [
					'block_controls' => [
						'type'       => 'object',
						'properties' => [
							'enable'          => [
								'type' => 'boolean',
							],
							'controls'        => [
								'type'       => 'object',
								'properties' => [
									'device_rules' => [
										'type'       => 'object',
										'properties' => [
											'enable' => [
												'type' => 'boolean',
											],
										],
									],
								],
							],
							'disabled_blocks' => [
								'type'  => 'array',
								'items' => [
									'type' => 'string',
								],
							],
						],
					],
				],
			]
		);

		return $this->schema;
	}
}
