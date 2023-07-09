<?php
/**
 * Rule engine rule model.
 *
 * @package UserMenus
 * @subpackage Models
 */

namespace UserMenus\Models\RuleEngine;

use function UserMenus\plugin;
use function UserMenus\Rules\current_rule;

/**
 * Handler for condition rules.
 *
 * @package UserMenus
 */
class Rule extends Item {

	/**
	 * Unique Hash ID.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Rule name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Rule options.
	 *
	 * @var array
	 */
	public $options;

	/**
	 * Rule not operand.
	 *
	 * @var boolean
	 */
	public $not_operand;

	/**
	 * Rule extras.
	 *
	 * Such as post type or taxnomy like meta.
	 *
	 * @var array
	 */
	public $extras = [];

	/**
	 * Rule is frontend only.
	 *
	 * @var boolean
	 */
	public $frontend_only = false;

	/**
	 * Rule definition.
	 *
	 * @var array
	 */
	public $definition;

	/**
	 * Rule is deprecated.
	 *
	 * @var boolean
	 */
	public $deprecated = false;

	/**
	 * Build a rule.
	 *
	 * @param array $rule Rule data.
	 *
	 * @throws \Exception If rule not found.
	 */
	public function __construct( $rule ) {
		$rule = wp_parse_args( $rule, [
			'id'         => '',
			'name'       => '',
			'notOperand' => false,
			'options'    => [],
			'extras'     => [],
		]);

		if ( isset( $rule['deprecated'] ) ) {
			$this->deprecated = $rule['deprecated'];
		}

		$name = $rule['name'];

		$this->definition = plugin( 'rules' )->get_rule( $name );

		if ( ! $this->definition ) {
			/* translators: 1. Rule name. */
			throw new \Exception( sprintf( __( 'Rule `%s` not found.', 'user-menus' ), $name ) );
		}

		$extras = isset( $this->definition['extras'] ) ? $this->definition['extras'] : [];

		$this->id            = $rule['id'];
		$this->name          = $name;
		$this->not_operand   = $rule['notOperand'];
		$this->frontend_only = isset( $this->definition['frontend'] ) ? $this->definition['frontend'] : false;
		$this->options       = $this->parse_options( $rule['options'] );
		$this->extras        = array_merge( $extras, $rule['extras'] );
	}

	/**
	 * Parse rule options based on rule definitions.
	 *
	 * @param array $options Array of rule opions.
	 * @return array
	 */
	public function parse_options( $options = [] ) {
		return $options;
	}

	/**
	 * Check the results of this rule.
	 *
	 * @return bool
	 */
	public function check_rule() {
		if ( $this->is_js_rule() ) {
			return true;
		}

		$check = $this->run_check();

		return $this->not_operand ? ! $check : $check;
	}

	/**
	 * Check the results of this rule.
	 *
	 * @return bool True if rule passes, false if not.
	 *
	 * @throws \Exception If rule callback is not callable.
	 */
	private function run_check() {
		$callback = isset( $this->definition['callback'] ) ? $this->definition['callback'] : null;

		if ( ! $callback ) {
			/* translators: 1. Rule name. */
			throw new \Exception( sprintf( __( 'Rule `%s` has no callback.', 'user-menus' ), $this->name ) );
		}

		if ( ! is_callable( $callback ) ) {
			/* translators: 1. Rule name. 2. Callback name. */
			throw new \Exception( sprintf( __( 'Rule `%1$s` callback is not callable (%2$s).', 'user-menus' ), $this->name, $callback ) );
		}

		// Set global current rule so it can be easily accessed.
		current_rule( $this );

		if ( $this->deprecated ) {
			$settings = [
				'target'   => $this->name,
				'settings' => $this->options,
			];

			// Old rules had the settings passed as the first argument.
			$check = call_user_func( $callback, $settings );
		} else {
			/**
			 * All rule options can be accessed via the global.
			 *
			 * @see \UserMenus\Rules\current_rule()
			 */
			$check = call_user_func( $callback );
		}

		// Clear global current rule.
		current_rule( null );

		return $check;
	}

	/**
	 * Check if this rule's callback is based in JS rather than PHP.
	 *
	 * @return bool
	 */
	public function is_js_rule() {
		return $this->frontend_only;
	}

	/**
	 * Return the rule check as boolean or null if the rule is JS based.
	 *
	 * @return bool|null
	 */
	public function get_check() {
		if ( $this->is_js_rule() ) {
			return null;
		}

		return $this->run_check();
	}

	/**
	 * Return the rule check as an array of information.
	 *
	 * Useful for debugging.
	 *
	 * @return array|null
	 */
	public function get_check_info() {
		if ( $this->is_js_rule() ) {
			return null;
		}

		return [
			'result' => $this->run_check(),
			'id'     => $this->id,
			'rule'   => $this->name,
			'not'    => $this->not_operand,
			'args'   => $this->options,
			'def'    => $this->definition,
		];
	}
}
