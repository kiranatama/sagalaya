<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Db
 */

namespace Zend\Db\RowGateway;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\Row;
use Zend\Db\ResultSet\RowObjectInterface;
use Zend\Db\Sql\Sql;

/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage RowGateway
 */
class RowGateway extends AbstractRowGateway
{

    /**
     * Constructor
     *
     * @param string $tableGateway
     * @param string|\Zend\Db\Sql\TableIdentifier $table
     * @param Adapter $adapter
     * @param Sql\Sql $sql
     */
    public function __construct($primaryKeyColumn, $table, $adapterOrSql = null)
    {
        // setup primary key
        $this->primaryKeyColumn = $primaryKeyColumn;

        // set table
        $this->table = $table;

        // set Sql object
        if ($adapterOrSql instanceof Sql) {
            $this->sql = $adapterOrSql;
        } elseif ($adapterOrSql instanceof Adapter) {
            $this->sql = new Sql($adapterOrSql, $this->table);
        } else {
            throw new Exception\InvalidArgumentException('A valid Sql object was not provided.');
        }

        if ($this->sql->getTable() !== $this->table) {
            throw new Exception\InvalidArgumentException('The Sql object provided does not have a table that matches this row object');
        }

        $this->initialize();
    }

}
