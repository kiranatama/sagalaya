<?php

namespace app\extensions\command\generator;

use lithium\util\Inflector;

abstract class AbstractGenerator {

	public static $generateAll = false;
	public $base, $path, $class, $name;

	public function __construct($xml) {
		$this->shell = new \lithium\console\Command();
		$this->class = $this->build($xml);
		$className = substr(get_class($this), strrpos(get_class($this),'\\') + 1);		
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
		$this->path = LITHIUM_APP_PATH . $this->base . "/{$this->name}.php";
	}

	public function build($xml) {
	}
	
	public function generate() {
		return $this->class->generate();
	}
}