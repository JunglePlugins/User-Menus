<?php
/**
 * Reviews Controller.
 *
 * Prompts users to leave a review after they've used the plugin for a while.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers\Admin;

use UserMenus\Base\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class Reviews
 */
class Reviews extends Controller {

	/**
	 * Option name for tracking review status.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'user_menus_review_status';

	/**
	 * Days to wait before showing review prompt.
	 *
	 * @var int
	 */
	const DAYS_BEFORE_PROMPT = 14;

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
		add_action( 'admin_notices', [ $this, 'maybe_show_review_notice' ] );
		add_action( 'wp_ajax_user_menus_dismiss_review', [ $this, 'handle_dismiss' ] );
	}

	/**
	 * Maybe show the review notice.
	 *
	 * @return void
	 */
	public function maybe_show_review_notice() {
		// Only show on nav-menus.php page.
		global $pagenow;
		if ( 'nav-menus.php' !== $pagenow ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		$status = get_option( self::OPTION_NAME, [] );

		// If dismissed permanently, don't show.
		if ( isset( $status['dismissed'] ) && $status['dismissed'] ) {
			return;
		}

		// If already reviewed, don't show.
		if ( isset( $status['reviewed'] ) && $status['reviewed'] ) {
			return;
		}

		// If snoozed, check if snooze period has passed.
		if ( isset( $status['snoozed_until'] ) && time() < $status['snoozed_until'] ) {
			return;
		}

		// Check if enough time has passed since installation.
		$installed_on = get_option( 'user_menus_installed_on' );
		if ( ! $installed_on ) {
			update_option( 'user_menus_installed_on', current_time( 'mysql' ) );
			return;
		}

		$install_time = strtotime( $installed_on );
		$days_since   = ( time() - $install_time ) / DAY_IN_SECONDS;

		if ( $days_since < self::DAYS_BEFORE_PROMPT ) {
			return;
		}

		$this->render_notice();
	}

	/**
	 * Render the review notice.
	 *
	 * @return void
	 */
	protected function render_notice() {
		$nonce = wp_create_nonce( 'user_menus_review_nonce' );
		?>
		<div class="notice notice-info is-dismissible user-menus-review-notice" id="user-menus-review-notice">
			<p>
				<strong><?php esc_html_e( 'Enjoying User Menus?', 'user-menus' ); ?></strong>
			</p>
			<p>
				<?php esc_html_e( "You've been using User Menus for a while now. If you find it useful, would you consider leaving us a review?", 'user-menus' ); ?>
			</p>
			<p>
				<a href="https://wordpress.org/support/plugin/user-menus/reviews/#new-post"
				   target="_blank"
				   class="button button-primary user-menus-review-action"
				   data-action="reviewed"
				   data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php esc_html_e( 'Leave a Review', 'user-menus' ); ?>
				</a>
				<button type="button"
						class="button user-menus-review-action"
						data-action="snooze"
						data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php esc_html_e( 'Maybe Later', 'user-menus' ); ?>
				</button>
				<button type="button"
						class="button user-menus-review-action"
						data-action="dismiss"
						data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php esc_html_e( 'No Thanks', 'user-menus' ); ?>
				</button>
			</p>
		</div>
		<script>
			jQuery( function( $ ) {
				$( '.user-menus-review-action' ).on( 'click', function() {
					var $btn = $( this );
					var action = $btn.data( 'action' );
					var nonce = $btn.data( 'nonce' );

					$.post( ajaxurl, {
						action: 'user_menus_dismiss_review',
						review_action: action,
						nonce: nonce
					}, function() {
						$( '#user-menus-review-notice' ).fadeOut();
					} );
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Handle the dismiss action.
	 *
	 * @return void
	 */
	public function handle_dismiss() {
		check_ajax_referer( 'user_menus_review_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error();
		}

		$action = isset( $_POST['review_action'] ) ? sanitize_text_field( wp_unslash( $_POST['review_action'] ) ) : '';
		$status = get_option( self::OPTION_NAME, [] );

		switch ( $action ) {
			case 'reviewed':
				$status['reviewed'] = true;
				break;

			case 'snooze':
				$status['snoozed_until'] = time() + ( 30 * DAY_IN_SECONDS );
				break;

			case 'dismiss':
				$status['dismissed'] = true;
				break;
		}

		update_option( self::OPTION_NAME, $status );

		wp_send_json_success();
	}
}
