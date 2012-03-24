<?php

namespace sagalaya\extensions\data;

use lithium\util\Validator;

/**
 * 
 * @author Mukhamad Ikhsan
 *
 */
class ModelValidator {
	
	public static $_errors;
	
	/**
	 * 
	 * @param Model $object
	 * @param array $object_hash
	 */
	public static function isValid($object, $object_hash = array()) {
		$errors = ModelValidator::validate($object, $object_hash);
		return empty($errors);
	}
	
	/**
	 * 
	 * @param Model $object
	 * @param array $object_hash
	 */
	public static function validate($object, $object_hash = array()) {
		
		$errors = null;			
		if (!in_array(spl_object_hash($object), $object_hash)) {
			$object_hash[] = spl_object_hash($object);
		}
		$reflection = new \ReflectionClass($object);
		$classname = $reflection->getName();
		$validations = $object->validations;
		
		if (!empty($validations)) {
			
			$unique = $equalWith = $custom = array();			 
			
			foreach ($validations as $field => $rules) {
				foreach ($rules as $key => $value) {
					if ($value[0] == "unique") {
						$unique[] = array($field, "message" => $value['message']);
						if (count($validations[$field]) == 1) {
							unset($validations[$field]);
						} else {
							unset($validations[$field][$key]);
						}
					} else if ($value[0] == "equalWith") {
						$equalWith[] = array($field, "message" => $value['message'], "with" => $value['with']);
						if (count($validations[$field]) == 1) {
							unset($validations[$field]);
						} else {
							unset($validations[$field][$key]);
						}
					} else if ($value[0] == "custom") {
						$custom[] = array($field, "message" => $value['message'], "function" => $value['function']);
						if (count($validations[$field]) == 1) {
							unset($validations[$field]);
						} else {
							unset($validations[$field][$key]);
						} 
					}
				}
			}
						
			$errors = Validator::check(static::convertToArray($object), $validations);
			
			/** Unique checking */
			foreach ($unique as $key => $value) {
				$result = $classname::getRepository()->findOneBy(array($value[0] => $object->$value[0]));
				if (!empty($result) && !(isset($object->id) && $object->id == $result->id)) {
					$errors[$value[0]][] = $value["message"];
				}
			}
			
			/** EqualWith checking */
			foreach ($equalWith as $key => $value) {
				if (isset($object->$value['with']) && $object->$value[0] != $object->$value['with']) {
					$errors[$value[0]][] = $value["message"];
				}
			}
			
			/** Custom validations */
			foreach ($custom as $key => $value) {
				$rule = create_function('$object', $value['function']);
				if ($rule($object, $object->$value[0]) === false) {
					$errors[$value[0]][] = $value['message'];
				}
			}
						
			$reflection = new \ReflectionClass($object);
			$properties = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);
			try {
				foreach ($properties as $property) {					
					$property->setAccessible(true);					
					if (ModelAnnotation::match($property, array('ManyToMany', 'OneToMany'))) {
						$relation = $property->getValue($object);						
						foreach ($relation as $item) {							
							if (!in_array(spl_object_hash($item), $object_hash)) {
								if (!ModelValidator::isValid($item, $object_hash)) {
									$errors[$property->getName()] = $item->getErrors();
								}
							}
						}
					} elseif(ModelAnnotation::match($property, array('ManyToOne', 'OneToOne'))) {						
						if ($item = $property->getValue($object)) {
							if (!in_array(spl_object_hash($item), $object_hash)) {
								if (!ModelValidator::isValid($item, $object_hash)) {
									$errors[$property->getName()] = $item->getErrors();
								}
							}
						}
					}
				}
			} catch (\ReflectionException $e) {
				die($e->getTrace() . "-" . $e->getMessage());				
				continue;
			}
		}
		
		ModelValidator::$_errors[spl_object_hash($object)] = $errors;
		return $errors;		
	}
	
	/**
	 * 
	 * @param Model $object
	 */
	public static function getErrors($object) {
		return ModelValidator::$_errors[spl_object_hash($object)];		
	}
	
	/**
	 * 
	 * @param Model $object
	 */
	public static function convertToArray($object) {
		$result = array();
		$reflector = new \ReflectionClass($object);
		$properties = $reflector->getProperties(\ReflectionProperty::IS_PROTECTED);
		foreach ($properties as $property) {
			$property->setAccessible(true);
			$value = $property->getValue($object);
			$field = $property->getName();
			if ($value == null) {
				$result[$field] = null;
			} else {
				if (ModelAnnotation::match($property, array('ManyToMany', 'OneToMany'))) {
					$result[$field] = $value->count();					
				} elseif (ModelAnnotation::match($property, array('ManyToOne', 'OneToOne'))) {				
					$result[$field] = $value->id;
				} else {
					$result[$field] = $value;
				}	
			}
		}
		return $result;
	}
	
}