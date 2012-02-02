<?php

namespace app\models;

/**
 * @Entity(repositoryClass="app\resources\repository\UserRepository")
 * @HasLifecycleCallbacks
 * @Table(name="users")
 */
class User extends \app\extensions\data\Model
{

	/**
	 * @Id @Column(type="integer") @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @Column(type="string", length=64)
	 */
	protected $fullname = null;

	/**
	 * @Column(type="string", length=64)
	 */
	protected $email = null;

	/**
	 * @Column(type="string")
	 */
	protected $password = null;

	/**
	 * @Column(type="boolean")
	 */
	protected $active = false;

	/**
	 * @Column(type="text", nullable=true)
	 */
	protected $about = null;

	/**
	 * @OneToOne(targetEntity="Profile", cascade={"persist","remove"})
	 */
	protected $profile = null;

	/**
	 * @ManyToMany(targetEntity="Group", mappedBy="members",
	 * cascade={"persist","remove"})
	 */
	protected $groups = null;

	/**
	 * @Column(type="datetime")
	 */
	protected $created = null;

	/**
	 * @Column(type="datetime")
	 */
	protected $modified = null;

	protected $validations = array(
			'fullname' => array(
					array(
						'notEmpty',
						'message' => 'Fullname can\'t be empty'
					),
					array(
						'custom',
						'message' => 'Fullname can\'t be George Bush',
						'function' => 'return strcasecmp($object->fullname, "George Bush") != 0;'
					)
					),
			'email' => array(
					array(
							'notEmpty',
							'message' => 'Email can\'t be empty'
					),
					array(
							'email',
							'message' => 'Email format is not correct'
					),
					array(
							'unique',
							'message' => 'Email has been taken, try other email'
					)
			),
			'password' => array(array(
					'notEmpty',
					'message' => 'Password can\'t be empty'
			))
	);

	/**
	 * @PrePersist
	 */
	public function beforePersist()
	{
		$this->created = new \DateTime();
	}

	/**
	 * @PreUpdate
	 */
	public function beforeUpdate()
	{
		$this->modified = new \DateTime();
	}


}

?>