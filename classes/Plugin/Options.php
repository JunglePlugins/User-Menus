<?php
/**
 * Plugin Options.
 *
 * @package UserMenus
 */

namespace UserMenus\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Options
 */
class Options {

	/**
	 * Option prefix.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Cached options.
	 *
	 * @var array<string,mixed>
	 */
	protected $options = [];

	/**
	 * Constructor.
	 *
	 * @param string $prefix Option prefix.
	 */
	public function __construct( $prefix ) {
		$this->prefix = $prefix;
	}

	/**
	 * Get option.
	 *
	 * @param string $key           Option key.
	 * @param mixed  $default_value Default value.
	 * @return mixed
	 */
	public function get( $key, $default_value = false ) {
		if ( ! isset( $this->options[ $key ] ) ) {
			$this->options[ $key ] = get_option( $this->prefix . '_' . $key, $default_value );
		}

		return $this->options[ $key ];
	}

	/**
	 * Set option.
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value Option value.
	 * @return bool
	 */
	public function set( $key, $value ) {
		$this->options[ $key ] = $value;
		return update_option( $this->prefix . '_' . $key, $value );
	}

	/**
	 * Delete option.
	 *
	 * @param string $key Option key.
	 * @return bool
	 */
	public function delete( $key ) {
		unset( $this->options[ $key ] );
		return delete_option( $this->prefix . '_' . $key );
	}

	/**
	 * Get all options.
	 *
	 * @return array<string,mixed>
	 */
	public function get_all() {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
				$this->prefix . '_%'
			)
		);

		$options = [];
		foreach ( $results as $result ) {
			$key = str_replace( $this->prefix . '_', '', $result->option_name );
			$options[ $key ] = maybe_unserialize( $result->option_value );
		}

		return $options;
	}
}
