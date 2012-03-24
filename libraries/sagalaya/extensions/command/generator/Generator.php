<?php

namespace sagalaya\extensions\command\generator;

use lithium\util\Inflector;

/**
 * 
 * @author Mukhamad Ikhsan
 *
 */
abstract class Generator {

	public static $generateAll = false;
	public $base, $path, $class, $name, $app, $namespace;

	/**
	 * 
	 * @param unknown_type $xml
	 */
	public function __construct($xml) {
				
		$this->class = $this->build($xml);
		$className = substr(get_class($this), strrpos(get_class($this),'\\') + 1);
		$this->app = substr(LITHIUM_APP_PATH, strripos(LITHIUM_APP_PATH, '/') + 1);		
				
		switch ($className) {
			case 'Model' :
				$this->base = '/models';
				$this->name = "{$xml->config->name}";				
				break;
			case 'Controller' :
				$this->base = '/controllers';
				$this->name = Inflector::pluralize("{$xml->config->name}") . "Controller";
				break;
			case 'ModelTest' :
				$this->base = '/tests/cases/models';
				$this->name = "{$xml->config->name}Test";
				break;
			case 'ControllerTest' :
				$this->base = '/tests/cases/controllers';
				$this->name = Inflector::pluralize("{$xml->config->name}") . "ControllerTest";
				break;
			case 'Repository' :
				$this->base = '/resources/repository';
				$this->name = "{$xml->config->name}Repository";
				break;
		}
		
		$this->namespace = $this->app . str_replace('/', '\\', $this->base);
		$this->path = LITHIUM_APP_PATH . $this->base . "/{$this->name}.php";
	}

	/**
	 * Every child class must implement this function
	 * @param unknown_type $xml
	 */
	public function build($xml) {
	}
	
	public function generate() {		
		return $this->class->generate();
	}
}