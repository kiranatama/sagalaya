<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Db
 */

namespace Zend\Db\Adapter\Driver\Sqlsrv;

/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 */
class ErrorException extends \Exception
{

    /**
     * Errors
     * 
     * @var array
     */
    protected $errors = array();

    /**
     * Construct
     * 
     * @param boolean $errors 
     */
    public function __construct($errors = false)
    {
        $this->errors = ($errors === false) ? sqlsrv_errors() : $errors;
    }

}
