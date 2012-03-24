<?php

namespace sagalaya\extensions\command\generator;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\DocblockGenerator;
use Zend\Code\Generator\MethodGenerator;
use lithium\util\Inflector;

class ControllerTest extends AbstractGenerator {
	
	public function build($model) {
		$class = new ClassGenerator(Inflector::pluralize("{$model->config->name}") . 'ControllerTest');
		$class->setExtendedClass('\lithium\test\Unit');
		$class->setNamespaceName('app\tests\cases\controllers');
	
		return $class;
	}
}