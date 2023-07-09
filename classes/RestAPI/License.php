<?php
/**
 * RestAPI Global Settings Endpoint.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package UserMenus
 */

namespace UserMenus\RestAPI;

use WP_Rest_Controller, WP_REST_Response, WP_REST_Server, WP_Error, WP_REST_Request;

use function UserMenus\plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Rest API Licensing Controller Class.
 */
class License extends WP_REST_Controller {

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
	protected $base = 'license';

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
					'callback'            => [ $this, 'get_license' ],
					'permission_callback' => [ $this, 'manage_license_permissions' ],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_license_key' ],
					'permission_callback' => [ $this, 'manage_license_permissions' ],
					'args'                => [
						'licenseKey' => [
							'required'          => true,
							'validate_callback' => function ( $param, $request, $key ) {
								return is_string( $param );
							},
						],
					],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'remove_license' ],
					'permission_callback' => [ $this, 'manage_license_permissions' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/activate',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'activate_license' ],
					'permission_callback' => [ $this, 'manage_license_permissions' ],
					'args'                => [
						'licenseKey' => [
							'required'          => false,
							'validate_callback' => function ( $param, $request, $key ) {
								return is_string( $param );
							},
						],
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/deactivate',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'deactivate_license' ],
					'permission_callback' => [ $this, 'manage_license_permissions' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/status',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_license_status' ],
					'permission_callback' => [ $this, 'manage_license_permissions' ],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'refresh_license_status' ],
					'permission_callback' => [ $this, 'manage_license_permissions' ],
				],
			]
		);
	}

	/**
	 * Clean private or unnecessary data from license data before returning it.
	 *
	 * @param array $license_data License data.
	 * @return array
	 */
	public function clean_license_data( $license_data ) {
		$license_data['status'] = $this->clean_license_status( $license_data['status'] );

		return $license_data;
	}

	/**
	 * Clean license status.
	 *
	 * @param array $license_status License status.
	 * @return array
	 */
	public function clean_license_status( $license_status ) {
		// Remove customer_email, customer_name, payment_id, ..., checksum keys from status array.
		$license_status = array_diff_key(
			$license_status,
			array_flip(
				[
					'customer_email',
					'customer_name',
					'payment_id',
					'checksum',
					'item_id',
					'item_name',
				]
			)
		);

		return $license_status;
	}

	/**
	 * Get plugin license.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_license() {
		$license_data = plugin( 'license' )->get_license_data();

		if ( $license_data ) {
			return new WP_REST_Response( $this->clean_license_data( $license_data ), 200 );
		} else {
			return new WP_Error( '404', __( 'Something went wrong.', 'user-menus' ), [ 'status' => 404 ] );
		}
	}

	/**
	 * Update plugin license key.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_license_key( $request ) {
		$license_key = sanitize_text_field( $request->get_param( 'licenseKey' ) );

		try {
			$old_key = plugin( 'license' )->get_license_key();

			if ( $old_key === $license_key ) {
				$license_status = plugin( 'license' )->get_license_status();
				return new WP_REST_Response( $this->clean_license_status( $license_status ), 200 );
			}

			if ( plugin( 'license' )->is_license_active() ) {
				plugin( 'license' )->deactivate_license();
			}

			plugin( 'license' )->update_license_key( $license_key );

			$license_status = plugin( 'license' )->activate_license( $license_key );

			$response = [
				'status' => $this->clean_license_status( $license_status ),
			];

			if ( ! plugin()->is_pro_installed() ) {
				$response['connectInfo'] = plugin( 'connect' )->get_connect_info( $license_key );
			}

			return new WP_REST_Response( $response, 200 );
		} catch ( \Exception $e ) {
			$message = __( 'Something went wrong, the license key could not be saved.', 'user-menus' );

			if ( '' !== $e->getMessage() ) {
				$message = $e->getMessage();
			}

			return new WP_Error( '404', $message, [ 'status' => 404 ] );
		}
	}

	/**
	 * Remove plugin license key.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function remove_license() {
		plugin( 'license' )->remove_license();

		if ( '' === plugin( 'license' )->get_license_key() ) {
			return new WP_REST_Response( true, 200 );
		} else {
			return new WP_Error( '404', __( 'Something went wrong.', 'user-menus' ), [ 'status' => 404 ] );
		}
	}

	/**
	 * Activate plugin license.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function activate_license( $request ) {
		$license_key = sanitize_text_field( $request->get_param( 'licenseKey' ) );

		try {
			$license_status = plugin( 'license' )->activate_license( $license_key );

			$response = [
				'status' => $this->clean_license_status( $license_status ),
			];

			if ( ! plugin()->is_pro_installed() ) {
				$response['connectInfo'] = plugin( 'connect' )->get_connect_info( plugin( 'license' )->get_license_key() );
			}

			return new WP_REST_Response( $response, 200 );
		} catch ( \Exception $e ) {
			$message = __( 'Something went wrong, the license could not be activated.', 'user-menus' );

			if ( '' !== $e->getMessage() ) {
				$message = $e->getMessage();
			}

			return new WP_Error( '404', $message, [ 'status' => 404 ] );
		}
	}

	/**
	 * Deactivate plugin license.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function deactivate_license() {
		try {
			$license_status = plugin( 'license' )->deactivate_license();

			return new WP_REST_Response( $this->clean_license_status( [ 'status' => $license_status ] ), 200 );
		} catch ( \Exception $e ) {
			$message = __( 'Something went wrong, the license could not be deactivated.', 'user-menus' );

			if ( '' !== $e->getMessage() ) {
				$message = $e->getMessage();
			}

			return new WP_Error( '404', $message, [ 'status' => 404 ] );
		}
	}

	/**
	 * Get plugin license status.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_status() {
		$license_status = plugin( 'license' )->get_license_status();

		if ( $license_status ) {
			return new WP_REST_Response( [ 'status' => $this->clean_license_status( $license_status ) ], 200 );
		} else {
			return new WP_Error( '404', __( 'Something went wrong.', 'user-menus' ), [ 'status' => 404 ] );
		}
	}

	/**
	 * Refresh plugin license status.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function refresh_license_status() {
		$license_status = plugin( 'license' )->refresh_license_status();

		if ( $license_status ) {
			return new WP_REST_Response( [ 'status' => $this->clean_license_status( $license_status ) ], 200 );
		} else {
			return new WP_Error( '404', __( 'Something went wrong.', 'user-menus' ), [ 'status' => 404 ] );
		}
	}

	/**
	 * Check update settings permissions.
	 *
	 * @return bool
	 */
	public function manage_license_permissions() {
		return current_user_can( 'manage_options' ) || current_user_can( 'activate_plugins' );
	}
}
