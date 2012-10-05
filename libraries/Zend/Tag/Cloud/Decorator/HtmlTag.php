<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Tag
 */

namespace Zend\Tag\Cloud\Decorator;

use Zend\Tag\Cloud\Decorator\Exception\InvalidArgumentException;
use Zend\Tag\ItemList;

/**
 * Simple HTML decorator for tags
 *
 * @category  Zend
 * @package   Zend_Tag
 */
class HtmlTag extends AbstractTag
{
    /**
     * List of tags which get assigned to the inner element instead of
     * font-sizes.
     *
     * @var array
     */
    protected $_classList = null;

    /**
     * @var string Encoding to utilize
     */
    protected $_encoding = 'UTF-8';

    /**
     * Unit for the fontsize
     *
     * @var string
     */
    protected $_fontSizeUnit = 'px';

    /**
     * Allowed fontsize units
     *
     * @var array
     */
    protected $_alloweFontSizeUnits = array('em', 'ex', 'px', 'in', 'cm', 'mm', 'pt', 'pc', '%');

    /**
     * List of HTML tags
     *
     * @var array
     */
    protected $_htmlTags = array(
        'li'
    );

    /**
     * Maximum fontsize
     *
     * @var integer
     */
    protected $_maxFontSize = 20;

    /**
     * Minimum fontsize
     *
     * @var integer
     */
    protected $_minFontSize = 10;

    /**
     * Set a list of classes to use instead of fontsizes
     *
     * @param  array $classList
     * @throws InvalidArgumentException When the classlist is empty
     * @throws InvalidArgumentException When the classlist contains an invalid classname
     * @return HTMLTag
     */
    public function setClassList(array $classList = null)
    {
        if (is_array($classList)) {
            if (count($classList) === 0) {
                throw new InvalidArgumentException('Classlist is empty');
            }

            foreach ($classList as $class) {
                if (!is_string($class)) {
                    throw new InvalidArgumentException('Classlist contains an invalid classname');
                }
            }
        }

        $this->_classList = $classList;
        return $this;
    }

    /**
     * Get class list
     *
     * @return array
     */
    public function getClassList()
    {
        return $this->_classList;
    }

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
         return $this->_encoding;
    }

    /**
     * Set encoding
     *
     * @param  string $value
     * @return HTMLTag
     */
    public function setEncoding($value)
    {
        $this->_encoding = (string) $value;
        return $this;
    }

    /**
     * Set the font size unit
     *
     * Possible values are: em, ex, px, in, cm, mm, pt, pc and %
     *
     * @param  string $fontSizeUnit
     * @throws InvalidArgumentException When an invalid fontsize unit is specified
     * @return HTMLTag
     */
    public function setFontSizeUnit($fontSizeUnit)
    {
        if (!in_array($fontSizeUnit, $this->_alloweFontSizeUnits)) {
            throw new InvalidArgumentException('Invalid fontsize unit specified');
        }

        $this->_fontSizeUnit = (string) $fontSizeUnit;
        $this->setClassList(null);
        return $this;
    }

    /**
     * Retrieve font size unit
     *
     * @return string
     */
    public function getFontSizeUnit()
    {
        return $this->_fontSizeUnit;
    }
     /**
     * Set the HTML tags surrounding the <a> element
     *
     * @param  array $htmlTags
     * @return HTMLTag
     */
    public function setHTMLTags(array $htmlTags)
    {
        $this->_htmlTags = $htmlTags;
        return $this;
    }

    /**
     * Get HTML tags map
     *
     * @return array
     */
    public function getHTMLTags()
    {
        return $this->_htmlTags;
    }

    /**
     * Set maximum font size
     *
     * @param  integer $maxFontSize
     * @throws InvalidArgumentException When fontsize is not numeric
     * @return HTMLTag
     */
    public function setMaxFontSize($maxFontSize)
    {
        if (!is_numeric($maxFontSize)) {
            throw new InvalidArgumentException('Fontsize must be numeric');
        }

        $this->_maxFontSize = (int) $maxFontSize;
        $this->setClassList(null);
        return $this;
    }

    /**
     * Retrieve maximum font size
     *
     * @return int
     */
    public function getMaxFontSize()
    {
        return $this->_maxFontSize;
    }

    /**
     * Set minimum font size
     *
     * @param  int $minFontSize
     * @throws InvalidArgumentException When fontsize is not numeric
     * @return HTMLTag
     */
    public function setMinFontSize($minFontSize)
    {
        if (!is_numeric($minFontSize)) {
            throw new InvalidArgumentException('Fontsize must be numeric');
        }

        $this->_minFontSize = (int) $minFontSize;
        $this->setClassList(null);
        return $this;
    }

    /**
     * Retrieve minimum font size
     *
     * @return int
     */
    public function getMinFontSize()
    {
        return $this->_minFontSize;
    }

    /**
     * Defined by Tag
     *
     * @param  ItemList $tags
     * @throws InvalidArgumentException
     * @return array
     */
    public function render($tags)
    {
        if (!$tags instanceof ItemList) {
            throw new InvalidArgumentException(sprintf(
                'HtmlTag::render() expects a Zend\Tag\ItemList argument; received "%s"',
                (is_object($tags) ? get_class($tags) : gettype($tags))
            ));
        }
        if (null === ($weightValues = $this->getClassList())) {
            $weightValues = range($this->getMinFontSize(), $this->getMaxFontSize());
        }

        $tags->spreadWeightValues($weightValues);

        $result = array();

        $enc = $this->getEncoding();
        foreach ($tags as $tag) {
            if (null === ($classList = $this->getClassList())) {
                $attribute = sprintf('style="font-size: %d%s;"', $tag->getParam('weightValue'), $this->getFontSizeUnit());
            } else {
                $attribute = sprintf('class="%s"', htmlspecialchars($tag->getParam('weightValue'), ENT_COMPAT, $enc));
            }

            $tagHTML = sprintf('<a href="%s" %s>%s</a>', htmlSpecialChars($tag->getParam('url'), ENT_COMPAT, $enc), $attribute, $tag->getTitle());

            foreach ($this->getHTMLTags() as $key => $data) {
                if (is_array($data)) {
                    $htmlTag    = $key;
                    $attributes = '';

                    foreach ($data as $param => $value) {
                        $attributes .= ' ' . $param . '="' . htmlspecialchars($value, ENT_COMPAT, $enc) . '"';
                    }
                } else {
                    $htmlTag    = $data;
                    $attributes = '';
                }

                $tagHTML = sprintf('<%1$s%3$s>%2$s</%1$s>', $htmlTag, $tagHTML, $attributes);
            }

            $result[] = $tagHTML;
        }

        return $result;
    }
}
