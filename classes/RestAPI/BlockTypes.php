<?php
/**
 * RestAPI Global Settings Endpoint.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package UserMenus
 */

namespace UserMenus\RestAPI;

use WP_Rest_Controller, WP_REST_Response, WP_REST_Server, WP_Error;
use function UserMenus\get_block_types;
use function UserMenus\update_block_types;

defined( 'ABSPATH' ) || exit;

/**
 * Rest API Settings Controller Class.
 */
class BlockTypes extends WP_REST_Controller {

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
	protected $base = 'blockTypes';

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
					'callback'            => [ $this, 'get_block_types' ],
					'permission_callback' => '__return_true', // Read only, so anyone can view.
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_block_types' ],
					'permission_callback' => [ $this, 'update_block_types_permissions' ],
					'args'                => $this->get_endpoint_args_for_item_schema( true ),
				],
				'schema' => [ $this, 'get_schema' ],
			]
		);
	}

	/**
	 * Get block type list.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_block_types() {
		$block_types = get_block_types();

		if ( $block_types ) {
			return new WP_REST_Response( $block_types, 200 );
		} else {
			return new WP_Error( '404', __( 'Something went wrong, the block types could not be found.', 'user-menus' ), [ 'status' => 404 ] );
		}
	}

	/**
	 * Update plugin settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_block_types( $request ) {
		// Get request json params.
		$block_types = $request->get_json_params();

		$error_message = __( 'Something went wrong, the block types could not be updated.', 'user-menus' );

		if ( ! is_array( get_block_types() ) ) {
			return new WP_Error( '500', $error_message, [ 'status' => 500 ] );
		}

		// TODO Add validation & schema as needed.
		update_block_types( $block_types );
		$new_block_types = get_block_types();

		if ( $new_block_types ) {
			return new WP_REST_Response( $new_block_types, 200 );
		} else {
			return new WP_Error( '404', $error_message, [ 'status' => 404 ] );
		}
	}

	/**
	 * Check update settings permissions.
	 *
	 * @return WP_Error|bool
	 */
	public function update_block_types_permissions() {
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
