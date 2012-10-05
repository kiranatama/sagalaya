<?php

namespace sagalaya\extensions\command\generator;

use Zend\Code\Generator\ClassGenerator;
use lithium\util\Inflector;

class ModelTest extends Generator {

	public function build($model) {
		$class = new ClassGenerator($model->config->name . 'Test');
		$class->setExtendedClass('\lithium\test\Unit');
		$class->setNamespaceName($this->namespace);

		return $class;
	}

}