<?php

namespace app\extensions\command\generator;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\DocblockGenerator;
use Zend\Code\Generator\MethodGenerator;
use lithium\util\Inflector;

class ModelTest extends AbstractGenerator {	
	
	public function build($model) {
		$class = new ClassGenerator($model->config->name . 'Test');
		$class->setExtendedClass('\lithium\test\Unit');
		$class->setNamespaceName('app\tests\cases\models');
	
		return $class;
	}
	
}