<?php
/**
 * Menu Importer Service.
 *
 * Handles importing User Menus data via WordPress WXR importer.
 *
 * @package UserMenus
 */

namespace UserMenus\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Class MenuImporter
 */
class MenuImporter {

	/**
	 * Initialize the importer.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'wxr_importer.pre_process.post_meta', [ $this, 'import_post_meta' ], 10, 2 );
		add_action( 'import_post_meta', [ $this, 'import_post_meta_legacy' ], 10, 3 );
	}

	/**
	 * Handle post meta import for WXR Importer 2.x.
	 *
	 * @param array $meta    Post meta data.
	 * @param int   $post_id Post ID.
	 * @return array
	 */
	public function import_post_meta( $meta, $post_id ) {
		if ( empty( $meta['key'] ) ) {
			return $meta;
		}

		// Handle User Menus specific meta.
		if ( '_user_menus_options' === $meta['key'] ) {
			$value = maybe_unserialize( $meta['value'] );
			if ( is_array( $value ) ) {
				update_post_meta( $post_id, $meta['key'], $value );
				// Return empty to prevent duplicate processing.
				return [];
			}
		}

		// Handle legacy User Menus meta conversion.
		if ( '_menu_item_um_options' === $meta['key'] ) {
			$value = maybe_unserialize( $meta['value'] );
			if ( is_array( $value ) ) {
				// Convert legacy format to new format.
				$new_value = $this->convert_legacy_options( $value );
				update_post_meta( $post_id, '_user_menus_options', $new_value );
				return [];
			}
		}

		return $meta;
	}

	/**
	 * Handle post meta import for legacy WordPress importer.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $key      Meta key.
	 * @param mixed  $value    Meta value.
	 * @return void
	 */
	public function import_post_meta_legacy( $post_id, $key, $value ) {
		// Handle User Menus meta.
		if ( '_user_menus_options' === $key ) {
			$value = maybe_unserialize( $value );
			if ( is_array( $value ) ) {
				update_post_meta( $post_id, $key, $value );
			}
		}

		// Handle legacy User Menus meta conversion.
		if ( '_menu_item_um_options' === $key ) {
			$value = maybe_unserialize( $value );
			if ( is_array( $value ) ) {
				$new_value = $this->convert_legacy_options( $value );
				update_post_meta( $post_id, '_user_menus_options', $new_value );
			}
		}
	}

	/**
	 * Convert legacy User Menus options to new format.
	 *
	 * @param array $old_options Legacy options.
	 * @return array
	 */
	protected function convert_legacy_options( $old_options ) {
		$new_options = [];

		// Map old option names to new ones.
		$mapping = [
			'which_users'   => 'which_users',
			'can_see'       => 'can_see',
			'roles'         => 'roles',
			'avatar_size'   => 'avatar_size',
			'redirect_type' => 'redirect_type',
			'redirect_url'  => 'redirect_url',
		];

		foreach ( $mapping as $old_key => $new_key ) {
			if ( isset( $old_options[ $old_key ] ) ) {
				$new_options[ $new_key ] = $old_options[ $old_key ];
			}
		}

		return $new_options;
	}
}
