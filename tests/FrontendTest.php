<?php
/**
 * Frontend tests.
 *
 * @package UserMenus
 */

/**
 * FrontendTest class.
 */
class FrontendTest extends WP_UnitTestCase {

	/**
	 * Test if block controls are enabled.
	 *
	 * @return void
	 */
	public function testHasBlockControls() {
		$test_attribute_sets = [
			[
				'attrs' => [],
			],
			[
				'attrs' => [
					'userMenus' => null,
				],
			],
			[
				'attrs' => [
					'userMenus' => [
						'enabled' => false,
					],
				],
			],
			[
				'attrs' => [
					'userMenus' => [
						'enabled' => true,
					],
				],
			],
		];

		$expected_results = [
			false,
			false,
			false,
			true,
		];

		$frontend = new \UserMenus\Frontend( [] );

		foreach ( $test_attribute_sets as $i => $block ) {
			$this->assertSame( $frontend->has_block_controls( $block ), $expected_results[ $i ] );
		}
	}

	/**
	 * Test if block controls are retrieved correctly.
	 *
	 * @return void
	 */
	public function testGetBlockControls() {
		$test_block = [
			'attrs' => [
				'userMenus' => [
					'enabled' => true,
					'rules'   => [
						'device' => [
							'onMobile'  => true,
							'onTablet'  => false,
							'onDesktop' => false,
						],
					],
				],
			],
		];

		$frontend = new \UserMenus\Controllers\Frontend( [] );

		$controls = $frontend->get_block_controls( $test_block );

		$this->assertSame( $controls, $test_block['attrs']['userMenus'] );
	}

	public function testAddBlockClasses() {
		$test_blocks_html = [
			'<p>Some text</p>',
			'<div class="gallery"><img src="#" /></div>',
			"<div class='gallery'><img src='#' /></div>",
			'<div class="gallery with-other-class" title="Some Title"></div>',
		];

		$expected_blocks_html = [
			'<p class=um-hide-on-mobile">Some text</p>',
			'<div class="gallery um-hide-on-mobile"><img src="#" /></div>',
			"<div class='gallery um-hide-on-mobile'><img src='#' /></div>",
			'<div class="gallery with-other-class um-hide-on-mobile" title="Some Title"></div>',
		];

		$test_block = [
			'attrs' => [
				'userMenus' => [
					'enabled' => true,
					'rules'   => [
						'device' => [
							'hideOn' => [
								'mobile'  => true,
								'tablet'  => false,
								'desktop' => false,
							],
						],
					],
				],
			],
		];

		$frontend = new \UserMenus\Frontend( [] );

		foreach ( $test_blocks_html as $i => $block_content ) {
			$this->assertSame( $expected_blocks_html[ $i ], $frontend->render_block( $block_content, $test_block ) );
		}
	}
}
