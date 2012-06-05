<?php

namespace sagalaya\extensions\security;

/**
 * Authentication using Doctrine ORM object
 * @author Mukhamad Ikhsan
 * `options` 
 * 		`auth`			:
 * 		`adapter`		:
 * 		`model`			:
 * 		`fields`		:
 * 		`filters`		:
 * 		`query`			:
 * 		`sessionHolder`	:
 * 		`validator`		:
 */
class Doctrine extends \lithium\security\Auth {
	
	/**
	 * 
	 * @param string $name
	 * @param array $credentials
	 * @param array $options
	 * @throws ConfigException
	 */
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
			
			if ($options['checkSession']) {
				if ($data = $session['class']::read($session['key'], $session['options'])) {
					return $config['model']::findOneById($data);
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
	
			if ($data = $self::adapter($name)->set($data, $options)) {
				$session['class']::write($session['key'], $data->id, $options + $session['options']);
				return $data;
			}
			return false;
		});
	}
}
?>