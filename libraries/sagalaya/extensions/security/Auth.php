<?php

namespace sagalaya\extensions\security;

/**
 * 
 * @author Mukhamad Ikhsan
 *
 */
class Auth {
	
	public static $auth = null;
	
	public static function config($options) {		
		Auth::$auth = new $options['default']['auth']();
		call_user_func(array(Auth::$auth, 'config'), $options);		
	}
	
	public static function check($name, $credentials = null, array $options = array()) {		
		return call_user_func(array(Auth::$auth, 'check'), $name, $credentials, $options);
	} 
	
	public static function set($name, $data, array $options = array()) {
		return call_user_func(array(Auth::$auth, 'set'), $name, $data, $options);
	}
	
	public static function clear($name) {
		return call_user_func(array(Auth::$auth, 'clear'), $name);
	}
	 
}