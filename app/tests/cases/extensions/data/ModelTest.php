<?php

namespace app\tests\cases\extensions\data;

use app\models\User;
use app\models\Group;
use app\models\Country;

/**
 * 
 * @author Mukhamad Ikhsan
 *
 */
class ModelTest extends \lithium\test\Unit {
	
	public function testCreateModel() {
		
		$indonesia = new Country(array('name' => 'Indonesia'));
		$indonesia->save();
		
		$user = new User(array(
			'fullname' => 'Mukhamad Ikhsan',
			'email' => 'some.email@email.com',
			'password' => 'rahasia',
			'profile' => array(
				'profession' => 'programmer',
				'nationality' => $indonesia
			)
		));		
		
		$this->assertEqual('Mukhamad Ikhsan', $user->fullname);
		$this->assertEqual('programmer', $user->profile->profession);
		$this->assertEqual('Indonesia', $user->profile->nationality->name);
		
		$this->assertTrue($user->save());
		$ikhsan = User::getRepository()->findOneBy(array('email' => 'some.email@email.com'));
		
		$users = User::findAll(array(
			'where' => array(
				'and' => array(
					array('fullname' => array('like' => '%Ikhsan')),
					array('fullname' => array('eq' => 'Mukhamad Ikhsan')),
					array('fullname' => array('neq' => 'Haris Riswandi'))
				)
			),
			'leftJoin' => array(
				array('field' => 'groups')
			)
		)); 
		$compacts = User::getCompactList('fullname');
		
		$this->assertEqual(1, count($users));
		$this->assertEqual(1, count($compacts));
				
		$this->assertEqual('Mukhamad Ikhsan', $ikhsan->fullname);
		$this->assertEqual('programmer', $ikhsan->profile->profession);
		$this->assertEqual('Indonesia', $ikhsan->profile->nationality->name);
		
		$ikhsan->delete();
		$indonesia->delete();
		
		$this->assertNull(User::getRepository()->findOneBy(array('email' => 'some.email@email.com')));
		$this->assertNull(Country::getRepository()->findOneBy(array('name' => 'Indonesia')));
	}
	
	public function testRelationModel() {
		$user = new User(array(
			'fullname' => 'Mukhamad Ikhsan',
			'email' => 'some.email@email.com',
			'password' => 'rahasia',
			'groups' => array(
				array('description' => 'Bikers'),
				array('description' => 'Modelers')		
			)
		));
		
		$this->assertFalse($user->save());
		$this->assertEqual(array(
				'groups' => array(
					array('name'=>array('Name cannot be empty.')),
					array('name'=>array('Name cannot be empty.'))
				)), $user->getErrors());
	}
	
	public function testValidateModel() {
		$user = new User(array(
			'fullname' => 'Mukhamad Ikhsan',
			'email' => 'some.email@email.com'
		));
		
		$this->assertFalse($user->save());
		$this->assertEqual(array('password' => array('Password cannot be empty.')), $user->getErrors());			
	}
	
	public function testCascadeModel() {
		$user = new User(array(
				'fullname' => 'Mukhamad Ikhsan',
				'email' => 'some.email@email.com',
				'password' => 'rahasia'
		));
		
		$this->assertTrue($user->save());
		
		$group = new Group(array('name' => "Testing"));
		$this->assertTrue($group->save());
		
		$user->addGroup($group);
		$this->assertTrue($user->save());

		$this->assertEqual(1, count($user->groups));
		$this->assertEqual(1, count($group->members));			
		
		$ikhsan = User::getRepository()->findOneBy(array('email' => 'some.email@email.com'));
		$testing = Group::getRepository()->findOneBy(array('name' => 'Testing'));
		
		$this->assertEqual(1, count($ikhsan->groups));
		$this->assertEqual(1, count($testing->members));
		
		$user->delete(); $group->delete();
	}
		
}
