<?php

namespace sagalaya\extensions\command\generator;

use Zend\Code\Generator\ClassGenerator;
use lithium\util\Inflector;

class Repository extends Generator {

	public function build($model) {
		$class = new ClassGenerator($model->config->name. 'Repository');
		$class->setExtendedClass('\Doctrine\ORM\EntityRepository');
		$class->setNamespaceName($this->namespace);

		return $class;
	}
}