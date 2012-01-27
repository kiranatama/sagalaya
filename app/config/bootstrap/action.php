<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * This file contains a series of method filters that allow you to intercept different parts of
 * Lithium's dispatch cycle. The filters below are used for on-demand loading of routing
 * configuration, and automatically configuring the correct environment in which the application
 * runs.
 *
 * For more information on in the filters system, see `lithium\util\collection\Filters`.
 *
 * @see lithium\util\collection\Filters
 */

use lithium\core\Libraries;
use lithium\net\http\Router;
use lithium\core\Environment;
use lithium\action\Dispatcher;
use lithium\security\Auth;
use lithium\storage\Session;
use lithium\action\Response;

/**
 * This filter intercepts the `run()` method of the `Dispatcher`, and first passes the `'request'`
 * parameter (an instance of the `Request` object) to the `Environment` class to detect which
 * environment the application is running in. Then, loads all application routes in all plugins,
 * loading the default application routes last.
 *
 * Change this code if plugin routes must be loaded in a specific order (i.e. not the same order as
 * the plugins are added in your bootstrap configuration), or if application routes must be loaded
 * first (in which case the default catch-all routes should be removed).
 *
 * If `Dispatcher::run()` is called multiple times in the course of a single request, change the
 * `include`s to `include_once`.
 *
 * @see lithium\action\Request
 * @see lithium\core\Environment
 * @see lithium\net\http\Router
 */
Dispatcher::applyFilter('run', function($self, $params, $chain) {
	Environment::set($params['request']);

	foreach (array_reverse(Libraries::get()) as $name => $config) {
		if ($name === 'lithium') {
			continue;
		}
		$file = "{$config['path']}/config/routes.php";
		file_exists($file) ? include $file : null;
	}
	return $chain->next($self, $params, $chain);
});

Dispatcher::applyFilter('_callable', function($self, $params, $chain) {

	$ctrl = $chain->next($self, $params, $chain);
	$request = isset($params['request']) ? $params['request'] : null;
	$action = $params['params']['action'];

	if ($request->args) {
		$arguments = array();
		foreach ($request->args as $value) {
			$param = explode(":", $value);
			$arguments[$param[0]] = (isset($param[1]))?$param[1]:null;			
		}
		$request->args = $arguments;
	}	

	if (Auth::check('default') || preg_match('|test.*|', $request->url)) {
		return $ctrl;
	}

	if (isset($ctrl->publicActions) && in_array($action, $ctrl->publicActions)) {
		return $ctrl;
	}	

	return function() use ($request) {
		Session::write('message', 'You need to login to access that page.');
		return new Response(compact('request') + array('location' => 'Sessions::add'));
	};
});

?>