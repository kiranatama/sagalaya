<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use lithium\core\ErrorHandler;
use lithium\action\Response;
use lithium\net\http\Media;
use lithium\analysis\Debugger;
use lithium\analysis\Logger;

ErrorHandler::apply('lithium\action\Dispatcher::run', array(), function($info, $params) {
	
	$stack = Debugger::trace(array('format' => 'array', 'trace' => $info['exception']->getTrace()));
    $exception_class = get_class($info['exception']);
	
    array_unshift($stack, array(
        'functionRef' => '[exception]',
        'file' => $info['exception']->getFile(),
        'line' => $info['exception']->getLine()
    ));

    $response = new Response(array(
                'request' => $params['request'],
                'status' => $info['exception']->getCode()
            ));
    $url = $params['request']->url;
    $type = substr($url, strrpos($url, '.') + 1);    
	
    Media::render($response, compact('info', 'params', 'stack', 'exception_class'), array(
        'controller' => 'errors',
        'template' => ($info['exception']->getCode() == 404) ? "404" : "development",
        'layout' => 'error',
        'request' => $params['request'],
    	'type' => (in_array($type, array('json', 'html'))) ? $type : 'html'
    ));
        
    return $response;
});

/**
 * Then, set up a basic logging configuration that will write to a file.
 */
Logger::config(array('error' => array('adapter' => 'File')));

?>