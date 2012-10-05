<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Amf
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\Amf\Response;

use Zend\Amf\Parser,
    Zend\Amf\Value;

/**
 * Handles converting the PHP object ready for response back into AMF
 *
 * @package    Zend_Amf
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface ResponseInterface
{
    /**
     * Instantiate new output stream and start serialization
     *
     * @return ResponseInterface
     */
    public function finalize();

    /**
     * Serialize the PHP data types back into Actionscript and
     * create and AMF stream.
     *
     * @param  Parser\OutputStream $stream
     * @return ResponseInterface
     */
    public function writeMessage(Parser\OutputStream $stream);

    /**
     * Return the output stream content
     *
     * @return string The contents of the output stream
     */
    public function getResponse();

    /**
     * Return the output stream content
     *
     * @return string
     */
    public function __toString();

    /**
     * Add an AMF body to be sent to the Flash Player
     *
     * @param  Value\MessageBody $body
     * @return ResponseInterface
     */
    public function addAmfBody(Value\MessageBody $body);

    /**
     * Return an array of AMF bodies to be serialized
     *
     * @return array
     */
    public function getAmfBodies();

    /**
     * Add an AMF Header to be sent back to the flash player
     *
     * @param  Value\MessageHeader $header
     * @return ResponseInterface
     */
    public function addAmfHeader(Value\MessageHeader $header);

    /**
     * Retrieve attached AMF message headers
     *
     * @return array Array of \Zend\Amf\Value\MessageHeader objects
     */
    public function getAmfHeaders();

    /**
     * Set the AMF encoding that will be used for serialization
     *
     * @param  int $encoding
     * @return ResponseInterface
     */
    public function setObjectEncoding($encoding);
}
