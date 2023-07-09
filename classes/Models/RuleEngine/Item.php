<?php
/**
 * Rule engine item model.
 *
 * @package UserMenus
 * @subpackage Models
 */

namespace UserMenus\Models\RuleEngine;

/**
 * Handler for condition items.
 *
 * @package UserMenus
 */
abstract class Item {

	/**
	 * Item id.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Return the checks as an array of information.
	 *
	 * Useful for debugging.
	 *
	 * @return array
	 */
	abstract public function get_check_info();
}
