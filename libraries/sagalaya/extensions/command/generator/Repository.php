<?php

namespace sagalaya\extensions\command\generator;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\DocblockGenerator;
use Zend\Code\Generator\MethodGenerator;
use lithium\util\Inflector;

class Repository extends AbstractGenerator {	
	
	public function build($model) {
		$class = new ClassGenerator($model->config->name. 'Repository');
		$class->setExtendedClass('\Doctrine\ORM\EntityRepository');
		$class->setNamespaceName('app\resources\repository');
	
		return $class;
	}
}