<?php

namespace JP\UM\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JP\UM\User\Codes
 */
class Codes {

	/**
	 * @return array
	 */
	public static function valid_codes() {
		return array(
			'first_name',
			'last_name',
			'username',
			'display_name',
			'nickname',
			'email',
		);
	}

}
