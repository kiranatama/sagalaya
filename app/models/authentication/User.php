<?php

namespace app\models\authentication;

/**
 * @Entity(repositoryClass="core\resources\repository\UserRepository")
 * @HasLifecycleCallbacks
 * @Table(name="users")
 */
class User extends \sagalaya\extensions\data\Model
	implements \Zend\Acl\Resource\ResourceInterface, \Zend\Acl\Role\RoleInterface {
	
	/**
	 * @Id @Column(type="string", length=36) @GeneratedValue(strategy="UUID")
	 */
	protected $id = null;
	
	/**
	 * (non-PHPdoc)
	 * @see Zend\Acl\Role.RoleInterface::getRoleId()
	 */
	public function getRoleId() {
		return $this->id;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend\Acl\Resource.ResourceInterface::getResourceId()
	 */
	public function getResourceId() {
		return $this->id;
	}
}