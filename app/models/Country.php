<?php

namespace app\models;

/**
 * @Entity(repositoryClass="app\resources\repository\ProfileRepository")
 * @Table(name="countries")
 */
class Country extends \app\extensions\data\Model {
	
	/** @Id @Column(type="integer") @GeneratedValue */
	protected $id;
	
	/** @Column(type="string", unique=true) */
	protected $name;
}