<?php

namespace app\extensions\helper;

use lithium\template\Helper;
use lithium\security\Auth;

class Authenticate extends Helper {

	public function isLogged() {
		return Auth::check('default');
	}

	public function getUserData($field) {
		$user = Auth::check('default');
		return $user[$field];
	}

}
?>