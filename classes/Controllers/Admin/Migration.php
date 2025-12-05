<?php
/**
 * Migration Controller.
 *
 * Handles the migration UI and process for upgrading from User Menus v1.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers\Admin;

use UserMenus\Base\Controller;
use UserMenus\Services\Migration as MigrationService;

defined( 'ABSPATH' ) || exit;

/**
 * Class Migration
 */
class Migration extends Controller {

	/**
	 * Migration service.
	 *
	 * @var MigrationService
	 */
	protected $migration;

	/**
	 * Check if controller is enabled.
	 *
	 * @return bool
	 */
	public function controller_enabled() {
		return is_admin();
	}

	/**
	 * Initialize the controller.
	 *
	 * @return void
	 */
	public function init() {
		$this->migration = new MigrationService();

		// Show migration notice if needed.
		add_action( 'admin_notices', [ $this, 'maybe_show_migration_notice' ] );

		// Handle AJAX migration.
		add_action( 'wp_ajax_user_menus_run_migration', [ $this, 'handle_migration' ] );
		add_action( 'wp_ajax_user_menus_dismiss_migration', [ $this, 'handle_dismiss' ] );

		// Auto-migrate on first load if data exists.
		add_action( 'admin_init', [ $this, 'maybe_auto_migrate' ] );
	}

	/**
	 * Maybe auto-migrate on first load.
	 *
	 * @return void
	 */
	public function maybe_auto_migrate() {
		// Only run once.
		if ( get_option( 'user_menus_migration_checked', false ) ) {
			return;
		}

		// Mark as checked.
		update_option( 'user_menus_migration_checked', true );

		// Check if migration is needed and run it.
		if ( $this->migration->needs_migration() && ! $this->migration->has_migrated() ) {
			$this->migration->run();
		}
	}

	/**
	 * Maybe show migration notice.
	 *
	 * @return void
	 */
	public function maybe_show_migration_notice() {
		// Only show on relevant pages.
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->base, [ 'nav-menus', 'plugins', 'options-general' ], true ) ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if dismissed.
		if ( get_option( 'user_menus_migration_dismissed', false ) ) {
			return;
		}

		// Check if migration completed.
		$status = $this->migration->get_migration_status();
		if ( $status ) {
			// Show success notice.
			$this->render_success_notice( $status );
			return;
		}

		// Check if migration is needed.
		if ( ! $this->migration->needs_migration() ) {
			return;
		}

		$this->render_migration_notice();
	}

	/**
	 * Render migration notice.
	 *
	 * @return void
	 */
	protected function render_migration_notice() {
		$nonce = wp_create_nonce( 'user_menus_migration_nonce' );
		?>
		<div class="notice notice-warning user-menus-migration-notice" id="user-menus-migration-notice">
			<p>
				<strong><?php esc_html_e( 'User Menus: Migration Available', 'user-menus' ); ?></strong>
			</p>
			<p>
				<?php esc_html_e( 'We detected data from User Menus v1. Would you like to migrate your menu item settings to User Menus?', 'user-menus' ); ?>
			</p>
			<p>
				<button type="button"
						class="button button-primary user-menus-run-migration"
						data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php esc_html_e( 'Migrate Now', 'user-menus' ); ?>
				</button>
				<button type="button"
						class="button user-menus-dismiss-migration"
						data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php esc_html_e( 'Dismiss', 'user-menus' ); ?>
				</button>
				<span class="spinner" style="float: none; margin-top: 0;"></span>
			</p>
		</div>
		<script>
			jQuery( function( $ ) {
				$( '.user-menus-run-migration' ).on( 'click', function() {
					var $btn = $( this );
					var $notice = $( '#user-menus-migration-notice' );
					var $spinner = $notice.find( '.spinner' );

					$btn.prop( 'disabled', true );
					$spinner.addClass( 'is-active' );

					$.post( ajaxurl, {
						action: 'user_menus_run_migration',
						nonce: $btn.data( 'nonce' )
					}, function( response ) {
						$spinner.removeClass( 'is-active' );

						if ( response.success ) {
							$notice.removeClass( 'notice-warning' ).addClass( 'notice-success' );
							$notice.find( 'p' ).last().html(
								'<strong><?php echo esc_js( __( 'Migration complete!', 'user-menus' ) ); ?></strong> ' +
								response.data.migrated + ' <?php echo esc_js( __( 'menu items migrated.', 'user-menus' ) ); ?>'
							);
						} else {
							$notice.removeClass( 'notice-warning' ).addClass( 'notice-error' );
							$notice.find( 'p' ).last().html(
								'<strong><?php echo esc_js( __( 'Migration failed.', 'user-menus' ) ); ?></strong> ' +
								response.data.message
							);
						}
					} );
				} );

				$( '.user-menus-dismiss-migration' ).on( 'click', function() {
					var $btn = $( this );

					$.post( ajaxurl, {
						action: 'user_menus_dismiss_migration',
						nonce: $btn.data( 'nonce' )
					}, function() {
						$( '#user-menus-migration-notice' ).fadeOut();
					} );
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Render success notice.
	 *
	 * @param array $status Migration status.
	 * @return void
	 */
	protected function render_success_notice( $status ) {
		// Only show once after migration.
		if ( get_option( 'user_menus_migration_success_shown', false ) ) {
			return;
		}

		update_option( 'user_menus_migration_success_shown', true );
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<strong><?php esc_html_e( 'User Menus: Migration Complete', 'user-menus' ); ?></strong>
			</p>
			<p>
				<?php
				printf(
					/* translators: %d: Number of menu items migrated */
					esc_html__( 'Successfully migrated %d menu items from User Menus v1.', 'user-menus' ),
					absint( $status['migrated'] )
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Handle migration AJAX request.
	 *
	 * @return void
	 */
	public function handle_migration() {
		check_ajax_referer( 'user_menus_migration_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'user-menus' ) ] );
		}

		$result = $this->migration->run();

		if ( ! empty( $result['errors'] ) ) {
			wp_send_json_error( [
				'message'  => __( 'Some items failed to migrate.', 'user-menus' ),
				'migrated' => $result['migrated'],
				'errors'   => $result['errors'],
			] );
		}

		wp_send_json_success( [
			'migrated' => $result['migrated'],
		] );
	}

	/**
	 * Handle dismiss AJAX request.
	 *
	 * @return void
	 */
	public function handle_dismiss() {
		check_ajax_referer( 'user_menus_migration_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		update_option( 'user_menus_migration_dismissed', true );

		wp_send_json_success();
	}
}
