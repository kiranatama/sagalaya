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

    Media::render($response, compact('info', 'params', 'stack', 'exception_class'), array(
        'controller' => 'errors',
        'template' => 'development',
        'layout' => 'error',
        'request' => $params['request']
    ));
    return $response;
});

?>