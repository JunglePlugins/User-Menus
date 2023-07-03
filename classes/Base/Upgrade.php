<?php
/**
 * Plugin controller.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 *
 * @package UserMenu
 */

namespace UserMenu\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Base Upgrade class.
 */
abstract class Upgrade implements \UserMenu\Interfaces\Upgrade {

	/**
	 * Type.
	 *
	 * @var string Uses data versioning types.
	 */
	const TYPE = '';

	/**
	 * Version.
	 *
	 * @var int
	 */
	const VERSION = 1;

	/**
	 * Stream.
	 *
	 * @var \UserMenu\Services\UpgradeStream
	 */
	public $stream;

	/**
	 * Upgrade constructor.
	 */
	public function __construct() {
	}

	/**
	 * Upgrade label
	 *
	 * @return string
	 */
	abstract public function label();

	/**
	 * Return full description for this upgrade.
	 *
	 * @return string
	 */
	public function description() {
		return '';
	}

	/**
	 * Check if the upgrade is required.
	 *
	 * @return bool
	 */
	public function is_required() {
		$current_version = \UserMenu\get_data_version( static::TYPE );
		return $current_version && $current_version < static::VERSION;
	}

	/**
	 * Check if the prerequisites are met.
	 *
	 * @return bool
	 */
	public function prerequisites_met() {
		return true;
	}

	/**
	 * Get the dependencies for this upgrade.
	 *
	 * @return string[]
	 */
	public function get_dependencies() {
		return [];
	}

	/**
	 * Run the upgrade.
	 *
	 * @return void|WP_Error|false
	 */
	abstract public function run();

	/**
	 * Run the upgrade.
	 *
	 * @param \UserMenu\Services\UpgradeStream $stream Stream.
	 *
	 * @return void|WP_Error|false
	 */
	public function stream_run( $stream ) {
		$this->stream = $stream;

		$return = $this->run();

		unset( $this->stream );

		return $return;
	}

	/**
	 * Return the stream.
	 *
	 * @return \UserMenu\Services\UpgradeStream|Object $stream Stream.
	 */
	public function stream() {
		$noop = function() {};

		return isset( $this->stream ) ? $this->stream : (object) [
			'send_event'           => $noop,
			'send_error'           => $noop,
			'send_data'            => $noop,
			'update_status'        => $noop,
			'update_task_status'   => $noop,
			'start_upgrades'       => $noop,
			'complete_upgrades'    => $noop,
			'start_task'           => $noop,
			'update_task_progress' => $noop,
			'complete_task'        => $noop,
		];
	}

}
