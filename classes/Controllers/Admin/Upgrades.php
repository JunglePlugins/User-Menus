<?php
/**
 * Upgrades Controller Class.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers\Admin;

use UserMenus\Base\Controller;

defined( 'ABSPATH' ) || exit;

use function __;
use function add_filter;
use function add_action;
use function esc_html_e;
use function esc_attr;
use function get_current_screen;
use function admin_url;
use function is_admin;
use function current_user_can;
use function get_option;
use function update_option;
use function wp_create_nonce;
use function wp_verify_nonce;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;
use function is_wp_error;

/**
 * Upgrades Controller.
 *
 * @package UserMenus\Admin
 */
class Upgrades extends Controller {

	/**
	 * Key to save list of upgrades that have been done.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'user_menus_upgrades';

	/**
	 * Initialize the settings page.
	 */
	public function init() {
		add_action( 'init', [ $this, 'hooks' ] );
		add_action( 'wp_ajax_user_menus_upgrades', [ $this, 'ajax_handler' ] );
		add_filter( 'user_menus/settings-page_localized_vars', [ $this, 'localize_vars' ] );
	}

	/**
	 * Hook into relevant WP actions.
	 */
	public function hooks() {
		if ( is_admin() && current_user_can( 'manage_options' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notices' ] );
			add_action( 'network_admin_notices', [ $this, 'admin_notices' ] );
			add_action( 'user_admin_notices', [ $this, 'admin_notices' ] );
		}
	}

	/**
	 * Get a list of all upgrades.
	 *
	 * @return string[]
	 */
	public function all_upgrades() {
		return [
			// Version 2 upgrades.
			'plugin_meta-2'  => '\UserMenus\Upgrades\PluginMeta_2',
			'restrictions-2' => '\UserMenus\Upgrades\Restrictions_2',
			'settings-2'     => '\UserMenus\Upgrades\Settings_2',
			'user_meta-2'    => '\UserMenus\Upgrades\UserMeta_2',
		];
	}

	/**
	 * Check if there are any upgrades to run.
	 *
	 * @return boolean
	 */
	public function has_upgrades() {
		return count( $this->get_required_upgrades() );
	}

	/**
	 * Get a list of required upgrades.
	 *
	 * Uses a cached list of done upgrades to prevent extra processing.
	 *
	 * @return \UserMenus\Base\Upgrade[]
	 */
	public function get_required_upgrades() {
		static $required_upgrades = null;

		if ( null === $required_upgrades ) {
			$required_upgrades = [];

			$all_upgrades  = $this->all_upgrades();
			$upgrades_done = get_option( self::OPTION_KEY, [] );
			$count_done    = count( $upgrades_done );

			foreach ( $all_upgrades as $key => $upgrade_class_name ) {
				if ( in_array( $key, $upgrades_done, true ) ) {
					continue;
				}

				if ( ! class_exists( $upgrade_class_name ) ) {
					continue;
				}

				/**
				 * Upgrade class instance.
				 *
				 * @var \UserMenus\Base\Upgrade $upgrade
				 */
				$upgrade = new $upgrade_class_name();

				if ( $upgrade->is_required() ) {
					$required_upgrades[ $key ] = $upgrade;
				} else {
					// If its not required, mark it as done.
					$upgrades_done[] = $key;
				}

				// Unset the upgrade class to prevent memory leaks.
				unset( $upgrade );
			}

			// Sort the required upgrades based on prerequisites.
			$required_upgrades = $this->sort_upgrades_by_prerequisites( $required_upgrades );

			// Store the list of upgrades that have been done if it has changed.
			if ( count( $upgrades_done ) > $count_done ) {
				update_option( self::OPTION_KEY, $upgrades_done );
			}
		}

		return $required_upgrades;
	}

	/**
	 * Sort upgrades based on prerequisites using a graph-based approach.
	 *
	 * @param \UserMenus\Base\Upgrade[] $upgrades List of upgrades to sort.
	 *
	 * @return \UserMenus\Base\Upgrade[]
	 */
	private function sort_upgrades_by_prerequisites( $upgrades ) {
		// Build the graph of upgrades and their dependencies.
		$graph = [];
		foreach ( $upgrades as $upgrade ) {
			$graph[ $upgrade::TYPE . '-' . $upgrade::VERSION ] = $upgrade->get_dependencies();
		}

		// Perform a topological sort on the graph.
		$sorted = $this->topological_sort( $graph );

		// Rebuild the list of upgrades in the sorted order.
		foreach ( $sorted as $key => $value ) {
			$sorted[ $key ] = $upgrades[ $value ];
		}

		// Return the sorted upgrades.
		return $sorted;
	}

	/**
	 * Perform a topological sort on a graph.
	 *
	 * @param array $graph Graph to sort.
	 *
	 * @return array
	 */
	private function topological_sort( $graph ) {
		$visited = [];
		$sorted  = [];

		foreach ( $graph as $node => $dependencies ) {
			$this->visit_node( $node, $graph, $visited, $sorted );
		}

		return $sorted;
	}

	/**
	 * Visit a node in the graph for topological sort.
	 *
	 * @param mixed $node Node to visit.
	 * @param array $graph Graph to sort.
	 * @param array $visited List of visited nodes.
	 * @param array $sorted List of sorted nodes.
	 */
	private function visit_node( $node, $graph, &$visited, &$sorted ) {
		if ( isset( $visited[ $node ] ) ) {
			// Node already visited, skip.
			return;
		}

		$visited[ $node ] = true;

		foreach ( $graph[ $node ] as $dependency ) {
			$this->visit_node( $dependency, $graph, $visited, $sorted );
		}

		$sorted[] = $node;
	}

	/**
	 * AJAX Handler
	 */
	public function ajax_handler() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'user_menus_upgrades' ) ) {
			wp_send_json_error();
		}

		if ( ! current_user_can( $this->container->get_permission( 'manage_settings' ) ) ) {
			wp_send_json_error();
		}

		try {
			$stream   = new \UserMenus\Services\UpgradeStream( 'upgrades' );
			$upgrades = $this->get_required_upgrades();
			$count    = count( $upgrades );

			// First do/while loop starts the stream and breaks if connection aborted.
			do {
				$stream->start();
				$stream->start_upgrades( $count, __( 'Upgrades started', 'user-menus' ) );

				$failed_upgrades = [];

				// This second while loop runs the upgrades.
				while ( ! empty( $upgrades ) ) {
					$upgrade = array_shift( $upgrades );

					$result = $upgrade->stream_run( $stream );

					if ( is_wp_error( $result ) ) {
						$stream->send_error( $result );
					} elseif ( false !== $result ) {
						$this->mark_upgrade_complete( $upgrade );
					} else {
						// False means the upgrade failed.
						$failed_upgrades[] = $upgrade::TYPE . '-' . $upgrade::VERSION;
					}
				}

				if ( ! empty( $failed_upgrades ) ) {
					$stream->send_error( [
						'message' => __( 'Some upgrades failed to complete.', 'user-menus' ),
						'data'    => $failed_upgrades,
					] );

					$stream->complete_upgrades( __( 'Upgrades complete with errors.', 'user-menus' ) );
				} else {
					$stream->complete_upgrades( __( 'Upgrades complete!', 'user-menus' ) );
				}
			} while ( ! $stream->should_abort() );
		} catch ( \Exception $e ) {
			$stream->send_error( $e );
		}
	}

	/**
	 * Mark an upgrade as complete.
	 *
	 * @param \UserMenus\Base\Upgrade $upgrade Upgrade to mark as complete.
	 *
	 * @return void
	 */
	public function mark_upgrade_complete( $upgrade ) {
		$upgrades_done   = get_option( self::OPTION_KEY, [] );
		$upgrades_done[] = $upgrade::TYPE . '-' . $upgrade::VERSION;
		update_option( self::OPTION_KEY, $upgrades_done );
	}

	/**
	 * AJAX Handler
	 */
	public function ajax_handler_demo() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'user_menus_upgrades' ) ) {
			wp_send_json_error();
		}

		if ( ! current_user_can( $this->container->get_permission( 'manage_settings' ) ) ) {
			wp_send_json_error();
		}

		try {
			$upgrades = $this->get_required_upgrades();
			$count    = count( $upgrades ) * 2;

			$stream = new \UserMenus\Services\UpgradeStream( 'upgrades' );

			$stream->start();

			$count = wp_rand( 3, 10 );

			do {
				$stream->start_upgrades( $count, __( 'Upgrades started', 'user-menus' ) );

				// test loop of 1000 upgrades.
				$test_delay = 60000;
				for ( $i = 0; $i < $count; $i++ ) {
					usleep( $test_delay );

					$task_count = wp_rand( 5, 100 );

					$stream->start_task(
						__( 'Migrating restrictions', 'user_menus' ),
						$task_count
					);

					// test loop of 1000 upgrades.
					for ( $i2 = 0; $i2 < $task_count; $i2++ ) {
						usleep( $test_delay );
						$stream->update_task_progress( $i2 + 1 );
					}

					usleep( $test_delay );

					// translators: %d: number of restrictions migrated.
					$stream->complete_task( sprintf( __( '%d restrictions migrated', 'user-menus' ), $i2 ) );
				}
				usleep( $test_delay );

				$stream->complete_upgrades( __( 'Upgrades complete!', 'user-menus' ) );
			} while ( ! $stream->should_abort() && ! empty( $upgrades ) );
		} catch ( \Exception $e ) {
			$stream->send_error( $e );
		}
	}

	/**
	 * Render admin notices if available.
	 */
	public function admin_notices() {
		if ( ! is_admin() ) {
			return;
		}

		if ( ! $this->has_upgrades() ) {
			return;
		}

		$screen = get_current_screen();

		if ( 'settings_page_user-menus-settings' === $screen->id ) {
			return;
		}

		?>
		<style>
			.user-menus-notice {
				display: flex;
				align-items: center;
				gap: 16px;
				padding: 8px;
				margin-top: 16px;
				margin-bottom: 16px;
			}

			.user-menus-notice .notice-logo {
				flex: 0 0 60px;
				max-width: 60px;
				font-size: 60px;
			}

			.user-menus-notice .notice-content {
				flex-grow: 1;
			}

			.user-menus-notice p {
				margin-bottom: 0;
				max-width: 800px;
			}

			.user-menus-notice .notice-actions {
				margin-top: 10px;
				margin-bottom: 0;
				padding-left: 0;
				list-style: none;

				display: flex;
				gap: 16px;
				align-items: center;
			}
		</style>

		<div class="notice notice-info user-menus-notice">
			<div class="notice-logo">
				<img class="logo" width="60" src="<?php echo esc_attr( $this->container->get_url( 'assets/images/illustration-check.svg' ) ); ?>" />
			</div>

			<div class="notice-content">
				<p>
					<strong>
						<?php esc_html_e( 'User Menus has been updated and needs to run some database upgrades.', 'user-menus' ); ?>
					</strong>
				</p>
				<ul class="notice-actions">
					<li>
						<a class="user-menus-go-to-settings button button-tertiary" href="<?php echo esc_attr( admin_url( 'options-general.php?page=user-menus-settings' ) ); ?>" data-reason="am_now">
							🚨   <?php esc_html_e( 'Upgrade Now', 'user-menus' ); ?>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Add localized vars to settings page if there are upgrades to run.
	 *
	 * @param array $vars Localized vars.
	 *
	 * @return array
	 */
	public function localize_vars( $vars ) {
		$vars['hasUpgrades'] = false;

		if ( ! $this->has_upgrades() ) {
			return $vars;
		}

		$vars['hasUpgrades']  = true;
		$vars['upgradeNonce'] = wp_create_nonce( 'user_menus_upgrades' );
		$vars['upgradeUrl']   = admin_url( 'admin-ajax.php?action=user_menus_upgrades' );
		$vars['upgrades']     = [];

		$upgrades = $this->get_required_upgrades();

		foreach ( $this->get_required_upgrades() as $key => $upgrade ) {
			$vars['upgrades'][ $key ] = [
				'key'         => $upgrade::TYPE . '-' . $upgrade::VERSION,
				'label'       => $upgrade->label(),
				'description' => $upgrade->description(),
			];
		}

		return $vars;
	}

}
