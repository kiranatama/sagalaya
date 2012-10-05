<?php

namespace sagalaya\extensions\data;

/**
 *
 * @author Mukhamad Ikhsan
 *
 */
class ModelAnnotation {

	private static $cached, $filename;

	/**
	 *
	 */
	public static function init() {

		static::$filename = LITHIUM_APP_PATH . '/resources/tmp/cache/doc.cache';

		if (!file_exists(static::$filename)) {
			$handler = fopen(static::$filename, 'w+');
			fclose($handler);
		}

		$content = file_get_contents(static::$filename);
		static::$cached = (empty($content))?array():json_decode($content, true);
	}

	/**
	 *
	 * @param unknown_type $class
	 * @param unknown_type $field
	 * @param unknown_type $arg
	 */
	public static function get($class, $field, $arg = null) {

		static::init();
		if (is_object($class)) {
			$class = get_class($class);
		}

		if (isset(static::$cached[$class]) && isset(static::$cached[$class][$field])) {
			if ($arg == null) {
				return static::$cached[$class][$field]['message'];
			} else {
				preg_match_all('|' . $arg . '=["][^"]+["]|U', static::$cached[$class][$field]['message'], $out);
				preg_match_all('|".*"|U', $out[0][0], $result);
				$value = substr($result[0][0], 1, -1);
				$documentBlocks[$class][$field][$arg] = $value;
				file_put_contents(static::$filename, json_encode(static::$cached));
				return $value;
			}
		} else {
			$reflector = new \ReflectionProperty($class, $field);
			static::$cached[$class] = array($field => array('message' => $reflector->getDocComment()));
			file_put_contents(static::$filename, json_encode(static::$cached));
			return static::get($class, $field, $arg);
		}

	}

	/**
	 *
	 * @param \ReflectionProperty $property
	 * @param unknown_type $matches
	 */
	public static function match(\ReflectionProperty $property, $matches = array()) {
		$comment = static::get($property->getDeclaringClass()->getName(), $property->getName());
		foreach ($matches as $match) {
			if (preg_match("|{$match}|", $comment)) return true;
		}
		return false;
	}
}