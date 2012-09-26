<?php

namespace sagalaya\extensions\security;

/**
 * Authenticate using remote function
 * @author Mukhamad Ikhsan
 * `options` 	:
 * 		`auth`		:
 * 		`adapter`	:
 * 		`api` 		: Used classname
 * 		`function`	: called function on the classname
 */
class Remote extends \lithium\security\Auth {

	public static function check($name, $credentials = null, array $options = array()) {

		$defaults = array('checkSession' => true, 'writeSession' => true);
		$options += $defaults;
		$params = compact('name', 'credentials', 'options');

		return static::_filter(__FUNCTION__, $params, function($self, $params) {
			extract($params);

			$config = $self::invokeMethod('_config', array($name));

			if ($config === null) {
				throw new ConfigException("Configuration `{$name}` has not been defined.");
			}

			$session = $config['session'];
			$options += $config;

			if ($options['checkSession'] && !isset($credentials)) {
				if ($data = $session['class']::read($session['key'], $session['options'])) {
					return call_user_func(array($options['api'], $options['function']), $data);
				}
			}

			if (($credentials) && $data = $self::adapter($name)->check($credentials, $options)) {
				return ($options['writeSession']) ? $self::set($name, $data) : $data;
			}

			return false;
		});
	}

	public static function set($name, $data, array $options = array()) {
		$params = compact('name', 'data', 'options');

		return static::_filter(__FUNCTION__, $params, function($self, $params) {
			extract($params);
			$config = $self::invokeMethod('_config', array($name));
			$session = $config['session'];

			if ($credential = $self::adapter($name)->set($data, $options)) {
				$session['class']::write($session['key'], $credential, $options + $session['options']);
				return $data;
			}

			return false;
		});
	}


	public static function clear($name, array $options = array()) {
		$defaults = array('clearSession' => true);
		$options += $defaults;

		return static::_filter(__FUNCTION__, compact('name', 'options'), function($self, $params) {
			extract($params);
			$config = $self::invokeMethod('_config', array($name));
			$session = $config['session'];

			if ($options['clearSession']) {
				$session['class']::delete($session['key'], $session['options']);
			}
			$self::adapter($name)->clear($options);
		});
	}
}