<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Markup
 */

namespace Zend\Markup;

/**
 * @category   Zend
 * @package    Zend_Markup
 */
class Token
{
    const TYPE_NONE   = 'none';
    const TYPE_MARKUP = 'markup';

    /**
     * Children of this token
     *
     * @var \Zend\Markup\TokenList
     */
    protected $_children;

    /**
     * The content
     *
     * @var string
     */
    protected $_content;

    /**
     * The token's type
     *
     * @var string
     */
    protected $_type;

    /**
     * Token name
     *
     * @var string
     */
    protected $_name = '';

    /**
     * Token attributes
     *
     * @var array
     */
    protected $_attributes = array();

    /**
     * The used token stopper (empty when none is found)
     *
     * @var string
     */
    protected $_stopper = '';

    /**
     * The parent token
     *
     * @var \Zend\Markup\Token
     */
    protected $_parent;


    /**
     * Construct the token
     *
     * @param  string $content
     * @param  string $type
     * @param  string $name
     * @param  array $attributes
     * @param  \Zend\Markup\Token $parent
     */
    public function __construct(
        $content,
        $type,
        $name = '',
        array $attributes = array(),
        Token $parent = null
    ) {
        $this->_content    = $content;
        $this->_type       = $type;
        $this->_name       = $name;
        $this->_attributes = $attributes;
        $this->_parent     = $parent;
    }

    // accessors

    /**
     * Set the stopper
     *
     * @param string $stopper
     * @return \Zend\Markup\Token
     */
    public function setStopper($stopper)
    {
        $this->_stopper = $stopper;

        return $this;
    }

    /**
     * Get the stopper
     *
     * @return string
     */
    public function getStopper()
    {
        return $this->_stopper;
    }

    /**
     * Get the token's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get the token's type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get the token contents
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Get an attribute
     *
     * @param string $name
     *
     * @return string
     */
    public function getAttribute($name)
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
    }

    /**
     * Check if the token has an attribute
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->_attributes[$name]);
    }

    /**
     * Get all the attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * Add an attribute
     *
     * @param string $name
     * @param $value
     * @return Token
     */
    public function addAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;

        return $this;
    }

    /**
     * Check if an attribute is empty
     *
     * @param string $name
     *
     * @return bool
     */
    public function attributeIsEmpty($name)
    {
        return empty($this->_attributes[$name]);
    }

    // functions for child/parent tokens

    /**
     * Add a child token
     *
     * @param Token $child
     * @return void
     */
    public function addChild(Token $child)
    {
        $this->getChildren()->addChild($child);
    }

    /**
     * Set the children token list
     *
     * @param  \Zend\Markup\TokenList $children
     * @return \Zend\Markup\Token
     */
    public function setChildren(TokenList $children)
    {
        $this->_children = $children;
        return $this;
    }

    /**
     * Get the children for this token
     *
     * @return \Zend\Markup\TokenList
     */
    public function getChildren()
    {
        if (null === $this->_children) {
            $this->setChildren(new TokenList());
        }
        return $this->_children;
    }

    /**
     * Does this token have any children
     *
     * @return bool
     */
    public function hasChildren()
    {
        return !empty($this->_children);
    }

    /**
     * Get the parent token (if any)
     *
     * @return \Zend\Markup\Token
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Set a parent token
     *
     * @param  \Zend\Markup\Token $parent
     * @return \Zend\Markup\Token
     */
    public function setParent(Token $parent)
    {
        $this->_parent = $parent;
        return $this;
    }

    /**
     * Magic clone function
     *
     * @return void
     */
    public function __clone()
    {
        $this->_parent   = null;
        $this->_children = null;
        $this->_content  = '';
    }
}
