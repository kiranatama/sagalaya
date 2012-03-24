<?php

namespace sagalaya\extensions\helper;

use lithium\template\Helper;
use lithium\security\Auth as ModelAuth;

class Auth extends Helper {

	public function isLogged() {
		return ModelAuth::check('default');
	}

	public function getUserData($field) {
		$user = ModelAuth::check('default');
		return $user[$field];
	}
}

?>