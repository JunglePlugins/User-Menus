<?php
/**
 * Rule engine group model.
 *
 * @package UserMenus
 * @subpackage Models
 */

namespace UserMenus\Models\RuleEngine;

/**
 * Handler for condition groups.
 *
 * @package UserMenus
 */
class Group extends Item {

	/**
	 * Group id.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Group label.
	 *
	 * @var string
	 */
	public $label;

	/**
	 * Group query.
	 *
	 * @var Query
	 */
	public $query;

	/**
	 * Build a group.
	 *
	 * @param array $group Group data.
	 */
	public function __construct( $group ) {
		$group = wp_parse_args( $group, [
			'id'    => '',
			'label' => '',
			'query' => [],
		]);

		$this->id    = $group['id'];
		$this->label = $group['label'];
		$this->query = new Query( $group['query'] );
	}

	/**
	 * Check if this group has JS based rules.
	 *
	 * @return bool
	 */
	public function has_js_rules() {
		return $this->query->has_js_rules();
	}

	/**
	 * Check this groups rules.
	 *
	 * @return bool
	 */
	public function check_rules() {
		return $this->query->check_rules();
	}

	/**
	 * Check this groups rules.
	 *
	 * @return bool
	 */
	public function get_checks() {
		return $this->query->get_checks();
	}

	/**
	 * Return the rule check as an array of information.
	 *
	 * Useful for debugging.
	 *
	 * @return array
	 */
	public function get_check_info() {
		return $this->query->get_check_info();
	}

}
