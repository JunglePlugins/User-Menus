<?php
/**
 * Plugin controller.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 *
 * @package UserMenus
 */

namespace UserMenus\Services;

defined( 'ABSPATH' ) || exit;

/**
 * HTTP Stream class.
 */
class UpgradeStream extends \UserMenus\Base\Stream {

	/**
	 * Upgrade status.
	 *
	 * @var array
	 */
	public $status = [
		'total'       => 0,
		'progress'    => 0,
		'currentTask' => null,
	];

	/**
	 * Update the status of the upgrade.
	 *
	 * @param array $status Status to update.
	 */
	public function update_status( $status = [] ) {
		$defaults = [
			'total'       => 0,
			'progress'    => 0,
			'currentTask' => null,
		];

		// Merge with existing status.
		$status = wp_parse_args(
			$status,
			$this->status ? (array) $this->status : []
		);

		// Update status (merge with defaults).
		$this->status = array_merge( $defaults, $status );
	}

	/**
	 * Update the status of the current task.
	 *
	 * @param array|null $task_status Status to update.
	 */
	public function update_task_status( $task_status = [] ) {
		$defaults = [
			'name'     => '',
			'total'    => 0,
			'progress' => 0,
		];

		if ( null === $task_status ) {
			// Reset current task.
			$this->status['currentTask'] = null;
		} else {
			// Merge with existing status.
			$task_status = wp_parse_args(
				$task_status,
				$this->status['currentTask'] ? (array) $this->status['currentTask'] : []
			);

			// Update status (merge with defaults).
			$this->status['currentTask'] = array_merge( $defaults, $task_status );
		}
	}

	/**
	 * Send an event to the client.
	 *
	 * @param string $event Event name.
	 * @param array  $data Data to send.
	 *
	 * @return void
	 */
	public function send_event( $event, $data = [] ) {
		// Always send the status.
		$data['status'] = $this->status;

		$data = is_string( $data ) ? $data : \wp_json_encode( $data );

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "event: {$event}" . PHP_EOL;
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "data: {$data}" . PHP_EOL;
		echo PHP_EOL;

		$this->flush_buffers();
	}

	/**
	 * Start the upgrade process.
	 *
	 * @param int    $total Number of upgrades.
	 * @param string $message Message to send.
	 */
	public function start_upgrades( $total, $message = null ) {
		$this->update_status( [
			'total'       => $total,
			'progress'    => 0,
			'currentTask' => null,
		] );

		$this->send_event( 'upgrades:start', [
			'message' => $message ? $message : __( 'Starting upgrades...', 'user-menus' ),
		] );
	}

	/**
	 * Complete the upgrade process.
	 *
	 * @param string $message Message to send.
	 */
	public function complete_upgrades( $message = null ) {
		$this->update_status( [
			'progress'    => $this->status['total'],
			'currentTask' => null,
		] );

		$this->send_event( 'upgrades:complete', [
			'message' => $message ? $message : __( 'Upgrades complete.', 'user-menus' ),
		] );
	}

	/**
	 * Start a task.
	 *
	 * @param string $name Task name.
	 * @param int    $task_steps Number of steps in the task.
	 * @param string $message Message to send.
	 */
	public function start_task( $name, $task_steps = 1, $message = null ) {
		$this->update_task_status( [
			'name'     => $name,
			'progress' => 0,
			'total'    => $task_steps,
		] );

		$this->send_event( 'task:start', [
			'message' => $message ? $message : $name,
		] );
	}

	/**
	 * Update the progress of the current task.
	 *
	 * @param int $progress Progress of the task.
	 */
	public function update_task_progress( $progress ) {
		$this->update_task_status( [
			'progress' => $progress,
		] );

		$this->send_event( 'task:progress', [] );
	}

	/**
	 * Complete the current task.
	 *
	 * @param string $message Message to send.
	 */
	public function complete_task( $message = null ) {
		$task_status = $this->status['currentTask'];

		$this->update_task_status( null );

		$this->update_status( [
			'progress' => $this->status['progress'] + 1,
		] );

		$this->send_event( 'task:complete', [
			// translators: %s: task name.
			'message' => $message ? $message : sprintf( __( 'Completed: %s', 'user-menus' ), $task_status['name'] ),
		] );
	}

}
