<?php

class RulesTest extends \WP_UnitTestCase {
	public function testOldRuleConversion() {
		$rules = new \UserMenus\Rules();

		$old_rule = $rules->remap_old_rule( [
			'id'       => 'user_has_commented',
			'group'    => 'User',
			'name'     => 'Has Commented',
			'fields'   => [
				'morethan' => [
					'label' => 'More Than (optional)',
					'type'  => 'number',
					'std'   => 0,
				],
				'lessthan' => [
					'label' => 'Less Than (optional)',
					'type'  => 'number',
					'std'   => 0,
				],
			],
			'callback' => [ 'ConditionCallbacks', 'user_has_commented' ],
			'priority' => 10,
			'advanced' => false,
		] );

		$new_rule = [
			'name'     => 'user_has_commented',
			'label'    => 'Has Commented',
			'category' => 'User',
			'format'   => '{label}',
			'verbs'    => null,
			'fields'   => [
				'morethan' => [
					'label' => 'More Than (optional)',
					'type'  => 'number',
					'std'   => 0,
				],
				'lessthan' => [
					'label' => 'Less Than (optional)',
					'type'  => 'number',
					'std'   => 0,
				],
			],
			'callback' => [ 'ConditionCallbacks', 'user_has_commented' ],
			'priority' => 10,
			'frontend' => false,
		];

		foreach ( $old_rule as $key => $value ) {
			$this->assertEquals( $value, $new_rule[ $key ] );
		}
	}
}
