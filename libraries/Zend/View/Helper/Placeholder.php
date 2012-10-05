<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_View
 */

namespace Zend\View\Helper;

use Zend\View\Exception\InvalidArgumentException;

/**
 * Helper for passing data between otherwise segregated Views. It's called
 * Placeholder to make its typical usage obvious, but can be used just as easily
 * for non-Placeholder things. That said, the support for this is only
 * guaranteed to effect subsequently rendered templates, and of course Layouts.
 *
 * @package    Zend_View
 * @subpackage Helper
 */
class Placeholder extends AbstractHelper
{
    /**
     * Placeholder items
     * @var array
     */
    protected $_items = array();

    /**
     * @var \Zend\View\Helper\Placeholder\Registry
     */
    protected $_registry;

    /**
     * Constructor
     *
     * Retrieve container registry from Zend_Registry, or create new one and register it.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_registry = Placeholder\Registry::getRegistry();
    }

    /**
     * Placeholder helper
     *
     * @param  string $name
     * @return \Zend\View\Helper\Placeholder\Container\AbstractContainer
     * @throws InvalidArgumentException
     */
    public function __invoke($name = null)
    {
        if ($name == null) {
            throw new InvalidArgumentException('Placeholder: missing argument.  $name is required by placeholder($name)');
        }

        $name = (string) $name;
        return $this->_registry->getContainer($name);
    }

    /**
     * Retrieve the registry
     *
     * @return \Zend\View\Helper\Placeholder\Registry
     */
    public function getRegistry()
    {
        return $this->_registry;
    }
}
