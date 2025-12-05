<?php
/**
 * Menu Editor Controller.
 *
 * Adds custom fields to the WordPress menu editor for controlling
 * menu item visibility based on user login status and roles.
 *
 * Uses React/TypeScript for the UI components.
 *
 * @package UserMenus
 */

namespace UserMenus\Controllers\Admin;

use UserMenus\Base\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class MenuEditor
 */
class MenuEditor extends Controller {

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
		add_action( 'admin_head-nav-menus.php', [ $this, 'register_metaboxes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_nav_menu_item_custom_fields', [ $this, 'render_fields' ], 10, 4 );
		add_action( 'wp_update_nav_menu_item', [ $this, 'save_menu_item' ], 10, 2 );
		add_action( 'admin_footer-nav-menus.php', [ $this, 'render_templates' ] );
	}

	/**
	 * Register metaboxes.
	 *
	 * @return void
	 */
	public function register_metaboxes() {
		add_meta_box(
			'user_menus_links',
			__( 'User Links', 'user-menus' ),
			[ $this, 'render_metabox' ],
			'nav-menus',
			'side',
			'default'
		);
	}

	/**
	 * Render metabox for adding Login/Register/Logout links.
	 *
	 * @return void
	 */
	public function render_metabox() {
		global $_nav_menu_placeholder, $nav_menu_selected_id;

		$link_types = $this->get_user_link_types();

		$walker = new \Walker_Nav_Menu_Checklist();

		$removed_args = [
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		];

		$registration_disabled = '1' !== get_option( 'users_can_register' );
		?>
		<div id="user-menus-div" class="user-menus">
			<div id="tabs-panel-user-menus-all" class="tabs-panel tabs-panel-active">

				<?php if ( $registration_disabled ) : ?>
					<small>
						<span class="dashicons dashicons-info"></span>
						<?php esc_html_e( 'Registration is currently disabled on your site.', 'user-menus' ); ?>
					</small>
				<?php endif; ?>

				<ul id="user-menus-checklist-all" class="categorychecklist form-no-clear <?php echo $registration_disabled ? 'user-menus-registration-disabled' : ''; ?>">
					<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $link_types ), 0, (object) [ 'walker' => $walker ] ); ?>
				</ul>

				<p class="button-controls">
					<span class="list-controls">
						<a href="<?php echo esc_url( add_query_arg( [ 'user-menus-all' => 'all', 'selectall' => 1 ], remove_query_arg( $removed_args ) ) ); ?>#user-menus-div" class="select-all">
							<?php esc_html_e( 'Select All', 'user-menus' ); ?>
						</a>
					</span>

					<span class="add-to-menu">
						<input type="submit" <?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'user-menus' ); ?>" name="add-user-menus-menu-item" id="submit-user-menus-div" />
						<span class="spinner"></span>
					</span>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Get user link types.
	 *
	 * @return array<int,object>
	 */
	protected function get_user_link_types() {
		$links = [
			[
				'object' => 'login',
				'title'  => __( 'Login', 'user-menus' ),
			],
			[
				'object' => 'register',
				'title'  => __( 'Register', 'user-menus' ),
			],
			[
				'object' => 'logout',
				'title'  => __( 'Logout', 'user-menus' ),
			],
		];

		$i = 0;
		foreach ( $links as $key => $link ) {
			$i++;
			$links[ $key ] = (object) array_replace_recursive( [
				'type'             => '',
				'object'           => '',
				'title'            => '',
				'ID'               => $i,
				'object_id'        => $i,
				'db_id'            => 0,
				'post_parent'      => 0,
				'menu_item_parent' => 0,
				'url'              => '',
				'target'           => '',
				'attr_title'       => '',
				'description'      => '',
				'classes'          => [],
				'xfn'              => '',
			], $link );
		}

		return $links;
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @param string $hook Page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'nav-menus.php' !== $hook ) {
			return;
		}

		$user_roles = \UserMenus\get_allowed_user_roles();

		// Get asset metadata for dependencies.
		$asset_file = $this->container->get_path( 'dist/menu-editor.asset.php' );
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: [
				'dependencies' => [ 'wp-element', 'wp-components', 'wp-i18n' ],
				'version'      => $this->container->get( 'version' ),
			];

		// Enqueue React-based menu editor script.
		wp_enqueue_script(
			'user-menus-menu-editor',
			$this->container->get_url( 'dist/menu-editor.js' ),
			$asset['dependencies'],
			$asset['version'],
			true
		);

		// Enqueue menu editor styles.
		wp_enqueue_style(
			'user-menus-menu-editor',
			$this->container->get_url( 'dist/menu-editor.css' ),
			[ 'wp-components' ],
			$asset['version']
		);

		// Localize script with configuration.
		wp_localize_script(
			'user-menus-menu-editor',
			'userMenusMenuEditor',
			[
				'adminUrl'  => admin_url(),
				'pluginUrl' => $this->container->get_url(),
				'userRoles' => $user_roles,
				'userCodes' => $this->container->get( 'user_codes' )->get_codes_for_js(),
				'defaults'  => $this->container->get( 'menu_items' )->get_defaults(),
				'i18n'      => $this->get_i18n_strings(),
			]
		);
	}

	/**
	 * Get i18n strings for JavaScript.
	 *
	 * @return array<string,string>
	 */
	protected function get_i18n_strings() {
		return [
			'whoCanSee'       => __( 'Who can see this link?', 'user-menus' ),
			'everyone'        => __( 'Everyone', 'user-menus' ),
			'loggedInUsers'   => __( 'Logged In Users', 'user-menus' ),
			'loggedOutUsers'  => __( 'Logged Out Users', 'user-menus' ),
			'chooseCanSee'    => __( 'Choose which roles can see this link', 'user-menus' ),
			'chooseCannotSee' => __( 'Choose which roles won\'t see this link', 'user-menus' ),
			'avatarSize'      => __( 'Avatar Size', 'user-menus' ),
			'redirectTo'      => __( 'Where should users be taken afterwards?', 'user-menus' ),
			'currentPage'     => __( 'Current Page', 'user-menus' ),
			'homePage'        => __( 'Home Page', 'user-menus' ),
			'customUrl'       => __( 'Custom URL', 'user-menus' ),
			'enterCustomUrl'  => __( 'Enter a URL user should be redirected to', 'user-menus' ),
			'insertUserCode'  => __( 'Insert User Code', 'user-menus' ),
			'userLink'        => __( 'User Link', 'user-menus' ),
		];
	}

	/**
	 * Render menu item fields.
	 *
	 * Renders the React mount point and hidden form fields for each menu item.
	 *
	 * @param int    $item_id Menu item ID.
	 * @param object $item    Menu item object.
	 * @param int    $depth   Item depth.
	 * @param array  $args    Additional arguments.
	 * @return void
	 */
	public function render_fields( $item_id, $item, $depth, $args ) {
		$options = $this->container->get( 'menu_items' )->get_options( $item_id );

		wp_nonce_field( 'user_menus_save', 'user_menus_nonce' );

		// Hidden fields for form submission.
		?>
		<input type="hidden" class="um-which-users" name="user_menus[<?php echo esc_attr( $item->ID ); ?>][which_users]" value="<?php echo esc_attr( $options['which_users'] ); ?>" />
		<input type="hidden" class="um-can-see" name="user_menus[<?php echo esc_attr( $item->ID ); ?>][can_see]" value="<?php echo esc_attr( $options['can_see'] ); ?>" />
		<input type="hidden" class="um-roles" name="user_menus[<?php echo esc_attr( $item->ID ); ?>][roles]" value="<?php echo esc_attr( implode( ',', $options['roles'] ) ); ?>" />
		<input type="hidden" class="um-avatar-size" name="user_menus[<?php echo esc_attr( $item->ID ); ?>][avatar_size]" value="<?php echo esc_attr( $options['avatar_size'] ); ?>" />
		<input type="hidden" class="um-redirect-type" name="user_menus[<?php echo esc_attr( $item->ID ); ?>][redirect_type]" value="<?php echo esc_attr( $options['redirect_type'] ); ?>" />
		<input type="hidden" class="um-redirect-url" name="user_menus[<?php echo esc_attr( $item->ID ); ?>][redirect_url]" value="<?php echo esc_attr( $options['redirect_url'] ); ?>" />

		<?php // React mount point. ?>
		<div class="user-menus-fields" data-item-id="<?php echo esc_attr( $item->ID ); ?>"></div>

		<?php // Data for React component. ?>
		<script type="application/json" class="user-menus-item-data">
			<?php
			echo wp_json_encode( [
				'options'    => $options,
				'itemObject' => $item->object ?? '',
			] );
			?>
		</script>
		<?php
	}

	/**
	 * Save menu item data.
	 *
	 * @param int $menu_id Menu ID.
	 * @param int $item_id Item ID.
	 * @return void
	 */
	public function save_menu_item( $menu_id, $item_id ) {
		// Verify nonce.
		if ( ! isset( $_POST['user_menus_nonce'] ) ||
			 ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['user_menus_nonce'] ) ), 'user_menus_save' ) ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		// Get submitted data.
		if ( empty( $_POST['user_menus'][ $item_id ] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$data = wp_unslash( $_POST['user_menus'][ $item_id ] );

		// Parse roles from comma-separated string (from React hidden field).
		$roles = [];
		if ( ! empty( $data['roles'] ) ) {
			if ( is_array( $data['roles'] ) ) {
				$roles = array_map( 'sanitize_text_field', $data['roles'] );
			} else {
				$roles = array_filter( array_map( 'sanitize_text_field', explode( ',', $data['roles'] ) ) );
			}
		}

		$options = [
			'which_users'   => isset( $data['which_users'] ) ? sanitize_text_field( $data['which_users'] ) : '',
			'can_see'       => isset( $data['can_see'] ) ? sanitize_text_field( $data['can_see'] ) : 'yes',
			'roles'         => $roles,
			'avatar_size'   => isset( $data['avatar_size'] ) ? absint( $data['avatar_size'] ) : 24,
			'redirect_type' => isset( $data['redirect_type'] ) ? sanitize_text_field( $data['redirect_type'] ) : 'current',
			'redirect_url'  => isset( $data['redirect_url'] ) ? esc_url_raw( $data['redirect_url'] ) : '',
		];

		$this->container->get( 'menu_items' )->save_options( $item_id, $options );
	}

	/**
	 * Render JavaScript templates for user codes.
	 *
	 * @return void
	 */
	public function render_templates() {
		$codes = $this->container->get( 'user_codes' )->get_valid_codes();
		?>
		<script type="text/html" id="tmpl-um-user-codes">
			<div class="um-user-codes">
				<button type="button" title="<?php esc_attr_e( 'Insert User Menu Codes', 'user-menus' ); ?>">
					<i class="dashicons dashicons-arrow-left"></i>
				</button>
				<ul>
					<?php foreach ( $codes as $code => $label ) : ?>
						<li>
							<a title="<?php echo esc_attr( $label ); ?>" href="#" data-code="<?php echo esc_attr( $code ); ?>">
								<?php echo esc_html( $label ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</script>
		<?php
	}
}
