<?php
/**
 * Rule registery
 *
 * @package UserMenus
 */

namespace UserMenus\RuleEngine;

/**
 * Rules registry
 */
class Rules {

	/**
	 * Array of rules.
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Current global rule instance.
	 *
	 * @var Rule
	 */
	public $current_rule = null;

	/**
	 * Rules constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Get the current global rule instance.
	 *
	 * @param Rule|null|false $rule Rule instance to set.
	 * @return Rule|null
	 */
	public function current_rule( $rule = false ) {
		if ( false === $rule ) {
			return $this->current_rule;
		}

		$this->current_rule = $rule;
	}

	/**
	 * Set up rules list.
	 *
	 * @return void
	 */
	public function init() {
		$this->register_built_in_rules();
		$this->register_deprecated_rules();
	}

	/**
	 * Register new rule type.
	 *
	 * @param array $rule New rule to register.
	 * @return void
	 */
	public function register_rule( $rule ) {
		if ( $this->is_rule_valid( $rule ) ) {
			$rule = wp_parse_args( $rule, $this->get_rule_defaults() );

			$index = $rule['name'];
			/**
			 * In the case multiple conditions are registered with the same
			 * identifier, we append an integer. This do/while quickly increases
			 * the integer by one until a valid new key is found.
			 */
			if ( array_key_exists( $index, $this->data ) ) {
				$i = 0;
				do {
					++$i;
					$index = $rule['name'] . '-' . $i;
				} while ( array_key_exists( $index, $this->data ) );
			}

			$indexs[]             = $index;
			$this->data[ $index ] = $rule;
		}
	}

	/**
	 * Check if rule is valid.
	 *
	 * @param array $rule Rule to test.
	 * @return boolean
	 */
	public function is_rule_valid( $rule ) {
		return ! empty( $rule ) && true;
	}

	/**
	 * Get array of all registered rules.
	 *
	 * @return array
	 */
	public function get_rules() {
		return $this->data;
	}

	/**
	 * Get a rule definition by name.
	 *
	 * @param string $rule_name Rule definition or null.
	 * @return array|null
	 */
	public function get_rule( $rule_name ) {
		return isset( $this->data[ $rule_name ] ) ? $this->data[ $rule_name ] : null;
	}

	/**
	 * Get array of registered rules filtered for the block-editor.
	 *
	 * @return array
	 */
	public function get_block_editor_rules() {
		$rules = $this->get_rules();

		/**
		 * Filter the rules.
		 *
		 * @param array $rules Rules.
		 *
		 * @return array
		 */
		return apply_filters( 'user_menus/rule_engine_rules', $rules );
	}

	/**
	 * Get list of verbs.
	 *
	 * @return array List of verbs with translatable text.
	 */
	public function get_verbs() {
		return [
			'are'         => __( 'Are', 'user-menus' ),
			'arenot'      => __( 'Are Not', 'user-menus' ),
			'is'          => __( 'Is', 'user-menus' ),
			'isnot'       => __( 'Is Not', 'user-menus' ),
			'has'         => __( 'Has', 'user-menus' ),
			'hasnot'      => __( 'Has Not', 'user-menus' ),
			'doesnothave' => __( 'Does Not Have', 'user-menus' ),
			'does'        => __( 'Does', 'user-menus' ),
			'doesnot'     => __( 'Does Not', 'user-menus' ),
			'was'         => __( 'Was', 'user-menus' ),
			'wasnot'      => __( 'Was Not', 'user-menus' ),
			'were'        => __( 'Were', 'user-menus' ),
			'werenot'     => __( 'Were Not', 'user-menus' ),
		];
	}

	/**
	 * Get a list of built in rules.
	 *
	 * @return void
	 */
	private function register_built_in_rules() {
		$verbs = $this->get_verbs();

		$rules = array_merge(
			$this->get_user_rules(),
			$this->get_general_content_rules(),
			$this->get_post_type_rules(),
			$this->get_taxonomy_rules()
		);

		foreach ( $rules as $rule ) {
			$this->register_rule( $rule );
		}
	}

	/**
	 * Get a list of user rules.
	 *
	 * @return array
	 */
	public function get_user_rules() {
		$verbs = $this->get_verbs();
		return [
			'user_is_logged_in' => [
				'name'     => 'user_is_logged_in',
				'label'    => __( 'Logged In', 'user-menus' ),
				'context'  => [ 'user' ],
				'category' => __( 'User', 'user-menus' ),
				'format'   => '{category} {verb} {label}',
				'verbs'    => [ $verbs['is'], $verbs['isnot'] ],
				'callback' => '\is_user_logged_in',
			],
			'user_has_role'     => [
				'name'     => 'user_has_role',
				'label'    => __( 'Role(s)', 'user-menus' ),
				'context'  => [ 'user' ],
				'category' => __( 'User', 'user-menus' ),
				'format'   => '{category} {verb} {label}',
				'verbs'    => [ $verbs['has'], $verbs['doesnothave'] ],
				'fields'   => [
					'roles' => [
						'label'    => __( 'Role(s)', 'user-menus' ),
						'type'     => 'tokenselect',
						'multiple' => true,
						'options'  => wp_roles()->get_names(),
					],
				],
				'callback' => '\UserMenus\Rules\user_has_role',
			],
		];
	}

	/**
	 * Get a list of general content rules.
	 *
	 * @return array
	 */
	public function get_general_content_rules() {
		$rules = [];
		$verbs = $this->get_verbs();

		$rules['content_is_front_page'] = [
			'name'     => 'content_is_front_page',
			'label'    => __( 'The Home Page', 'user-menus' ),
			'context'  => [ 'content' ],
			'category' => __( 'Content', 'user-menus' ),
			'format'   => '{category} {verb} {label}',
			'verbs'    => [ $verbs['is'], $verbs['isnot'] ],
			'callback' => '\UserMenus\Rules\content_is_home_page',
		];

		$rules['content_is_blog_index'] = [
			'name'     => 'content_is_blog_index',
			'label'    => __( 'The Blog Index', 'user-menus' ),
			'context'  => [ 'content', 'posttype:post' ],
			'category' => __( 'Content', 'user-menus' ),
			'format'   => '{category} {verb} {label}',
			'verbs'    => [ $verbs['is'], $verbs['isnot'] ],
			'callback' => '\UserMenus\Rules\content_is_blog_index',
		];

		$rules['content_is_search_results'] = [
			'name'     => 'content_is_search_results',
			'label'    => __( 'A Search Result Page', 'user-menus' ),
			'context'  => [ 'content', 'search' ],
			'category' => __( 'Content', 'user-menus' ),
			'format'   => '{category} {verb} {label}',
			'verbs'    => [ $verbs['is'], $verbs['isnot'] ],
			'callback' => '\is_search',
		];

		$rules['content_is_404_page'] = [
			'name'     => 'content_is_404_page',
			'label'    => __( 'A 404 Error Page', 'user-menus' ),
			'context'  => [ 'content', '404' ],
			'category' => __( 'Content', 'user-menus' ),
			'format'   => '{category} {verb} {label}',
			'verbs'    => [ $verbs['is'], $verbs['isnot'] ],
			'callback' => '\is_404',
		];

		return $rules;
	}

	/**
	 * Get a list of WP post type rules.
	 *
	 * @return array
	 */
	public function get_post_type_rules() {
		$verbs = $this->get_verbs();

		$rules      = [];
		$post_types = get_post_types( [ 'public' => true ], 'objects' );

		foreach ( $post_types as $name => $post_type ) {
			$type_rules = [];

			if ( $post_type->has_archive ) {
				$type_rules[ "content_is_{$name}_archive" ] = [
					'name'     => "content_is_{$name}_archive",
					/* translators: %s: Post type singular name */
					'label'    => sprintf( __( 'A %s Archive', 'user-menus' ), $post_type->labels->singular_name ),
					'callback' => '\UserMenus\Rules\content_is_post_type_archive',
				];
			}

			$type_rules[ "content_is_{$name}" ] = [
				'name'     => "content_is_{$name}",
				/* translators: %s: Post type singular name */
				'label'    => sprintf( __( 'A %s', 'user-menus' ), $post_type->labels->singular_name ),
				'callback' => '\UserMenus\Rules\content_is_post_type',
			];

			$type_rules[ "content_is_selected_{$name}" ] = [
				'name'     => "content_is_selected_{$name}",
				/* translators: %s: Post type singular name */
				'label'    => sprintf( __( 'A Selected %s', 'user-menus' ), $post_type->labels->singular_name ),
				'fields'   => [
					'selected' => [
						/* translators: %s: Post type plurals name */
						'placeholder' => sprintf( __( 'Select %s.', 'user-menus' ), strtolower( $post_type->labels->name ) ),
						'type'        => 'postselect',
						'post_type'   => $name,
						'multiple'    => true,
					],
				],
				'callback' => '\UserMenus\Rules\content_is_selected_post',
			];

			$type_rules[ "content_is_{$name}_with_id" ] = [
				'name'     => "content_is_{$name}_with_id",
				/* translators: %s: Post type singular name */
				'label'    => sprintf( __( 'A %s with ID', 'user-menus' ), $post_type->labels->singular_name ),
				'fields'   => [
					'selected' => [
						/* translators: %s: Post type singular name */
						'placeholder' => sprintf( __( '%s IDs: 128, 129', 'user-menus' ), strtolower( $post_type->labels->name ) ),
						'type'        => 'text',
					],
				],
				'callback' => '\UserMenus\Rules\content_is_selected_post',
			];

			if ( is_post_type_hierarchical( $name ) ) {
				$type_rules[ "content_is_child_of_{$name}" ] = [
					'name'     => "content_is_child_of_{$name}",
					/* translators: %s: Post type plural name */
					'label'    => sprintf( __( 'A Child of Selected %s', 'user-menus' ), $post_type->labels->name ),
					'fields'   => [
						'selected' => [
							/* translators: %s: Post type plural name */
							'placeholder' => sprintf( __( 'Select %s.', 'user-menus' ), strtolower( $post_type->labels->name ) ),
							'type'        => 'postselect',
							'post_type'   => $name,
							'multiple'    => true,
						],
					],
					'callback' => '\UserMenus\Rules\content_is_child_of_post',
				];

				$type_rules[ "content_is_ancestor_of_{$name}" ] = [
					'name'     => "content_is_ancestor_of_{$name}",
					/* translators: %s: Post type plural name */
					'label'    => sprintf( __( 'An Ancestor of Selected %s', 'user-menus' ), $post_type->labels->name ),
					'fields'   => [
						'selected' => [
							/* translators: %s: Post type plural name */
							'placeholder' => sprintf( __( 'Select %s.', 'user-menus' ), strtolower( $post_type->labels->name ) ),
							'type'        => 'postselect',
							'post_type'   => $name,
							'multiple'    => true,
						],
					],
					'callback' => '\UserMenus\Rules\content_is_ancestor_of_post',
				];
			}

			$templates = wp_get_theme()->get_page_templates();

			if ( 'page' === $name && ! empty( $templates ) ) {
				$type_rules[ "content_is_{$name}_with_template" ] = [
					'name'     => "content_is_{$name}_with_template",
					/* translators: %s: Post type singular name */
					'label'    => sprintf( __( 'A %s With Template', 'user-menus' ), $post_type->labels->singular_name ),
					'fields'   => [
						'selected' => [
							'type'     => 'tokenselect',
							'multiple' => true,
							'options'  => array_merge( [ 'default' => __( 'Default', 'user-menus' ) ], $templates ),
						],
					],
					'callback' => '\UserMenus\Rules\content_is_ancestor_of_post',
				];
			}

			foreach ( $type_rules as $rule ) {
				// Merge defaults.
				$type_rules[ $rule['name'] ] = wp_parse_args( $rule, [
					'category' => __( 'Content', 'user-menus' ),
					'context'  => [ 'content', "posttype:{$name}" ],
					'format'   => '{category} {verb} {label}',
					'verbs'    => [ $verbs['is'], $verbs['isnot'] ],
					'fields'   => [],
					'extras'   => [
						'post_type' => $name,
					],
				] );
			}

			// Merge type rules & type tax rules.
			$rules = array_merge( $rules, $type_rules, $this->get_post_type_tax_rules( $name ) );
		}

		return $rules;
	}

	/**
	 * Generate post type taxonomy rules.
	 *
	 * @param string $name Post type name.
	 *
	 * @return array
	 */
	public function get_post_type_tax_rules( $name ) {
		$verbs = $this->get_verbs();

		$post_type  = get_post_type_object( $name );
		$taxonomies = get_object_taxonomies( $name, 'object' );
		$rules      = [];

		foreach ( $taxonomies as $tax_name => $taxonomy ) {
			$rules[ "content_is_{$name}_with_{$tax_name}" ] = [
				'name'     => "content_is_{$name}_with_{$tax_name}",
				/* translators: %1$s: Post type singular name, %2$s: Taxonomy singular name */
				'label'    => sprintf( _x( 'A %1$s with %2$s', 'condition: post type plural and taxonomy singular label ie. A Post With Category', 'user-menus' ), $post_type->labels->singular_name, $taxonomy->labels->singular_name ),
				'context'  => [ 'content', "posttype:{$name}", "taxonomy:{$tax_name}" ],
				'category' => __( 'Content', 'user-menus' ),
				'format'   => '{category} {verb} {label}',
				'verbs'    => [ $verbs['is'], $verbs['isnot'] ],
				'fields'   => [
					'selected' => [
						/* translators: %s: Taxonomy singular name */
						'placeholder' => sprintf( _x( 'Select %s.', 'condition: post type plural label ie. Select categories', 'user-menus' ), strtolower( $taxonomy->labels->name ) ),
						'type'        => 'taxonomyselect',
						'taxonomy'    => $tax_name,
						'multiple'    => true,
					],
				],
				'extras'   => [
					'post_type' => $name,
					'taxonomy'  => $tax_name,
				],
				'callback' => '\UserMenus\Rules\content_is_post_with_tax_term',
			];
		}

		return $rules;
	}

	/**
	 * Generates conditions for all public taxonomies.
	 *
	 * @return array
	 */
	public function get_taxonomy_rules() {
		$rules      = [];
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$verbs      = $this->get_verbs();

		foreach ( $taxonomies as $tax_name => $taxonomy ) {
			$tax_defaults = [
				'category' => __( 'Content', 'user-menus' ),
				'context'  => [ 'content', "taxonomy:{$tax_name}" ],
				'format'   => '{category} {verb} {label}',
				'verbs'    => [ $verbs['is'], $verbs['isnot'] ],
				'fields'   => [],
				'extras'   => [
					'taxonomy' => $tax_name,
				],
			];

			$rules[ "content_is_{$tax_name}_archive" ] = wp_parse_args( [
				'name'     => "content_is_{$tax_name}_archive",
				/* translators: %s: Taxonomy plural name */
				'label'    => sprintf( _x( 'A %s Archive', 'condition: taxonomy plural label ie. A Category Archive', 'user-menus' ), $taxonomy->labels->singular_name ),
				'callback' => '\UserMenus\Rules\content_is_taxonomy_archive',
			], $tax_defaults );

			$rules[ "content_is_selected_tax_{$tax_name}" ] = wp_parse_args( [
				'name'     => "content_is_selected_tax_{$tax_name}",
				/* translators: %s: Taxonomy plural name */
				'label'    => sprintf( _x( 'A Selected %s', 'condition: taxonomy plural label ie. A Selected Category', 'user-menus' ), $taxonomy->labels->singular_name ),
				'fields'   => [
					'selected' => [
						/* translators: %s: Taxonomy plural name */
						'placeholder' => sprintf( _x( 'Select %s.', 'condition: taxonomy plural label ie. Select Categories', 'user-menus' ), strtolower( $taxonomy->labels->name ) ),
						'type'        => 'taxonomyselect',
						'taxonomy'    => $tax_name,
						'multiple'    => true,
					],
				],
				'callback' => '\UserMenus\Rules\content_is_selected_term',
			], $tax_defaults );

			$rules[ "content_is_tax_{$tax_name}_with_id" ] = wp_parse_args( [
				'name'     => "content_is_tax_{$tax_name}_with_id",
				/* translators: %s: Taxonomy plural name */
				'label'    => sprintf( _x( 'A %s with ID', 'condition: taxonomy plural label ie. A Category with ID: Selected', 'user-menus' ), $taxonomy->labels->name ),
				'fields'   => [
					'selected' => [
						/* translators: %s: Taxonomy plural name */
						'placeholder' => sprintf( _x( '%s IDs: 128, 129', 'condition: taxonomy plural label ie. Category IDs', 'user-menus' ), strtolower( $taxonomy->labels->singular_name ) ),
						'type'        => 'text',
					],
				],
				'callback' => '\UserMenus\Rules\content_is_selected_term',
			], $tax_defaults );
		}

		return $rules;
	}

	/**
	 * Get an array of rule default values.
	 *
	 * @return array Array of rule default values.
	 */
	public function get_rule_defaults() {
		$verbs = $this->get_verbs();
		return [
			'name'     => '',
			'label'    => '',
			'context'  => [],
			'category' => __( 'Content', 'user-menus' ),
			'format'   => '{category} {verb} {label}',
			'verbs'    => [ $verbs['is'], $verbs['isnot'] ],
			'fields'   => [],
			'callback' => null,
			'frontend' => false,
		];
	}

	/**
	 * Register & remap deprecated conditions to rules.
	 *
	 * @return void
	 */
	public function register_deprecated_rules() {
		/**
		 * Filters the old conditions to be registered as rules.
		 *
		 * @deprecated 2.0.0
		 *
		 * @param array $old_rules Array of old rules to manipulate.
		 *
		 * @return array
		 */
		$old_rules = apply_filters( 'user_menus/old_conditions', [] );

		if ( ! empty( $old_rules ) ) {
			$old_rules = $this->parse_old_rules( $old_rules );

			foreach ( $old_rules as $rule ) {
				$rule['deprecated'] = true;
				$this->register_rule( $rule );
			}
		}
	}

	/**
	 * Parse rules that are still registered using the older deprecated methods.
	 *
	 * @param array $old_rules Array of old rules to manipulate.
	 * @return array
	 */
	public function parse_old_rules( $old_rules ) {
		$new_rules = [];

		foreach ( $old_rules as $key => $old_rule ) {
			$new_rules[ $key ] = $this->remap_old_rule( $old_rule );
		}

		return $new_rules;
	}

	/**
	 * Remaps keys & values from an old `condition` into a new `rule`.
	 *
	 * @param array $old_rule Old rule definition.
	 * @return array New rule definition.
	 */
	public function remap_old_rule( $old_rule ) {
		$old_rule = wp_parse_args( $old_rule, $this->get_old_rule_defaults() );

		$new_rule = [
			'format' => '{label}',
		];

		$remaped_keys = [
			'id'       => 'name',
			'name'     => 'label',
			'group'    => 'category',
			'fields'   => 'fields',
			'callback' => 'callback',
			'advanced' => 'frontend',
			'priority' => 'priority',
		];

		foreach ( $remaped_keys as $old_key => $new_key ) {
			if ( isset( $old_rule[ $old_key ] ) ) {
				$new_rule[ $new_key ] = $old_rule[ $old_key ];
				unset( $old_rule[ $old_key ] );
			}
		}

		// Merge any leftover 'unknonw' keys, with new stuff second.
		return array_merge( $new_rule, $old_rule );
	}

	/**
	 * Get an array of old rule default values.
	 *
	 * @return array Array of old rule default values.
	 */
	private function get_old_rule_defaults() {
		return [
			'id'       => '',
			'callback' => null,
			'group'    => '',
			'name'     => '',
			'priority' => 10,
			'fields'   => [],
			'advanced' => false,
		];
	}

}
