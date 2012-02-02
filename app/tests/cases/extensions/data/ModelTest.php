<?php

namespace app\tests\cases\extensions\data;

use app\models\User;
use app\models\Group;
use app\models\Country;

use app\extensions\data\ModelValidator as Validator;

/**
 * 
 * @author Mukhamad Ikhsan
 *
 */
class ModelTest extends \lithium\test\Unit {
	
	public function testCreateModel() {
		
		$indonesia = new Country(array('name' => 'Indonesia'));	
		
		$user = new User(array(
			'fullname' => 'GEORGE BUSH',
			'email' => 'some.email@email.com',
			'password' => 'rahasia',
			'profile' => array(
				'profession' => 'programmer',
				'nationality' => $indonesia
			)
		));		
		
		$this->assertEqual('GEORGE BUSH', $user->fullname);
		$this->assertEqual('programmer', $user->profile->profession);
		$this->assertEqual('Indonesia', $user->profile->nationality->name);
		
		$this->assertFalse(Validator::isValid($user));
		$this->assertEqual(array('fullname' => array('Fullname can\'t be George Bush')), $user->getErrors());
		
		$user->fullname = 'Mukhamad Ikhsan';
		
		$this->assertTrue(Validator::isValid($user));
		$this->assertEqual(array(), $user->getErrors());
		
	}
	
	
		
}
