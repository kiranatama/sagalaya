<?php

namespace app\models;

/**
 * @Entity(repositoryClass="app\resources\repository\ProfileRepository")
 * @Table(name="profiles")
 */
class Profile extends \app\extensions\data\Model {
	
	/** @Id @Column(type="integer") @GeneratedValue */
	protected $id;
	
	/** @Column(type="string") */
	protected $profession;
	
	/** @ManyToOne(targetEntity="Country") */
	protected $nationality;
	
}