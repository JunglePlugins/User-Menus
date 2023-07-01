<?php
/**
 * Backward compatibility functions.
 *
 * @package UserMenus
 */

namespace UserMenus;

defined( 'ABSPATH' ) || exit;

/**
 * Get v1 restrictions from wp_options.
 *
 * @return array
 */
function get_v1_restrictions() {
	$settings = get_option( 'ca_um_settings', [] );

	if ( ! isset( $settings['restrictions'] ) ) {
		return [];
	}

	return $settings['restrictions'];
}

/**
 * Remap old conditions to new rules.
 *
 * @param array $old_conditions Array of old conditions.
 *
 * @return array
 */
function remap_conditions_to_query( $old_conditions ) {
	$query = [
		'logicalOperator' => 'and',
		'items'           => [],
	];

	// Old conditions were in a group[rules[]] format.
	foreach ( $old_conditions as $conditions ) {
		$group = [
			'id'    => uniqid(),
			'type'  => 'group',
			'query' => [
				'logicalOperator' => 'or',
				'items'           => [],
			],
		];

		// Loop over all old condtions in this group and map them to new rules.
		foreach ( $conditions as $condition ) {
			$group['query']['items'][] = remap_condition_to_rule( $condition );
		}

		$query['items'][] = $group;
	}

	return $conditions;
}

/**
 * Remap old condition to new rule.
 *
 * @param array $condition Old condition.
 *
 * @return array
 */
function remap_condition_to_rule( $condition ) {
	$target = $condition['target'];

	// Handles 95% of the work except the name remap.
	$rule = [
		'id'         => uniqid(),
		'type'       => 'rule',
		// custom rules will pass through.
		'name'       => $target,
		'notOperand' => isset( $condition['not_operand'] ) && $condition['not_operand'] ? true : false,
		'options'    => is_array( $condition['settings'] ) ? $condition['settings'] : [],
	];

	// Start from simplest to match to most complex.
	if ( 'is_front_page' === $target ) {
		$rule['name'] = 'content_is_front_page';
	} elseif ( 'is_home' === $target ) {
		$rule['name'] = 'content_is_blog_index';
	} elseif ( 'is_search' === $target ) {
		$rule['name'] = 'content_is_search_results';
	} elseif ( 'is_404' === $target ) {
		$rule['name'] = 'content_is_404_page';
	} elseif ( strpos( $target, 'tax_' ) === 0 ) {
		// Split the target into post type and modifier.
		$tax_target = explode( '_', $target );

		// Modifier should be the last key.
		$modifier = array_pop( $tax_target );

		// Post type is the remaining keys combined.
		$taxnomy = implode( '_', $tax_target );

		// Using a switch for readability.
		switch ( $modifier ) {
			case 'all':
				$rule['name'] = "content_is_{$taxnomy}_archive";
				break;

			case 'selected':
				$rule['name'] = "content_is_selected_tax_{$taxnomy}";
				break;

			case 'ID':
				$rule['name'] = "content_is_tax_{$taxnomy}_with_id";
				break;
		}
	} elseif ( strpos( $target, '_w_' ) > 0 ) {
		$pt_target = explode( '_w_', $target );
		// First key is the post type.
		$post_type = array_shift( $pt_target );
		// Last Key is the taxonomy.
		$taxonomy = array_pop( $pt_target );

		$rule['name'] = "content_is_{$post_type}_with_{$taxonomy}";
	} else {
		// Split the target into post type and modifier.
		$pt_target = explode( '_', $target );

		// Modifier should be the last key.
		$modifier = array_pop( $pt_target );

		// Post type is the remaining keys combined.
		$post_type = implode( '_', $pt_target );

		if ( post_type_exists( $post_type ) && ! empty( $modifier ) ) {
			switch ( $modifier ) {
				case 'index':
					$rule['name'] = "content_is_{$post_type}_archive";
					break;

				case 'all':
					$rule['name'] = "content_is_{$post_type}";
					break;

				case 'selected':
					$rule['name'] = "content_is_selected_{$post_type}";
					break;

				case 'ID':
					$rule['name'] = "content_is_{$post_type}_with_id";
					break;

				case 'children':
					$rule['name'] = "content_is_child_of_{$post_type}";
					break;

				case 'ancestors':
					$rule['name'] = "content_is_ancestor_of_{$post_type}";
					break;

				case 'template':
					$rule['name'] = "content_is_{$post_type}_with_template";
					break;
			}
		}
	}

	return $rule;
}
