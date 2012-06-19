<?php

namespace sagalaya\extensions\helper;

use lithium\template\Helper;
use sagalaya\extensions\security\Auth;

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
		return Auth::check('default');
	}

	/**
	 * Get the info of the current user session
	 * @param string $field
	 */
	public function getUserData($field) {
		$user = Auth::check('default');
		return $user->$field;
	}
}

?>