<?php

use lithium\test\Unit;
use app\models\User;

class Model extends Unit {
	
	public function initiate() {
		$user = new User(array(
				'email' => 'ikhsan.only@gmail.com',
				'fullname' => 'Mukhamad Ikhsan',
				'password' => 'password',
				'retypePassword' => 'password'
			));
		$this->assertEqual('ikhsan.only@gmail.com', $user->__get('email'));
	}
}

?>