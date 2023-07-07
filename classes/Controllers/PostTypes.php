<?php
/**
 * Post type setup.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package UserMenus
 */

namespace UserMenus\Controllers;

use UserMenus\Base\Controller;

/**
 * Post type controller.
 */
class PostTypes extends Controller {

	/**
	 * Init controller.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'init', [ $this, 'register_rest_fields' ] );
		add_action( 'save_post_um_restriction', [ $this, 'save_post' ], 10, 3 );
	}

	/**
	 * Register `restriction` post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		/**
		 * Post Type: Restrictions.
		 */
		$labels = [
			'name'          => __( 'Restrictions', 'user-menus' ),
			'singular_name' => __( 'Restriction', 'user-menus' ),
		];

		$args = [
			'label'               => __( 'Restrictions', 'user-menus' ),
			'labels'              => $labels,
			'description'         => '',
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'show_in_rest'        => true,
			'rest_base'           => 'restrictions',
			'rest_namespace'      => 'user-menus/v2',
			'has_archive'         => false,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'delete_with_user'    => false,
			'exclude_from_search' => true,
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'can_export'          => true,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => [ 'title' ],
			'show_in_graphql'     => false,
			'capabilities'        => [
				'create_posts' => $this->container->get_permission( 'edit_restrictions' ),
				'edit_posts'   => $this->container->get_permission( 'edit_restrictions' ),
				'delete_posts' => $this->container->get_permission( 'edit_restrictions' ),
			],

		];

		register_post_type( 'um_restriction', $args );
	}

	/**
	 * Registers custom REST API fields for um_restrictions post type.
	 *
	 * @return void
	 */
	public function register_rest_fields() {
		register_rest_field( 'um_restriction', 'title', [
			'get_callback'    => function ( $obj ) {
				return get_the_title( $obj['id'] );
			},
			'update_callback' => function ( $value, $obj ) {
				wp_update_post( [
					'ID'         => $obj->ID,
					'post_title' => sanitize_text_field( $value ),
				] );
			},
		] );

		register_rest_field( 'um_restriction', 'description', [
			'get_callback'    => function ( $obj ) {
				return get_the_excerpt( $obj['id'] );
			},
			'update_callback' => function ( $value, $obj ) {
				wp_update_post( [
					'ID'           => $obj->ID,
					'post_excerpt' => sanitize_text_field( $value ),
				] );
			},
		] );

		register_rest_field( 'um_restriction', 'settings', [
			'get_callback'        => function ( $obj ) {
				return get_post_meta( $obj['id'], 'restriction_settings', true );
			},
			'update_callback'     => function ( $value, $obj ) {
				// Update the field/meta value.
				update_post_meta( $obj->ID, 'restriction_settings', $value );
			},
			'permission_callback' => function () {
				return current_user_can( $this->container->get_permission( 'edit_restrictions' ) );
			},
		] );
	}

	/**
	 * Add data version meta to new restrictions.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 *
	 * @return void
	 */
	public function save_post( $post_id, $post, $update ) {
		if ( $update ) {
			return;
		}

		add_post_meta( $post_id, 'data_version', 1 );
	}
}
