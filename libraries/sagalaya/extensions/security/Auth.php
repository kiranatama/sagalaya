<?php 

namespace sagalaya\extensions\security;

/**
 * Lithium Auth class not provide abstraction for implementation adapter class,
 * check, set, and clear function that is should be abstraction as interface 
 * but implemented as rigid convention  
 * 
 * @author Mukhamad Ikhsan
 * 
 */
class Auth {
	
	public static $auth = null;
	
	/**
	 * first called when application startup on bootstrap,
	 * initialize what adapter used for authentication
	 * @param array $options
	 */
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

?>