<?php
/**
 * Admin Widget Editor controller.
 *
 * @note This is only used for the old WP -4.9 widget editor.
 *
 * @package UserMenus\Admin
 * @copyright (c) 2023 Code Atlantic LLC
 */

namespace UserMenus\Controllers\Admin;

use UserMenus\Base\Controller;

use function \UserMenus\Rules\allowed_user_roles;
use function UserMenus\Widgets\parse_options as parse_widget_options;

/**
 * WidgetEditor controller class.
 */
class WidgetEditor extends Controller {

	/**
	 * Initialize widget editor UX.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'in_widget_form', [ $this, 'fields' ], 5, 3 );
		add_filter( 'widget_update_callback', [ $this, 'save' ], 5, 3 );
	}

	/**
	 * Enqueue v1 admin scripts.
	 *
	 * @param mixed $hook Admin page hook name.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'widgets.php' === $hook ) {
			wp_enqueue_style( 'user-menus-widget-editor' );
			wp_enqueue_script( 'user-menus-widget-editor' );
		}
	}

	/**
	 * Renders additional widget option fields.
	 *
	 * @param \WP_Widget $widget Widget instance.
	 * @param bool       $ret Whether to return the output.
	 * @param array      $instance Widget instance options.
	 */
	public function fields( $widget, $ret, $instance ) {
		$allowed_user_roles = allowed_user_roles();

		wp_nonce_field( 'user-menus-widget-editor-nonce', 'user-menus-widget-editor-nonce' );

		$which_users_options = [
			''           => __( 'Everyone', 'user-menus' ),
			'logged_out' => __( 'Logged Out Users', 'user-menus' ),
			'logged_in'  => __( 'Logged In Users', 'user-menus' ),
		];

		$instance = parse_widget_options( $instance );

		?>
		<p class="widget_options-which_users">
			<label for="<?php echo esc_attr( $widget->get_field_id( 'which_users' ) ); ?>">
				<?php esc_html_e( 'Who can see this widget?', 'user-menus' ); ?><br />
				<select name="<?php echo esc_attr( $widget->get_field_name( 'which_users' ) ); ?>" id="<?php echo esc_attr( $widget->get_field_id( 'which_users' ) ); ?>" class="widefat">
					<?php foreach ( $which_users_options as $option => $label ) : ?>
						<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $option, $instance['which_users'] ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>

		<p class="widget_options-roles">
			<?php esc_html_e( 'Choose which roles can see this widget', 'user-menus' ); ?><br />
			<?php foreach ( $allowed_user_roles as $option => $label ) : ?>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $widget->get_field_name( 'roles' ) ); ?>[]" value="<?php echo esc_attr( $option ); ?>" <?php checked( in_array( $option, $instance['roles'], true ), true ); ?>/>
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</p>
		<?php
	}

	/**
	 * Validates & saves additional widget options.
	 *
	 * @param array $instance Widget instance options.
	 * @param array $new_instance New widget instance options.
	 * @param array $old_instance Old widget instance options.
	 *
	 * @return array|bool
	 */
	public function save( $instance, $new_instance, $old_instance ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( isset( $_POST['user-menus-widget-editor-nonce'] ) && wp_verify_nonce( $_POST['user-menus-widget-editor-nonce'], 'user-menus-widget-editor-nonce' ) ) {
			$new_instance            = parse_widget_options( $new_instance );
			$instance['which_users'] = $new_instance['which_users'];
			$instance['roles']       = $new_instance['roles'];

			if ( 'logged_in' === $instance['which_users'] ) {
				$allowed_roles = allowed_user_roles();

				// Validate chosen roles and remove non-allowed roles.
				foreach ( (array) $instance['roles'] as $key => $role ) {
					if ( ! array_key_exists( $role, $allowed_roles ) ) {
						unset( $instance['roles'][ $key ] );
					}
				}
			} else {
				unset( $instance['roles'] );
			}
		} else {
			$old_instance            = parse_widget_options( $old_instance );
			$instance['which_users'] = $old_instance['which_users'];

			if ( empty( $old_instance['roles'] ) ) {
				unset( $instance['roles'] );
			} else {
				$instance['roles'] = $old_instance['roles'];
			}
		}

		return $instance;
	}


}
