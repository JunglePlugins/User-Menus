<?php
/**
 * Connect.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 *
 * @package UserMenus
 */

namespace UserMenus\Plugin;

use function UserMenus\plugin;
use function UserMenus\Base\Container;
use function request_filesystem_credentials;

defined( 'ABSPATH' ) || exit;

/**
 * Connection management.
 *
 * @package UserMenus
 */
class Connect {

    // TODO: Correct api url, token, and nonce for User Menus.

	const API_URL    = 'https://upgrade.usermenusplugin.com/';
	const DEBUG_MODE = false;

	const TOKEN_OPTION_NAME = 'user_menus_connect_token';
	const NONCE_OPTION_NAME = 'user_menus_connect_nonce';

	const ERROR_REFERRER       = 1;
	const ERROR_AUTHENTICATION = 2;
	const ERROR_USER_AGENT     = 3;
	const ERROR_SIGNATURE      = 4;
	const ERROR_NONCE          = 5;
	const ERROR_WEBHOOK_ARGS   = 6;

	/**
	 * Container.
	 *
	 * @var \UserMenus\Base\Container
	 */
	private $c;

	/**
	 * Initialize license management.
	 *
	 * @param \UserMenus\Base\Container $c Container.
	 */
	public function __construct( $c ) {
		$this->c = $c;

		$this->register_hooks();
	}

	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_nopriv_user_menus_connect_verify_connection', [ $this, 'process_verify_connection' ] );
		add_action( 'wp_ajax_nopriv_user_menus_connect_webhook', [ $this, 'process_webhook' ] );
	}

	/**
	 * Generate a new authorizatin token.
	 *
	 * @return string
	 */
	public function generate_token() {
		$token = hash( 'sha512', wp_rand() );

		update_option( self::TOKEN_OPTION_NAME, $token );

		return $token;
	}

	/**
	 * Get the current token.
	 *
	 * @return string|false
	 */
	public function get_access_token() {
		return get_option( self::TOKEN_OPTION_NAME, false );
	}

	/**
	 * Get the current nonce.
	 *
	 * @param string $token Token.
	 *
	 * @return string|false
	 */
	public function get_nonce_name( $token ) {
		return self::NONCE_OPTION_NAME . '_' . $token;
	}

	/**
	 * Log a message to the debug log if enabled.
	 *
	 * Here to prevent constant conditional checks for the debug mode.
	 *
	 * @param string $message Message.
	 * @param string $type    Type.
	 */
	public function debug_log( $message, $type = 'INFO' ) {
		if ( self::DEBUG_MODE ) {
			plugin( 'logging' )->log( "Plugin\Connect.$type: $message" );
		}
	}

	/**
	 * Get header Authorization
	 *
	 * @return string
	 */
	public function get_request_authorization_header() {
		$headers = null;

		if ( isset( $_SERVER['Authorization'] ) ) {
			$headers = trim( sanitize_text_field( wp_unslash( $_SERVER['Authorization'] ) ) );
		} elseif ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) { // Nginx or fast CGI.
			$headers = trim( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) ) );
		} elseif ( function_exists( 'apache_request_headers' ) ) {
			$request_headers = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization).
			$request_headers = array_combine( array_map( 'ucwords', array_keys( $request_headers ) ), array_values( $request_headers ) );
			if ( isset( $request_headers['Authorization'] ) ) {
				$headers = trim( $request_headers['Authorization'] );
			}
		}
		return $headers;
	}

	/**
	 * Get access token from header.
	 *
	 * @return string|null
	 */
	public function get_request_token() {
		$headers = $this->get_request_authorization_header();
		// HEADER: Get the access token from the header.
		if ( ! empty( $headers ) ) {
			if ( preg_match( '/Bearer\s(\S+)/', $headers, $matches ) ) {
				return trim( $matches[1], ', ' );
			}
		}
		return null;
	}

	/**
	 * Get nonce from header.
	 *
	 * @return string|null
	 */
	public function get_request_nonce() {
		$headers = $this->get_request_authorization_header();
		// HEADER: Get the nonce from the header.
		if ( ! empty( $headers ) ) {
			if ( preg_match( '/Nonce\s(\S+)/', $headers, $matches ) ) {
				return trim( $matches[1], ', ' );
			}
		}
		return null;
	}

	/**
	 * Get the OAuth connect URL.
	 *
	 * @param string $license_key License key.
	 *
	 * @return string
	 */
	public function get_connect_info( $license_key ) {
		$token    = $this->generate_token();
		$nonce    = wp_create_nonce( $this->get_nonce_name( $token ) );
		$webhook  = admin_url( 'admin-ajax.php' );
		$redirect = add_query_arg(
			[
				'page' => 'user-menus-settings',
				'view' => 'upgrade',
				'tab'  => 'general',
			],
			admin_url( 'options-general.php' )
		);

		$url = add_query_arg(
			[
				'key'      => $license_key,
				'token'    => $token,
				'nonce'    => $nonce,
				'webhook'  => $webhook,
				'version'  => plugin( 'version' ),
				'siteurl'  => admin_url(),
				'homeurl'  => home_url(),
				'redirect' => rawurldecode(base64_encode($redirect)), // phpcs:ignore
			],
			self::API_URL
		);

		$this->debug_log( 'Generated new connection.' );
		$this->debug_log( 'Token: ' . $token );
		$this->debug_log( 'Nonce: ' . $nonce );

		return [
			'url'      => $url,
			'back_url' => add_query_arg(
				[
					'action' => 'user_menus_connect',
					'token'  => $token,
				],
				$webhook
			),
		];
	}

	/**
	 * Kill the connection with no permission.
	 *
	 * @param int    $error_no Error number.
	 * @param string $message Error message.
	 */
	public function kill_connection( $error_no = self::ERROR_REFERRER, $message = false ) {
		$this->debug_log( "Killing connection with error ($error_no) message: " . $message, 'ERROR' );

		wp_die( esc_html( self::DEBUG_MODE && $message ? $message : __( 'Sorry, You Are Not Allowed to Access This Page.', 'user-menus' ) ), esc_attr( $error_no ), [ 'response' => 403 ] );
	}

	/**
	 * Verify the user agent.
	 *
	 * @return void
	 */
	public function verify_user_agent() {
		// Check user agent matches User Menus Upgrader.
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) || 'UserMenusUpgrader' !== $_SERVER['HTTP_USER_AGENT'] ) {
			$this->kill_connection( self::ERROR_USER_AGENT, 'User agent invalid.' );
		}
	}

	/**
	 * Verify the referrer.
	 *
	 * @return void
	 */
	public function verify_referrer() {
		$referer = isset( $_SERVER['HTTP_X_SENDING_DOMAIN'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_SENDING_DOMAIN'] ) ) : '';

		if ( ! $referer ) {
			// Mimic the default you don't have permission to do that screen from WordPress.
			$this->kill_connection( self::ERROR_REFERRER, 'Missing referrer' );
		}

		$referer_host = wp_parse_url( $referer, PHP_URL_HOST );

		if ( ! $referer_host ) {
			$this->kill_connection( self::ERROR_REFERRER, 'Invalid referrer' );
		}

		$allowed_hosts = [
			'usermenusplugin.com',
			'upgrade.usermenusplugin.com',
		];

		if ( ! in_array( $referer_host, $allowed_hosts, true ) ) {
			$this->debug_log( 'Referrer mismatch: ' . $referer_host, 'DEBUG' );
			$this->kill_connection( self::ERROR_REFERRER, 'Referrer doesn\'t match' );
		}
	}

	/**
	 * Verify the nonce.
	 *
	 * @deprecated 2.0.0 Don't use, it doesn't work as its a separate server making request.
	 */
	public function verify_nonce() {
		$token = $this->get_access_token();
		$nonce = $this->get_request_nonce();

		if ( ! $nonce ) {
			$this->kill_connection( self::ERROR_NONCE, 'Missing nonce' );
		}

		if ( false === wp_verify_nonce( $nonce, $this->get_nonce_name( $token ) ) ) {
			$this->debug_log( 'Nonce mismatch: ' . $nonce, 'DEBUG' );
			$this->debug_log( 'Nonce Name: ' . $this->get_nonce_name( $token ) );
			$this->kill_connection( self::ERROR_NONCE, 'Invalid nonce' );
		}
	}

	/**
	 * Verify the authentication token.
	 *
	 * @return void
	 */
	public function verify_authentication() {
		// Get token from header Bearer.
		$token      = $this->get_access_token();
		$auth_token = $this->get_request_token();

		if ( ! $token || ! $auth_token ) {
			$this->kill_connection( self::ERROR_AUTHENTICATION, 'Missing authentication' );
		}

		// Verify hashes match.
		if ( ! hash_equals( $token, $auth_token ) ) {
			$this->debug_log( 'Token mismatch: ' . $auth_token, 'DEBUG' );
			$this->kill_connection( self::ERROR_AUTHENTICATION, 'Invalid authentiction' );
		}
	}

	/**
	 * Generate signature hash.
	 *
	 * @param array|string $data Data to hash.
	 * @param string       $token Token to hash with.
	 * @return string
	 */
	public function generate_hash( $data, $token ) {
		// Convert boolean values to their string representation.
		array_walk_recursive($data, function ( &$value ) {
			if ( is_bool( $value ) ) {
				$value = $value ? '1' : '0';
			}
		});

		if ( ! is_string( $data ) ) {
			// Sort the array before encoding it as JSON.
			ksort( $data );

			// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			$data = json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		}

		// Generate the hash binary.
		$hash = hash_hmac( 'sha256', $data, $token, true );

		$this->debug_log( 'Hash: ' . $hash, 'DEBUG' );
		$this->debug_log( 'Data: ' . $data, 'DEBUG' );

		// Encode the hash in base64 to make it URL safe.
		return base64_encode( $hash ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Verify the signature of the requester.
	 *
	 * @return void
	 */
	public function verify_signature() {
		if ( ! isset( $_SERVER['HTTP_X_USERMENUS_SIGNATURE'] ) ) {
			return;
		}

		// Verify the webhook signature.
		$signature = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_USERMENUS_SIGNATURE'] ) );

		// Calculate the expected signature.
		$expected_signature = $this->generate_hash( $_POST, $this->get_access_token() ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Compare the expected signature to the received signature.
		if ( ! hash_equals( $expected_signature, $signature ) ) {
			$this->debug_log( "Signature mismatch: \r\n - Webhook: " . $signature . "\r\n - Calculated: " . $expected_signature, 'DEBUG' );
			$this->kill_connection( self::ERROR_SIGNATURE, 'Invalid signature' );
		}
	}

	/**
	 * Validate the connection.
	 *
	 * @return void
	 */
	public function validate_connection() {
		$this->debug_log( 'Validating connection...', 'DEBUG' );
		$this->verify_user_agent();
		// If production, verify the referrer.
		if ( 'production' === wp_get_environment_type() ) {
			$this->verify_referrer();
		}
		$this->verify_authentication();
		$this->verify_signature();
		$this->debug_log( 'Connection validated', 'DEBUG' );
	}

	/**
	 * Verify the connection.
	 *
	 * @return void
	 */
	public function process_verify_connection() {
		$this->validate_connection();
		wp_send_json_success();
	}

	/**
	 * Get the webhook args.
	 *
	 * @return array
	 */
	public function get_webhook_args() {
		$args = [
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			'file'  => ! empty( $_REQUEST['file'] ) ? esc_url_raw( wp_unslash( $_REQUEST['file'] ) ) : '',
			'type'  => ! empty( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : 'plugin',
			'slug'  => ! empty( $_REQUEST['slug'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['slug'] ) ) : '',
			'force' => ! empty( $_REQUEST['force'] ) ? (bool) $_REQUEST['force'] : false,
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		];

		$this->verify_webhook_args( $args );

		return $args;
	}

	/**
	 * Verify and return webhook args.
	 *
	 * @param array $args The webhook args.
	 *
	 * @return void
	 */
	public function verify_webhook_args( $args ) {
		$file_url  = ! empty( $args['file'] ) ? $args['file'] : false;
		$file_type = ! empty( $args['type'] ) ? $args['type'] : false;
		$file_slug = ! empty( $args['slug'] ) ? $args['slug'] : false;
		$force     = ! empty( $args['force'] ) ? (bool) $args['force'] : false;

		if ( ! $file_url || ! $file_type || ! $file_slug ) {
			$this->kill_connection( self::ERROR_WEBHOOK_ARGS, 'Missing webhook args' );
		}

		if ( ! in_array( $file_type, [ 'plugin', 'theme' ], true ) ) {
			$this->kill_connection( self::ERROR_WEBHOOK_ARGS, 'Invalid webhook args' );
		}
	}

	/**
	 * Listen for incoming secure webhooks from the API server.
	 *
	 * @return void
	 */
	public function process_webhook() {
		// 1. Validate the connection is secure & from the API server.
		$this->validate_connection();

		$error = esc_html__( 'There was an error while installing an upgrade. Please download the plugin from usermenusplugin.com and install it manually.', 'user-menus' );

		// 2. Get the webhook data.
		$args = $this->get_webhook_args();

		// 3. Delete the token to prevent abuse.
		if ( ! self::DEBUG_MODE ) {
			$this->debug_log( 'Deleting token', 'DEBUG' );
			delete_option( self::TOKEN_OPTION_NAME );
		}

		// 4. Validate license key.
		if ( ! plugin( 'license' )->is_license_active() ) {
			$this->debug_log( 'License not active', 'DEBUG' );
			wp_send_json_error( $error );
		}

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'settings_page_user-menus-settings' );

		switch ( $args['type'] ) {
			case 'plugin':
				$this->install_plugin( $args );
				break;
		}
	}

	/**
	 * Install a plugin.
	 *
	 * @param array $args The file args.
	 * @return void
	 */
	public function install_plugin( $args ) {
		$this->debug_log( 'Installing plugin...', 'DEBUG' );

		// If not forcing, and already active, return success.
		if ( ! $args['force'] && is_plugin_active( "{$args['slug']}/{$args['slug']}.php" ) ) {
			$this->debug_log( 'Plugin already installed & active.', 'DEBUG' );
			wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'user-menus' ) );
		}

		// Get the upgrader.
		$upgrader = $this->c->get( 'upgrader' );

		// Install the plugin. (if installed already, this will replace it using upgrade).
		$installed = $upgrader->install_plugin( $args['file'] );

		if ( ! is_wp_error( $installed ) ) {
			$this->debug_log( 'Plugin installed & activated successfully.', 'DEBUG' );
			wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'user-menus' ) );
		}

		switch ( $installed->get_error_code() ) {
			default:
				$error = $installed->get_error_message();
		}
    // TODO: Update url.
		if ( empty( $error ) ) {
			$error = esc_html__( 'There was an error while installing an upgrade. Please download the plugin from usermenusplugin.com and install it manually.', 'user-menus' );
		}

		$this->debug_log( 'Plugin install failed: ' . $error, 'DEBUG' );
		wp_send_json_error( $installed->get_error_message() );
	}
}
