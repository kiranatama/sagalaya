<?php

namespace app\models;

/**
 * @Entity(repositoryClass="app\resources\repository\GroupRepository")
 * @Table(name="groups")
 * @HasLifecycleCallbacks
 */
class Group extends \app\extensions\data\Model {
	
	/** @Id @Column(type="integer") @GeneratedValue */
	protected $id;
	
	/** @Column(type="string") */
	protected $name;
	
	/** @Column(type="text", nullable=true) */
	protected $description;
	
	/** @ManyToMany(targetEntity="User", inversedBy="groups", cascade={"persist", "remove"}) */
	protected $members;
	
	/** @Column(type="datetime", nullable=true) */
	protected $created;
	
	/** @Column(type="datetime", nullable=true) */
	protected $modified;
	
	protected $validations = array(
		'name' => array(
				array('notEmpty', 'message' => 'Name cannot be empty.')
		)
	);
	
	/** @PrePersist */
	public function beforePersist() {
		if (!$this->created) {
			$this->__set('created', new \DateTime());
		} else {
			$this->__set('modified', new \DateTime());
		}
	}
	
	/** @PreUpdate */
	public function beforeUpdate() {
		$this->__set('modified', new \DateTime());
	}	
}