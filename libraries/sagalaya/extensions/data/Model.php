<?php

namespace sagalaya\extensions\data;

use lithium\data\Connections;

/**
 * Description of core Model
 * This class must inherited by all model class
 * @author Mukhamad Ikhsan
 */
abstract class Model {

	/**
	 * Query result in object graph
	 */
	const Model_Object = 1;

	/**
	 * Query result in scalar graph
	 */
	const Model_Scalar = 2;

	/**
	 * Query result in single scalar
	 */
	const Model_Single_Scalar = 3;

	/**
	 * Query result in array
	 */
	const Model_Array = 4;

	/**
	 * Query result in simple object
	 */
	const Model_Simple_Object = 5;

	/**
	 * Constructor for all models, if child declared constructor, parent constructor must be called
	 * @param array $args
	 */
	public function __construct($args = array()) {
		ModelBuilder::create($this, $args);
	}

	/**
	 * getter function
	 * @param string $field
	 */
	public function __get($field) {
		if (isset($this->$field)) {
			return $this->$field;
		} else {
			return null;
		}
	}

	/**
	 * setter function
	 * @param string $field
	 * @param mixed $value
	 */
	public function __set($field, $value) {
		if ($field == "properties") {
			ModelBuilder::update($this, $value);
		} else {
			$setter = "set" . ucfirst($field);
			if (method_exists($this, $setter)) {
				$this->$setter($value);
			} else {
				$this->$field = $value;
			}
		}
		return $this;
	}

	/**
	 * Shortcut for calling repository
	 * @param string $method
	 * @param mixed $args
	 */
	public static function __callStatic($method, $args) {
		return self::getRepository()->$method($args[0]);
	}

	/**
	 * function to return the classname of called class
	 * @param boolean $short
	 * @return string
	 */
	public static function _name($short = false) {
		$className = get_called_class();
		if ($short) {
			$splitted = explode('\\', $className);
			$className = end($splitted);
			$className = strtolower($className);
		}
		return $className;
	}

	/**
	 * function to get alias for the class
	 * @param string $alias
	 * @return string
	 */
	public static function _alias($alias = null) {
		if ($alias) {
			$className = $alias;
		} else {
			$className = self::_name(true);
		}
		if (in_array($className, array('group'))) {
			$className = "_{$className}";
		}
		return $className;
	}

	/**
	 * Remove the object from database
	 * @param integer $id
	 */
	public function delete() {
		if ($this && is_object($this)) {
			self::getEntity()->remove($this);
			self::getEntity()->flush();
		}
	}

	/**
	 * Persist the object to database
	 * @return boolean
	 */
	public function save() {
		if (ModelValidator::isValid($this)) {
			self::getEntity()->persist($this);
			self::getEntity()->flush();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * return errors information if not valid when trying to save
	 * @return NULL
	 */
	public function getErrors() {
		return ModelValidator::getErrors($this);
	}

	/**
	 * Return query result in array, where the key as object id, and the value of specific field
	 * @param string $field
	 * @param string $order
	 * @return array query result
	 */
	public static function getCompactList($field, $conditions = array()) {
			
		$query = self::findAll($conditions);
		$result = array();
		
		foreach ($query as $row) {
			if (is_array($field)) {
				$result[$row->id] = '';
				foreach ($field as $column) {
					$result[$row->id] .= $row->$column . ',';
				}
				$result[$row->id] = rtrim($result[$row->id], ',');
			} else {
				$result[$row->id] = $row->$field;
			}
		}
		
		return $result;
	}

	/**
	 * get EntityManager object
	 * @param string $connection
	 * @return object
	 */
	public static function getEntity($connection = 'default') {
		return Connections::get($connection)->getEntityManager();
	}

	/**
	 * get model repository
	 * @return type
	 */
	public static function getRepository() {
		return self::getEntity()->getRepository(get_called_class());
	}

	/**
	 *
	 * @param integer $id
	 * @return object
	 */
	public static function get($id) {
		return self::getEntity()->find(get_called_class(), $id);
	}

	/**
	 * Processing supllying custom query made
	 * @param array $options
	 * @return list of object
	 */
	public static function processQuery($options = array()) {

		$className = self::_alias();
		$qb = Model::getEntity()->createQueryBuilder();
		$selected = array();

		if (isset($options['where'])) {

			if (isset($options['where']['and'])) {
				$and = $qb->expr()->andx();
				foreach ($options['where']['and'] as $rule) {
					$and->add(self::addRule($qb, $rule));
				}
			}

			if (isset($options['where']['or'])) {
				$or = $qb->expr()->orx();
				foreach ($options['where']['or'] as $rule) {
					$or->add(self::addRule($qb, $rule));
				}
			}

		}

		if (isset($options['leftJoin'])) {
			foreach ($options['leftJoin'] as $join) {
				$selected[] = self::_alias($join['field']);
			}
		}

		if (isset($options['innerJoin'])) {
			foreach ($options['innerJoin'] as $join) {
				$selected[] = self::_alias($join['field']);
			}
		}

		if (isset($options['select'])) {
			foreach ($options['select'] as $select) {				
				if (isset($select['aggregate'])) {
					$aggregate = strtoupper($select['aggregate']);
					$as = (isset($select['as'])) ? $select['as'] : $select['field'];
					if (is_array($select['field'])) {
						$separator = (isset($select['operator'])) ? $select['operator'] : ",";
						foreach ($select['field'] as $key => $field) {
							$select['field'][$key] = "{$className}.{$field}";
						}
						$selectField = implode(" {$separator} ", $select['field']);						
					} else {
						$selectField =	"{$className}.{$select['field']}";
					}										
					switch ($aggregate) {
						case 'SUM' :							
							$selected[] = "SUM({$selectField}) as {$as}";
							break;
						case 'AVG' :
							$selected[] = "AVG({$selectField}) as {$as}";
							break;
						case 'COUNT':
							$selected[] = "COUNT({$selectField}) as {$as}";
							break;
					}
				} else {
					$selected[] = $select['field'];
				}
			}
		}
		
		$qb->select($className . (!empty($selected) ? ", " : "") . implode(", ", $selected) .
				(isset($options['groupBy']['select']) ? ", {$options['groupBy']['select']}" : ""))
				->from(get_called_class(), $className);


		$and = self::addJoin($qb, $options, $className, (isset($and) ? $and : null));

		if (isset($and)) {
			$qb->where($and);
		}

		if (isset($or)) {
			$qb->orWhere($or);
		}

		if (isset($options['orderBy'])) {
			$orders = array();
			foreach ($options['orderBy']['fields'] as $field) {
				$orders[] = "$className.$field";
			}
			$qb->addOrderBy(implode(",", $orders),
					(isset($options['orderBy']['direction']) ? $options['orderBy']['direction'] : null));
		}

		if (isset($options['offset'])) {
			$qb->setFirstResult($options['offset']);
		}

		if (isset($options['limit'])) {
			$qb->setMaxResults($options['limit']);
		}

		if (isset($options['groupBy'])) {
			foreach ($options['groupBy']['fields'] as $by) {
				$qb->addGroupBy($by);
			}
		}

		return $qb;
	}

	/**
	 * findAll is intensive method that using of processQuery function
	 * 
	 * example of using findAll function
	 * 
	 * User::findAll(array(
	 * 
	 * 		'select' => array(
	 * 			array('field' => 'type'),
	 * 			array('field' => array('salary', 'others'), 
	 * 					'aggregate' => 'SUM', 'operator' => '*', 'as' => 'income'),
	 * 			array('field' => 'id', 'aggregate' => 'COUNT', 'as' => 'number')
	 * 		),
	 * 
	 * 		'where' => array(
	 * 			'and' => array(
	 * 				array('fullname' => array('is' => 'NOT NULL')),
	 * 				array('city' => array('like' => '%Bandung%')) 
	 * 			),
	 * 			'or' => array(
	 *				array('birthday' => array('lte' => '2012-10-10'))
	 * 			)
	 * 		),
	 *  
	 * 		'leftJoin' => array(
	 * 			array('field' => 'group', 
	 * 					'where' => array(
	 * 						array('name' => array('eq' => 'administrator'))
	 * 			))
	 * 		),
	 * 
	 * 		'offset' => 0, 'limit' => 10, 'groupBy' => 'group',
	 * 		'orderBy' => array('fields' => array('age', 'fullname'), 'direction' => 'DESC')
	 * ), User::Model_Array);
	 *  
	 * @param array $options
	 * @param string $type
	 */
	public static function findAll($options = array(), $type = Model::Model_Object) {
			
		$qb = self::processQuery($options);
			
		switch ($type) {
			case Model::Model_Object :
				return $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
			case Model::Model_Array :
				return $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
			case Model::Model_Scalar :
				return $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SCALAR);
			case Model::Model_Single_Scalar :
				return $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SINGLE_SCALAR);
			case Model::Model_Simple_Object :
				return $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SIMPLEOBJECT);
		}				
			
		throw new \Exception("Wrong provided type of returning query for Model::findAll(). \n
				Must one of these (OBJECT, ARRAY, SCALAR, SINGLESCALAR, SIMPLE)");
	}

	/**
	 * Debug query for processQuery
	 * @param array $options
	 */
	public static function debugQuery($options = array()) {
			
		$qb = self::processQuery($options);
		$br = "<br />";
			
		$debug = "DQL Result : {$br}\n{$qb->getDQL()}{$br}{$br}\n";
		$debug .= "SQL Result : {$br}\n{$qb->getQuery()->getSQL()}{$br}\n";
			
		die($debug);
	}

	/**
	 * Recursively process join query
	 * @param QueryBuilder $qb
	 * @param Array $options
	 * @param String $className
	 * @param object $and
	 */
	public static function addJoin($qb, $options, $className, $and = null) {
		foreach (array('leftJoin', 'innerJoin') as $joinType) {
			if (isset($options[$joinType])) {
				foreach ($options[$joinType] as $join) {

					if (isset($join['where'])) {
						if (!isset($and)) {
							$and = $qb->expr()->andx();
						}
						foreach ($join['where'] as $rule) {
							$and->add(self::addRule($qb, $rule, self::_alias($join['field'])));
						}
					}

					$qb->$joinType("{$className}.{$join['field']}", self::_alias($join['field']));

					if (isset($join[$joinType])) {
						$and = self::addJoin($qb, $join, self::_alias($join['field']), $and);
					}
				}
			}
		}
		return $and;
	}

	/**
	 * Add rule to conditions
	 * @param QueryBuilder $qb
	 * @param array $rule
	 * @param string $alias
	 */
	private static function addRule($qb, $rule, $alias = null) {
			
		foreach ($rule as $key => $value) {
			$field = $key;
			foreach ($value as $_cond => $_match) {
				$condition = $_cond;
				$match = $_match;
			}
		}
			
		if (!is_numeric($match) && !is_array($match) &&
				$condition != 'is') {
			if ($match instanceof \DateTime) {
				$match = $match->format('Y-m-d');
			}

			if (!preg_match('|\'.*\'|', $match)) {
				$match = "'{$match}'";
			}
		} else if (is_array($match) && $condition == 'between') {
			foreach ($match as $index => $value) {
				if ($value instanceof \DateTime) {
					$value = $value->format('Y-m-d');
				}
					
				if (!preg_match('|\'.*\'|', $value)) {
					$match[$index] = "'{$value}'";
				}
			}
		}

		$className = self::_alias($alias);

		switch ($condition) {
			case 'is' :
				if (strcasecmp('NULL', $match) == 0) {
					return $qb->expr()->isNull("{$className}.{$field}");
				} else {
					return $qb->expr()->isNotNull("{$className}.{$field}");
				}
			case 'like' :
				return $qb->expr()->like("{$className}.{$field}", $match);
			case 'neq' :
				return $qb->expr()->neq("{$className}.{$field}", $match);
			case 'nlike' :
				return $qb->expr()->like("{$className}.{$field} NOT", $match);
			case 'in' :
				if (is_string(reset($match))) {
					return $qb->expr()->in("{$className}.{$field}", "'" . implode("', '", $match) . "'");
				}
				return $qb->expr()->in("{$className}.{$field}", implode(",", $match));
			case 'notin' :
				return $qb->expr()->in("{$className}.{$field} NOT", implode(",", $match));
			case 'gt' :
				return $qb->expr()->gt("{$className}.{$field}", $match);
			case 'gte' :
				return $qb->expr()->gte("{$className}.{$field}", $match);
			case 'lt' :
				return $qb->expr()->lt("{$className}.{$field}", $match);
			case 'lte' :
				return $qb->expr()->lte("{$className}.{$field}", $match);
			case 'between' :
				return $qb->expr()->between("{$className}.{$field}", reset($match), end($match));
			default :
				return $qb->expr()->eq("{$className}.{$field}", $match);
		}

	}

	/**
	 * Return array of model object
	 * @param array $joins
	 * @return array
	 */
	public function toArray($joins = array()) {
		
		if (empty($this->id)) {
			return ModelValidator::convertToArray($this);
		}
		
		foreach ($joins as $key => $join) {
			$joins[$key] = array('field' => $join);
		}
		
		return end(self::findAll(array(
			'where' => array(
					'and' => array(
							array('id' => array('eq' => $this->id))
					)
			),
		'leftJoin' => $joins), Model::Model_Array));
	}
}

?>