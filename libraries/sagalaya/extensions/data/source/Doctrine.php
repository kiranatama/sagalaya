<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sagalaya\extensions\data\source;

use sagalaya\extensions\doctrine\logger\SqlLogger;
use sagalaya\extensions\doctrine\mapper\ModelDriver;

use lithium\util\Set;
use lithium\core\Environment;
use lithium\data\Connections;

use Doctrine\Common\EventManager;
use Doctrine\Common\Cache\ArrayCache;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Event\Listeners\MysqlSessionInit;
use Doctrine\DBAL\Driver;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;


/**
 *
 */
class Doctrine extends \lithium\data\source\Database {
	/**
	 * Entity manager.
	 */
	protected $_em;

	/**
	 * Schema manager
	 */
	protected $_sm;

	/**
	 *
	 */
	public function __construct($config = array()) {

		$defaults = array(
				'proxy' => array(
						'auto' => (Environment::is('production'))?false:true,
						'path' => LITHIUM_APP_PATH . '/resources/tmp/cache/Doctrine/Proxies',
						'namespace' => 'Doctrine\Proxies'
				),
				'mapping' => array('class' => null, 'path' => LITHIUM_APP_PATH . '/models'),
				'configuration' => null,
				'eventManager' => null,
				'utf-8' => true,
		);
		$config = Set::merge($defaults, $config);

		$configuration = $config['configuration'] ?: new Configuration();
		$eventManager = $config['eventManager'] ?: new EventManager();

		$configuration->setProxyDir($config['proxy']['path']);
		$configuration->setProxyNamespace($config['proxy']['namespace']);

		$configuration->setAutoGenerateProxyClasses($config['proxy']['auto']);
		$configuration->setMetadataCacheImpl(new ArrayCache());

		// Annotation Driver
		$classPaths = array(LITHIUM_APP_PATH . '/models');
		$iterator = new \DirectoryIterator(reset($classPaths));
		foreach ($iterator as $file) {
			if ($file->isDir()) {
				$classPaths[] = $file->getPath();
			}
		}
		$driver = $configuration->newDefaultAnnotationDriver($classPaths);
		$configuration->setMetadataDriverImpl($driver);

		$configuration->setSqlLogger(new SqlLogger());
		$mapping = array('adapter' => 'driver', 'login' => 'user', 'database' => 'dbname');

		foreach($mapping as $source => $dest) {
			if (isset($config[$source]) && !isset($config[$dest])) {
				$config[$dest] = $config[$source];
				unset($config[$source]);
			}
		}

		$this->_em = EntityManager::create($config, $configuration, $eventManager);
		$this->_sm = $this->_em->getConnection()->getSchemaManager();

		if ($this->_em->getConnection()->getDriver() instanceof Driver\PDOMySql\Driver && $config['utf-8']) {
			$this->_em->getEventManager()->addEventSubscriber(
					new MysqlSessionInit('utf8', 'utf8_unicode_ci')
			);
		}

		$this->_em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('Decimal', 'money');
		parent::__construct($config);
	}

	/**
	 * Connects to the database using the options provided to the class constructor.
	 *
	 * @return boolean True if the database could be connected, else false.
	 */
	public function connect() {
		if (!$this->isConnected()) {
			return $this->getEntityManager()->getConnection()->connect();
		}
		return false;
	}


	/**
	 * Disconnects the adapter from the database.
	 *
	 * @return boolean True on success, else false.
	 */
	public function disconnect() {
		if ($this->isConnected()) {
			$this->getEntityManager()->getConnection()->close();
			return true;
		}
		return false;
	}

	/**
	 *
	 */
	public function isConnected(array $options = array()) {
		$defaults = array('autoConnect' => false);
		$options += $defaults;

		if (!($this->_em) || !($connection = $this->_em->getConnection())) {
			return false;
		}
		$connected = $connection->isConnected();

		if (!$connected && $options['autoConnect']) {
			$this->connect();
			return $this->_em->getConnection()->isConnected();
		}
		return $connected;
	}

	/**
	 *
	 */
	public function getEntityManager() {
		return $this->_em;
	}

	/**
	 *
	 */
	public function setEntityManager(EntityManager $em) {
		$this->_em = $em;
	}

	/**
	 *
	 */
	public function getSchemaManager() {
		return $this->_sm;
	}

	/**
	 *
	 */
	public function setSchemaManager(AbstractSchemaManager $sm) {
		$this->_sm = $sm;
	}

	/**
	 *
	 */
	public function getConnection() {
		return $this->_em->getConnection();
	}

	/**
	 * Returns the list of tables in the currently-connected database.
	 *
	 * @param string $model The fully-name-spaced class name of the model object making the request.
	 * @return array Returns an array of objects to which models can connect.
	 * @filter This method can be filtered.
	 */
	public function entities($class = null) {
		$entities = array();
		$tables = $this->getSchemaManager()->listTables();
		if (!empty($tables)) {
			foreach($tables as $table) {
				$entities[] = $table->getName();
			}
		}
		return $entities;
	}

	public function result($type, $resource, $context) {
		if (!is_object($resource)) {
			return null;
		}

		$result = null;
		switch ($type) {
			case 'next':
				if ($resource instanceof \Iterator) {
					$row = $resource->next();
					if ($row !== false) {
						$result = $row[0];
					}
				} else {
					$result = $resource;
				}
				if ($result) {
					$this->getEntityManager()->detach($result);
				}
				break;
			case 'close':
				unset($resource);
				break;
			default:
				$result = parent::result($type, $resource, $context);
			break;
		}
		return $result;
	}

	/**
	 *
	 */
	public function describe($entity, array $meta = array()) {
		$schema = array();
		$columns = $this->getSchemaManager()->listTableColumns($entity);
		$mapping = array();

		foreach($columns as $field => $column) {
			if (!$mapping[$class = get_class($column->getType())]) {
				$type = substr($class, strrpos($class, '\\') + 1);
				$mapping[$class] = strtolower(preg_replace('/Type$/', '', $type));
			}
			$schema[$field] = array_merge($column->toArray(), array('type' => $mapping[$class]));
		}
		return $schema;
	}

	/**
	 *
	 * @return RecordSet
	 */
	public function read($query, array $options = array()) {
		$doctrineQuery = $query->query();

		if (!isset($doctrineQuery)) {
			$query = $query->export($this);
			$params = compact('query', 'options');
			$doctrineQuery = $this->_filter(__METHOD__, $params, function($self, $params, $chain) {
				extract($params);
				$doctrineQuery = $self->getEntityManager()->createQueryBuilder();
				$doctrineQuery->from($options['model'], $options['model']::meta('name'));

				if (!empty($query['fields'])) {
					foreach($query['fields'] as $scope => $fields) {
						if (!is_string($scope)) {
							$scope = $query['model'];
						}
						foreach($fields as $field) {
							$doctrineQuery->addSelect("{$scope::meta('name')}.{$field}");
						}
					}
				} else {
					$doctrineQuery->addSelect($options['model']::meta('name'));
				}

				if (isset($query['conditions'])) {
					$doctrineQuery->add('where', $query['conditions']);
				}

				if (empty($query['offset']) && !empty($query['page'])) {
					$query['offset'] = ($query['page'] - 1) * $query['limit'];
				}

				if (!empty($query['offset'])) {
					$doctrineQuery->setFirstResult($query['offset']);
				}

				if (!empty($query['limit'])) {
					$doctrineQuery->setMaxResults($query['limit']);
				}

				if (!empty($query['order'])) {
					foreach($query['order'] as $field => $direction) {
						$doctrineQuery->addOrderBy($field, $direction);
					}
				}

				return $doctrineQuery;
			});

			if (!isset($doctrineQuery)) {
				return null;
			}
			$doctrineQuery = $doctrineQuery->getQuery();

			if (!empty($query['fields'])) {
				$doctrineQuery->setHint($doctrineQuery::HINT_FORCE_PARTIAL_LOAD, true);
			}
		}

		if ($query['limit'] == 1) {
			return $doctrineQuery->getSingleResult();
		}
		return $doctrineQuery->iterate();
	}

	/**
	 *
	 */
	public function conditions($conditions, $context, array $options = array()) {
		$model = $context->model();
		return $this->_parseConditions((array) $conditions, array('alias'=>$model::meta('name')));
	}

	/**
	 *
	 */
	public function fields($fields, $query) {
		$columns = array();
		if (!empty($fields)) {
			$columns = $this->schema($query);
			if (!empty($columns)) {
				$belongsToFields = array();
				foreach($columns as $key => $currentFields) {
					$className = is_string($key) ? $key : $query->model();
					$belongsTo = ModelDriver::bindings($className, 'belongsTo');
					$belongsToFields = !empty($belongsTo) ?
					Set::combine(array_values($belongsTo), '/key', '/fieldName') :
					array();
					foreach($fields as $i => $field) {
						if (!empty($belongsToFields[$field])) {
							unset($fields[$i]);
							$fields[] = $belongsToFields[$field];
						}
					}

					$columns[$key] = array_unique($fields);
				}
			}
		}
		return $columns;
	}

	/**
	 *
	 */
	public function order($order, $query) {
		if (!empty($order)) {
			$pattern = '/\s+(ASC|DESC)/i';
			$model = $query->model();
			$name = $model::meta('name');
			$sort = array();
			foreach((array) $order as $field => $direction) {
				$index = $field;
				if (!is_string($field)) {
					$field = $direction;
					$direction = null;
				}

				if (preg_match($pattern, $field, $matches)) {
					$field = preg_replace($pattern, '', $field);
					$direction = $matches[1];
				}

				if (strpos($field, '.') === false) {
					$field = "{$name}.{$field}";
				}

				$sort[$field] = strtoupper($direction ?: 'ASC');
			}
			$order = $sort;
		}
		return $order ?: array();
	}

	/**
	 *
	 */
	public function limit($limit, $query) {
		return $limit ?: array();
	}

	protected function _parseConditions(array $conditions, array $options = array()) {
		$query = $this->getEntityManager()->createQueryBuilder();
		if (empty($conditions)) {
			return null;
		} else if (is_string($conditions)) {
			$query->$clause($conditions);
		} else {
			$expr = $query->expr();
			foreach($conditions as $key => $value) {
				if (is_string($key) && in_array(strtolower($key), array('or'))) {
					$clause = strtolower($key);
					$innerQuery = $this->getEntityManager()->createQueryBuilder();
					foreach((array) $value as $innerKey => $piece) {
						if (is_string($innerKey)) {
							$piece = array($innerKey => $piece);
						}
						$innerQuery->{"{$clause}Where"}($this->_parseConditions($piece, $options));
					}
					$query->andWhere($innerQuery->getDqlPart('where'));
				} else if (is_string($key)) {
					if (strpos($key, '.') === false) {
						$key = "{$options['alias']}.{$key}";
					}
					if (is_array($value)) {
						foreach($value as $iv => $ivalue) {
							$value[$iv] = $expr->literal($ivalue);
						}
						$query->andWhere($expr->in($key, $value));
					} else {
						$query->andWhere($expr->eq($key, $expr->literal($value)));
					}
				} else {
					$query->andWhere($this->_parseConditions($value, $options));
				}
			}
		}

		return $query->getDqlPart('where');
	}

	public function create($query, array $options = array()) {
	}

	public function encoding($encoding = null) {
	}

	public function sources($class = null){
	}

	public function error() {
	}

	public function update($query, array $options = array()) {
	}

	public function delete($query, array $options = array()) {
	}

	protected function _execute($query) {
	}

	protected function _insertId($query) {
	}
}

?>
