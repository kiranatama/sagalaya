<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Form
 */

namespace Zend\Form\View\Helper;

use Traversable;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;

/**
 * @category   Zend
 * @package    Zend_Form
 * @subpackage View
 */
class FormCheckbox extends FormInput
{
    /**
     * Render a form <input> element from the provided $element
     *
     * @param  ElementInterface $element
     * @throws \Zend\Form\Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $name = $element->getName();
        if (empty($name)) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $attributes            = $element->getAttributes();
        $attributes['name']    = $name;
        $attributes['checked'] = '';
        $attributes['type']    = $this->getInputType();
        $closingBracket        = $this->getInlineClosingBracket();

        if (isset($attributes['value']) && $attributes['value'] == $element->getCheckedValue()) {
            $attributes['checked'] = 'checked';
        }
        $attributes['value'] = $element->getCheckedValue();

        $rendered = sprintf(
            '<input %s%s',
            $this->createAttributesString($attributes),
            $closingBracket
        );

        $useHiddenElement = $element->useHiddenElement();

        if ($useHiddenElement) {
            $hiddenAttributes = array(
                'name'  => $attributes['name'],
                'value' => $element->getUncheckedValue()
            );

            $rendered = sprintf(
                '<input type="hidden" %s%s',
                $this->createAttributesString($hiddenAttributes),
                $closingBracket
            ) . $rendered;
        }

        return $rendered;
    }

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface $element
     * @return string|FormCheckbox
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Return input type
     *
     * @return string
     */
    protected function getInputType()
    {
        return 'checkbox';
    }
}
