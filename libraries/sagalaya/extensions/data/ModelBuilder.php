<?php

namespace sagalaya\extensions\data;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * 
 * @author Mukhamad Ikhsan
 *
 */
class ModelBuilder {
	
	public static $properties, $class, $namespace;	
	
	/**
	 * 
	 * @param unknown_type $object
	 */
	public static function init($object) {		
		static::$class = new \ReflectionClass($object);
		static::$properties = static::$class->getProperties(\ReflectionProperty::IS_PROTECTED);
		static::$namespace = static::$class->getNamespaceName();		
	}
	
	/**
	 * 
	 * @param unknown_type $object
	 * @param unknown_type $data
	 */
	public static function create($object, $data = array()) {
		
		static::init($object);
		
		foreach (static::$properties as $property) {
			if (ModelAnnotation::match($property, array('ManyToMany', 'OneToMany'))) {
				$property->setAccessible(true);
				$property->setValue($object, new ArrayCollection());	
			}			
		}
		
		foreach ($data as $field => $value) {
			try {
				$comment = ModelAnnotation::get($object, $field);
				
				if (preg_match('|ManyToMany|', $comment) || preg_match('|OneToMany|', $comment)) {
					static::add($object, $field, $value);					
				} else if (preg_match('|ManyToOne|', $comment) || preg_match('|OneToOne|', $comment)) {	
					static::set($object, $field, $value);																											
				} else {
					$object->$field = $value;					
				}								
				
			} catch (\ReflectionException $e) {
				continue;
			}
		}
		
	}
	
	public static function update($object, $data = array()) {
		
		static::init($object);
		foreach ($data as $field => $value) {
			try {
				
				$comment = ModelAnnotation::get($object, $field);
						
				if (preg_match('|ManyToMany|', $comment) || preg_match('|OneToMany|', $comment)) {	
					$object->$field->clear();				
					static::add($object, $field, $value);
				} else if (preg_match('|ManyToOne|', $comment) || preg_match('|OneToOne|', $comment)) {
					static::set($object, $field, $value);
				} else {
					$object->$field = $value;
				}
		
			} catch (\ReflectionException $e) {
				continue;
			}
		}
	}
	
	/**
	 * 
	 * @param unknown_type $object
	 * @param unknown_type $field
	 * @param unknown_type $value
	 */
	public static function set($object, $field, $value) {
		
		$comment = ModelAnnotation::get($object, $field);
		$targetEntity = static::$namespace . '\\' . ModelAnnotation::get($object, $field, 'targetEntity');
		
		if (is_array($value)) {
			$instance = new $targetEntity($value);
		} else if (is_int($value)) {
			$instance = $targetEntity::get($value);
		} else if (is_object($value)) {
			$instance = $value;
		}

		if (isset($instance)) {
			$object->$field = $instance;
			if (preg_match('|inversedBy|', $comment)) {
				$inversedBy = ModelAnnotation::get($object, $field, 'inversedBy');
				$instance->$inversedBy = $object;
			} else if (preg_match('|mappedBy|', $comment)) {
				$mappedBy = ModelAnnotation::get($object, $field, 'mappedBy'); 
				$instance->$mappedBy = $object;
			}
		}
	}
	
	/**
	 * 
	 * @param unknown_type $object
	 * @param unknown_type $field
	 * @param unknown_type $value
	 */
	public static function add($object, $field, $value) {		
		$targetEntity = static::$namespace . '\\' . ModelAnnotation::get($object, $field, 'targetEntity');		
		foreach ($value as $index => $item) {
			if (is_array($item)) {
				$instance = $targetEntity::getRepository()->findOneBy($item);
			} else if (is_int($item)) {
				$instance = $targetEntity::get($item);
			} else if (is_object($item)) {
				$instance = $item;
			}
			
			if (!is_object($instance) && is_array($item)) {				
				$instance = new $targetEntity($item);				
			}
			
			if (isset($instance)) {
				$object->$field->add($instance);
			}
		}
	}
}