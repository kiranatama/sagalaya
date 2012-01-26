<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace app\extensions\doctrine\mapper\reflection;

use \lithium\util\Set;

class SchemaReflection extends \ReflectionClass {
	protected $_relations;
	protected $_schema;

	public function getRelations() {
		return $this->_relations;
	}

	public function setRelations($relations) {
		$this->_relations = $relations;
	}

	public function getSchema() {
		return $this->_schema;
	}

	public function setSchema($schema) {
		$this->_schema = $schema;
	}

	public function getProperty($name) {
		$fields = array_keys($this->getSchema());
		$relations = $this->getRelations();
		if (!empty($relations)) {
			foreach($relations as $type => $set) {
				foreach($set as $key => $relation) {
					$fields[] = $relation['fieldName'];
				}
			}
		}

		$property = null;
		try {
			$property = parent::getProperty($name);
		} catch(\ReflectionException $e) {
			if (!in_array($name, $fields)) {
				throw $e;
			}
		}

		if (empty($property)) {
			$property = new SchemaReflectionProperty($this, $name);
		}

		return $property;
	}
}

?>