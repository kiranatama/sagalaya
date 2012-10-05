<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sagalaya\extensions\doctrine\mapper;

use \sagalaya\extensions\doctrine\mapper\reflection\SchemaReflection;
use \lithium\data\Connections;
use \lithium\util\Inflector;
use \lithium\util\Set;
use \Doctrine\ORM\Mapping\ClassMetadataInfo;
use \Doctrine\ORM\Mapping\Driver\Driver;

/**
 *
 */
class ModelDriver implements Driver {
	protected static $_bindingMapping = array(
			'belongsTo' => 'mapManyToOne',
			'hasMany' => 'mapOneToMany',
			'hasOne' => 'mapOneToOne'
	);
	protected static $_bindings = array();

	public function loadMetadataForClass($className, ClassMetadataInfo $metadata) {
		if (!($metadata->reflClass instanceof SchemaReflection)) {
			$metadata->reflClass = new SchemaReflection(get_class($metadata));
		}
		$metadata->primaryTable['name'] = $className::meta('source');

		$primaryKey = $className::meta('key');
		$bindings = static::bindings($className);
		$relations = array();
		if (!empty($bindings)) {
			foreach($bindings as $type => $set) {
				foreach($set as $key => $relation) {
					$mapping = array(
							'fetch' => \Doctrine\ORM\Mapping\AssociationMapping::FETCH_EAGER,
							'fieldName' => $relation['fieldName'],
							'sourceEntity' => $className,
							'targetEntity' => $relation['class'],
							'mappedBy' => null,
							'cascade' => !empty($relation['dependent']) ? array('remove') : array(),
							'optional' => ($type != 'belongsTo')
					);

					if (in_array($type, array(/*'belongsTo',*/ 'hasOne', 'hasMany'))) {
						$inverse = ($type == 'belongsTo');
						$mapping['joinColumns'][] = array(
								'fieldName' => !$inverse ? $relation['key'] : $relation['fieldName'],
								'name' => !$inverse ? $relation['fieldName'] : $relation['key'],
								'referencedColumnName' => $relation['class']::meta('key')
						);
					}

					if (in_array($type, array('belongsTo', 'hasOne', 'hasMany'))) {
						$mapping['mappedBy'] = static::_fieldName($mapping);
					}

					$relations[$type][$key] = $mapping;
				}
			}
		}

		$schema = (array) $className::schema();

		$metadata->reflClass->setRelations($relations);
		$metadata->reflClass->setSchema($schema);

		$belongsToFields = !empty($bindings['belongsTo']) ?
		Set::combine(array_values($bindings['belongsTo']), '/key', '/fieldName') :
		array();

		foreach ($schema as $field => $column) {
			$mapping = array_merge(array(
					'id' => $field == $primaryKey,
					'fieldName' => !empty($belongsToFields[$field]) ? $belongsToFields[$field] : $field,
					'columnName' => $field
			), (array) $column);

			$metadata->mapField($mapping);

			if ($mapping['id']) {
				$metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_AUTO);
			}
		}

		foreach($relations as $type => $set) {
			foreach($set as $key => $mapping) {
				$metadata->{static::$_bindingMapping[$type]}($mapping);
				$mapping = $metadata->associationMappings[$mapping['fieldName']];
			}
		}
	}

	public function isTransient($className) {
		return true;
	}

	public function getAllClassNames() {
		$classes = array();
		return $classes;
	}

	public function preload() {
		$tables = array();
		return $tables;
	}

	public static function bindings($className, $type = null) {
		if (empty(static::$_bindings[$className])) {
			$ns = function($class) use ($className) {
				static $namespace;
				$namespace = $namespace ?: preg_replace('/\w+$/', '', $className);
				return "{$namespace}{$class}";
			};

			$modelName = $className::meta('name');
			$bindings = array();
			foreach(static::$_bindingMapping as $binding => $method) {
				$relations = $className::relations($binding);
				if (empty($relations)) {
					$bindings[$binding] = array();
					continue;
				}

				foreach($relations as $key => $value) {
					$defaults = array(
							'alias' => null,
							'class' => null,
							'key' => null,
							'fieldName' => null,
							'conditions' => null,
							'fields' => true
					);

					if ($binding != 'belongsTo') {
						$defaults['dependent'] = false;
					}

					if ($binding == 'hasMany') {
						$defaults = array_merge($defaults, array(
								'order' => null,
								'limit' => null,
								'exclusive' => null,
								'finder' => null,
								'counter' => null
						));
					}

					$relation = array();
					if (is_array($value)) {
						$relation = $value;
					}

					$relation = array_merge($defaults, $relation);

					if (!is_string($key) && is_string($value)) {
						$relation['class'] = $value;
					} elseif (is_string($key)) {
						$relation['class'] = $key;
					}

					if (empty($relation['key'])) {
						switch($binding) {
							case 'belongsTo':
								$relation['key'] = Inflector::underscore($relation['class']) . '_id';
								break;
							case 'hasOne':
							case 'hasMany':
								$relation['key'] = Inflector::underscore($modelName) . '_id';
								break;
						}
					}

					if (strpos($relation['class'], '\\') === false) {
						$relation['class'] = $ns($relation['class']);
					}

					if (empty($relation['alias'])) {
						$relation['alias'] = $relation['class']::meta('name');
					}

					if (!is_string($key)) {
						$key = $relation['alias'];
					}

					if (empty($relation['fieldName'])) {
						$relation['fieldName'] = static::_fieldName($relation['class']::meta('name'));
					}

					$bindings[$binding][$key] = $relation;
				}
			}
			static::$_bindings[$className] = $bindings;
		}
		return !empty($type) ? static::$_bindings[$className][$type] : static::$_bindings[$className];
	}

	protected static function _fieldName($className) {
		if (is_array($className)) {
			$className = $className['sourceEntity'];
		}
		$className = strpos($className, '\\') !== false ?
		substr($className, strrpos($className, '\\') + 1) :
		$className;
		return strtolower($className[0]).substr($className, 1);
	}
}

?>