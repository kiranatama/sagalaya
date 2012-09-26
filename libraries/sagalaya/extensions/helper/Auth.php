<?php

namespace sagalaya\extensions\helper;

use lithium\template\Helper;
use sagalaya\extensions\security\Auth as sAuth;

/**
 * 
 * @author Mukhamad Ikhsan
 *
 */
class Auth extends Helper {

	/**
	 * Check if current session is logged user or not
	 */
	public function isLogged() {
		return sAuth::check('default');
	}

	/**
	 * Get the info of the current user session
	 * @param string $field
	 */
	public function getUserData($field) {
		$user = sAuth::check('default');
		return $user->$field;
	}
}

?>