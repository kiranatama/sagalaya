<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Ldap
 */

namespace Zend\Ldap\Collection;

use Zend\Ldap;
use Zend\Ldap\Exception;

/**
 * Zend\Ldap\Collection\DefaultIterator is the default collection iterator implementation
 * using ext/ldap
 *
 * @category   Zend
 * @package    Zend_Ldap
 */
class DefaultIterator implements \Iterator, \Countable
{
    const ATTRIBUTE_TO_LOWER = 1;
    const ATTRIBUTE_TO_UPPER = 2;
    const ATTRIBUTE_NATIVE   = 3;

    /**
     * LDAP Connection
     *
     * @var \Zend\Ldap\Ldap
     */
    protected $ldap = null;

    /**
     * Result identifier resource
     *
     * @var resource
     */
    protected $resultId = null;

    /**
     * Current result entry identifier
     *
     * @var resource
     */
    protected $current = null;

    /**
     * Number of items in query result
     *
     * @var integer
     */
    protected $itemCount = -1;

    /**
     * The method that will be applied to the attribute's names.
     *
     * @var  integer|callback
     */
    protected $attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;

    /**
     * Constructor.
     *
     * @param  \Zend\Ldap\Ldap $ldap
     * @param  resource        $resultId
     * @throws \Zend\Ldap\Exception\LdapException if no entries was found.
     * @return DefaultIterator
     */
    public function __construct(Ldap\Ldap $ldap, $resultId)
    {
        $this->ldap      = $ldap;
        $this->resultId  = $resultId;
        $this->itemCount = @ldap_count_entries($ldap->getResource(), $resultId);
        if ($this->itemCount === false) {
            throw new Exception\LdapException($this->ldap, 'counting entries');
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the current result set
     *
     * @return bool
     */
    public function close()
    {
        $isClosed = false;
        if (is_resource($this->resultId)) {
            $isClosed       = @ldap_free_result($this->resultId);
            $this->resultId = null;
            $this->current  = null;
        }
        return $isClosed;
    }

    /**
     * Gets the current LDAP connection.
     *
     * @return \Zend\Ldap\Ldap
     */
    public function getLDAP()
    {
        return $this->ldap;
    }

    /**
     * Sets the attribute name treatment.
     *
     * Can either be one of the following constants
     * - Zend\Ldap\Collection\DefaultIterator::ATTRIBUTE_TO_LOWER
     * - Zend\Ldap\Collection\DefaultIterator::ATTRIBUTE_TO_UPPER
     * - Zend\Ldap\Collection\DefaultIterator::ATTRIBUTE_NATIVE
     * or a valid callback accepting the attribute's name as it's only
     * argument and returning the new attribute's name.
     *
     * @param  integer|callback $attributeNameTreatment
     * @return DefaultIterator Provides a fluent interface
     */
    public function setAttributeNameTreatment($attributeNameTreatment)
    {
        if (is_callable($attributeNameTreatment)) {
            if (is_string($attributeNameTreatment) && !function_exists($attributeNameTreatment)) {
                $this->attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
            } elseif (is_array($attributeNameTreatment)
                && !method_exists($attributeNameTreatment[0], $attributeNameTreatment[1])
            ) {
                $this->attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
            } else {
                $this->attributeNameTreatment = $attributeNameTreatment;
            }
        } else {
            $attributeNameTreatment = (int)$attributeNameTreatment;
            switch ($attributeNameTreatment) {
                case self::ATTRIBUTE_TO_LOWER:
                case self::ATTRIBUTE_TO_UPPER:
                case self::ATTRIBUTE_NATIVE:
                    $this->attributeNameTreatment = $attributeNameTreatment;
                    break;
                default:
                    $this->attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
                    break;
            }
        }

        return $this;
    }

    /**
     * Returns the currently set attribute name treatment
     *
     * @return integer|callback
     */
    public function getAttributeNameTreatment()
    {
        return $this->attributeNameTreatment;
    }

    /**
     * Returns the number of items in current result
     * Implements Countable
     *
     * @return int
     */
    public function count()
    {
        return $this->itemCount;
    }

    /**
     * Return the current result item
     * Implements Iterator
     *
     * @return array|null
     * @throws \Zend\Ldap\Exception\LdapException
     */
    public function current()
    {
        if (!is_resource($this->current)) {
            $this->rewind();
        }
        if (!is_resource($this->current)) {
            return null;
        }

        $entry          = array('dn' => $this->key());
        $ber_identifier = null;
        $name           = @ldap_first_attribute(
            $this->ldap->getResource(), $this->current,
            $ber_identifier
        );
        while ($name) {
            $data = @ldap_get_values_len($this->ldap->getResource(), $this->current, $name);
            unset($data['count']);

            switch ($this->attributeNameTreatment) {
                case self::ATTRIBUTE_TO_LOWER:
                    $attrName = strtolower($name);
                    break;
                case self::ATTRIBUTE_TO_UPPER:
                    $attrName = strtoupper($name);
                    break;
                case self::ATTRIBUTE_NATIVE:
                    $attrName = $name;
                    break;
                default:
                    $attrName = call_user_func($this->attributeNameTreatment, $name);
                    break;
            }
            $entry[$attrName] = $data;
            $name             = @ldap_next_attribute(
                $this->ldap->getResource(), $this->current,
                $ber_identifier
            );
        }
        ksort($entry, SORT_LOCALE_STRING);
        return $entry;
    }

    /**
     * Return the result item key
     * Implements Iterator
     *
     * @throws \Zend\Ldap\Exception\LdapException
     * @return string|null
     */
    public function key()
    {
        if (!is_resource($this->current)) {
            $this->rewind();
        }
        if (is_resource($this->current)) {
            $currentDn = @ldap_get_dn($this->ldap->getResource(), $this->current);
            if ($currentDn === false) {
                throw new Exception\LdapException($this->ldap, 'getting dn');
            }

            return $currentDn;
        } else {
            return null;
        }
    }

    /**
     * Move forward to next result item
     * Implements Iterator
     *
     * @throws \Zend\Ldap\Exception\LdapException
     * @return
     */
    public function next()
    {
        $code = 0;

        if (is_resource($this->current) && $this->itemCount > 0) {
            $this->current = @ldap_next_entry($this->ldap->getResource(), $this->current);
            if ($this->current === false) {
                $msg = $this->ldap->getLastError($code);
                if ($code === Exception\LdapException::LDAP_SIZELIMIT_EXCEEDED) {
                    // we have reached the size limit enforced by the server
                    return;
                } elseif ($code > Exception\LdapException::LDAP_SUCCESS) {
                    throw new Exception\LdapException($this->ldap, 'getting next entry (' . $msg . ')');
                }
            }
        } else {
            $this->current = false;
        }
    }

    /**
     * Rewind the Iterator to the first result item
     * Implements Iterator
     *
     *
     * @throws \Zend\Ldap\Exception\LdapException
     */
    public function rewind()
    {
        if (is_resource($this->resultId)) {
            $this->current = @ldap_first_entry($this->ldap->getResource(), $this->resultId);
            if ($this->current === false
                && $this->ldap->getLastErrorCode() > Exception\LdapException::LDAP_SUCCESS
            ) {
                throw new Exception\LdapException($this->ldap, 'getting first entry');
            }
        }
    }

    /**
     * Check if there is a current result item
     * after calls to rewind() or next()
     * Implements Iterator
     *
     * @return boolean
     */
    public function valid()
    {
        return (is_resource($this->current));
    }
}
