<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_View
 */

namespace Zend\View\Helper\Placeholder\Container;

use Zend\View\Exception;
use Zend\View\Helper\Placeholder\Registry;

/**
 * Base class for targetted placeholder helpers
 *
 * @package    Zend_View
 * @subpackage Helper
 */
abstract class AbstractStandalone
    extends \Zend\View\Helper\AbstractHelper
    implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @var \Zend\View\Helper\Placeholder\Container\AbstractContainer
     */
    protected $_container;

    /**
     * @var \Zend\View\Helper\Placeholder\Registry
     */
    protected $_registry;

    /**
     * Registry key under which container registers itself
     * @var string
     */
    protected $_regKey;

    /**
     * Flag wheter to automatically escape output, must also be
     * enforced in the child class if __toString/toString is overriden
     * @var book
     */
    protected $_autoEscape = true;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->setRegistry(Registry::getRegistry());
        $this->setContainer($this->getRegistry()->getContainer($this->_regKey));
    }

    /**
     * Retrieve registry
     *
     * @return \Zend\View\Helper\Placeholder\Registry
     */
    public function getRegistry()
    {
        return $this->_registry;
    }

    /**
     * Set registry object
     *
     * @param  \Zend\View\Helper\Placeholder\Registry $registry
     * @return \Zend\View\Helper\Placeholder\Container\AbstractStandalone
     */
    public function setRegistry(Registry $registry)
    {
        $this->_registry = $registry;
        return $this;
    }

    /**
     * Set whether or not auto escaping should be used
     *
     * @param  bool $autoEscape whether or not to auto escape output
     * @return \Zend\View\Helper\Placeholder\Container\AbstractStandalone
     */
    public function setAutoEscape($autoEscape = true)
    {
        $this->_autoEscape = ($autoEscape) ? true : false;
        return $this;
    }

    /**
     * Return whether autoEscaping is enabled or disabled
     *
     * return bool
     */
    public function getAutoEscape()
    {
        return $this->_autoEscape;
    }

    /**
     * Escape a string
     *
     * @param  string $string
     * @return string
     */
    protected function _escape($string)
    {
        $enc = 'UTF-8';
        if ($this->view instanceof \Zend\View\Renderer\RendererInterface
            && method_exists($this->view, 'getEncoding')
        ) {
            $enc = $this->view->getEncoding();
            $escaper = $this->view->plugin('escapeHtml');
            return $escaper((string) $string);
        }
        /**
         * bump this out to a protected method to kill the instance penalty!
         */
        $escaper = new \Zend\Escaper\Escaper($enc);
        return $escaper->escapeHtml((string) $string);
        /**
         * Replaced to ensure consistent escaping
         */
        //return htmlspecialchars((string) $string, ENT_COMPAT, $enc);
    }

    /**
     * Set container on which to operate
     *
     * @param  \Zend\View\Helper\Placeholder\Container\AbstractContainer $container
     * @return \Zend\View\Helper\Placeholder\Container\AbstractStandalone
     */
    public function setContainer(AbstractContainer $container)
    {
        $this->_container = $container;
        return $this;
    }

    /**
     * Retrieve placeholder container
     *
     * @return \Zend\View\Helper\Placeholder\Container\AbstractContainer
     */
    public function getContainer()
    {
        return $this->_container;
    }

    /**
     * Overloading: set property value
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $container = $this->getContainer();
        $container[$key] = $value;
    }

    /**
     * Overloading: retrieve property
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        $container = $this->getContainer();
        if (isset($container[$key])) {
            return $container[$key];
        }

        return null;
    }

    /**
     * Overloading: check if property is set
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        $container = $this->getContainer();
        return isset($container[$key]);
    }

    /**
     * Overloading: unset property
     *
     * @param  string $key
     * @return void
     */
    public function __unset($key)
    {
        $container = $this->getContainer();
        if (isset($container[$key])) {
            unset($container[$key]);
        }
    }

    /**
     * Overload
     *
     * Proxy to container methods
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Exception\BadMethodCallException
     */
    public function __call($method, $args)
    {
        $container = $this->getContainer();
        if (method_exists($container, $method)) {
            $return = call_user_func_array(array($container, $method), $args);
            if ($return === $container) {
                // If the container is returned, we really want the current object
                return $this;
            }
            return $return;
        }

        throw new Exception\BadMethodCallException('Method "' . $method . '" does not exist');
    }

    /**
     * String representation
     *
     * @return string
     */
    public function toString()
    {
        return $this->getContainer()->toString();
    }

    /**
     * Cast to string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Countable
     *
     * @return int
     */
    public function count()
    {
        $container = $this->getContainer();
        return count($container);
    }

    /**
     * ArrayAccess: offsetExists
     *
     * @param  string|int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->getContainer()->offsetExists($offset);
    }

    /**
     * ArrayAccess: offsetGet
     *
     * @param  string|int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getContainer()->offsetGet($offset);
    }

    /**
     * ArrayAccess: offsetSet
     *
     * @param  string|int $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        return $this->getContainer()->offsetSet($offset, $value);
    }

    /**
     * ArrayAccess: offsetUnset
     *
     * @param  string|int $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        return $this->getContainer()->offsetUnset($offset);
    }

    /**
     * IteratorAggregate: get Iterator
     *
     * @return Iterator
     */
    public function getIterator()
    {
        return $this->getContainer()->getIterator();
    }
}
