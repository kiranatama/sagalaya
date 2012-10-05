<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Feed
 */

namespace Zend\Feed\Writer\Renderer;

use DOMDocument;
use DOMElement;
use Zend\Feed\Writer;

/**
* @category Zend
* @package Zend_Feed_Writer
*/
class AbstractRenderer
{
    /**
     * Extensions
     * @var array
     */
    protected $_extensions = array();

    /**
     * @var mixed
     */
    protected $_container = null;

    /**
     * @var DOMDocument
     */
    protected $_dom = null;

    /**
     * @var bool
     */
    protected $_ignoreExceptions = false;

    /**
     * @var array
     */
    protected $_exceptions = array();

    /**
     * Encoding of all text values
     *
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $_type = null;

    /**
     * @var DOMElement
     */
    protected $_rootElement = null;

    /**
     * Constructor
     *
     * @param  mixed $container
     * @return void
     */
    public function __construct($container)
    {
        $this->_container = $container;
        $this->setType($container->getType());
        $this->_loadExtensions();
    }

    /**
     * Save XML to string
     *
     * @return string
     */
    public function saveXml()
    {
        return $this->getDomDocument()->saveXml();
    }

    /**
     * Get DOM document
     *
     * @return DOMDocument
     */
    public function getDomDocument()
    {
        return $this->_dom;
    }

    /**
     * Get document element from DOM
     *
     * @return DOMElement
     */
    public function getElement()
    {
        return $this->getDomDocument()->documentElement;
    }

    /**
     * Get data container of items being rendered
     *
     * @return mixed
     */
    public function getDataContainer()
    {
        return $this->_container;
    }

    /**
     * Set feed encoding
     *
     * @param  string $enc
     * @return AbstractRenderer
     */
    public function setEncoding($enc)
    {
        $this->_encoding = $enc;
        return $this;
    }

    /**
     * Get feed encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Indicate whether or not to ignore exceptions
     *
     * @param  bool $bool
     * @return AbstractRenderer
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function ignoreExceptions($bool = true)
    {
        if (!is_bool($bool)) {
            throw new Writer\Exception\InvalidArgumentException('Invalid parameter: $bool. Should be TRUE or FALSE (defaults to TRUE if null)');
        }
        $this->_ignoreExceptions = $bool;
        return $this;
    }

    /**
     * Get exception list
     *
     * @return array
     */
    public function getExceptions()
    {
        return $this->_exceptions;
    }

    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets the absolute root element for the XML feed being generated. This
     * helps simplify the appending of namespace declarations, but also ensures
     * namespaces are added to the root element - not scattered across the entire
     * XML file - may assist namespace unsafe parsers and looks pretty ;).
     *
     * @param DOMElement $root
     */
    public function setRootElement(DOMElement $root)
    {
        $this->_rootElement = $root;
    }

    /**
     * Retrieve the absolute root element for the XML feed being generated.
     *
     * @return DOMElement
     */
    public function getRootElement()
    {
        return $this->_rootElement;
    }

    /**
     * Load extensions from Zend_Feed_Writer
     *
     * @return void
     */
    protected function _loadExtensions()
    {
        Writer\Writer::registerCoreExtensions();
        $manager = Writer\Writer::getExtensionManager();
        $all = Writer\Writer::getExtensions();
        if (stripos(get_called_class(), 'entry')) {
            $exts = $all['entryRenderer'];
        } else {
            $exts = $all['feedRenderer'];
        }
        foreach ($exts as $extension) {
            $plugin = $manager->get($extension);
            $plugin->setDataContainer($this->getDataContainer());
            $plugin->setEncoding($this->getEncoding());
            $this->_extensions[$extension] = $plugin;
        }
    }
}
