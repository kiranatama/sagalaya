<?php

namespace sagalaya\extensions\command\generator;

use Zend\Code\Generator\DocblockGenerator;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\MethodGenerator;
use lithium\util\Inflector;

class Controller extends Generator {	
	
	public function build($model) {
		
		$class = new ClassGenerator(Inflector::pluralize("{$model->config->name}") . 'Controller');
		$class->setExtendedClass('\lithium\action\Controller');		
		$class->setNamespaceName($this->namespace);

		$directory = LITHIUM_APP_PATH . '/views/' . strtolower(Inflector::pluralize("{$model->config->name}"));
		$view_directory = LITHIUM_APP_PATH . '/views/' . strtolower(Inflector::pluralize("{$model->config->name}"));
		if (!file_exists($directory)) {
			mkdir($directory);
		}

		$publicActions = new PropertyGenerator('publicActions', array(), PropertyGenerator::FLAG_PUBLIC);
		$docBlock = new DocblockGenerator('Array of function excluded from auth (has public access)');
		$publicActions->setDocBlock($docBlock);
		$class->addPropertyFromGenerator($publicActions);

		$messageMethod = new MethodGenerator('_message');
		$messageMethod->setParameter('value');
		$messageMethod->setBody("\lithium\storage\Session::write('message', \$value);");
		$class->addMethodFromGenerator($messageMethod);

		foreach (array('index', 'create', 'edit', 'view', 'delete') as $action) {
			$method = new MethodGenerator($action);

			$single = strtolower("{$model->config->name}");
			$plurals = Inflector::pluralize($single);

			if (!file_exists($view_directory . '/' . $action . '.html.twig')) {
				file_put_contents($view_directory . '/' . $action . '.html.twig', '');
			}

			switch ($action) {
				case 'index' :
					$body = "\${$plurals} = {$model->config->name}::findAll();\n\n";
					$body .= "return compact('{$plurals}');";
					$method->setBody($body);
					break;
				case 'create' :
					$body = "if (\$this->request->data) {\n\n";
					$body .= "\t\${$single} = new {$model->config->name}(\$this->request->data);\n\n";
					$body .= "\tif(\${$single}->save()) {\n";
					$body .= "\t\t\$this->_message('Successfully to create {$model->config->name}');\n";
					$body .= "\t\t\$this->redirect('{$model->config->name}s::index');\n";
					$body .= "\t} else {\n";
					$body .= "\t\t\$this->_message('Failed to create {$model->config->name}, please check the error');\n";
					$body .= "\t\t\$errors = \${$single}->getErrors();\n";
					$body .= "\t}\n\n";
					$body .= "}\n\n";
					$body .= "return compact('{$single}', 'errors');";
					$method->setBody($body);
					break;
				case 'edit' :
					$body = "if (\$this->request->id) {\n\n";
					$body .= "\t\${$single} = {$model->config->name}::get(\$this->request->id);\n";
					$body .= "\t\${$single}->properties = \$this->request->data;\n\n";
					$body .= "\tif(\${$single}->save()) {\n";
					$body .= "\t\t\$this->_message('Successfully to update {$model->config->name}');\n";
					$body .= "\t\t\$this->redirect('{$model->config->name}s::index');\n";
					$body .= "\t} else {\n";
					$body .= "\t\t\$this->_message('Failed to update {$model->config->name}, please check the error');\n";
					$body .= "\t\t\$errors = \${$single}->getErrors();\n";
					$body .= "\t}\n\n";
					$body .= "}\n\n";
					$body .= "return compact('{$single}', 'errors');";
					$method->setBody($body);
					break;
				case 'view' :
					$body = "if (\$this->request->id) {\n";
					$body .= "\t\${$single} = {$model->config->name}::get(\$this->request->id);\n";
					$body .= "}\n\n";
					$body .= "return compact('{$single}');";
					$method->setBody($body);
					break;
				case 'delete' :
					$body = "if (\$this->request->id) {\n";
					$body .= "\t\${$single} = {$model->config->name}::get(\$this->request->id);\n";
					$body .= "\t\${$single}->delete();\n";
					$body .= "\t\$this->_message('Success to delete {$model->config->name}');\n";
					$body .= "\t\$this->redirect('{$model->config->name}s::index');\n";
					$body .= "\treturn true;\n";
					$body .= "}\n\n";
					$body .= "\$this->_message('{$model->config->name} id cannot be empty');\n";
					$body .= "\$this->redirect(\$this->request->referer());\n";
					$body .= "return false;";
					$method->setBody($body);
					break;
			}
			$class->addMethodFromGenerator($method);
		}

		if (isset($model->actions)) {
			foreach ($model->actions->action as $action) {
				$method = new MethodGenerator("{$action->name}");
				$method->setBody("{$action->code}");
				if (!file_exists($view_directory . '/' . $action . '.html.twig')) {
					file_put_contents($view_directory . '/' . $action . '.html.twig', '');
				}
				$class->addMethodFromGenerator($method);
			}
		}

		return $class;
	}
	
	public function createView($filename) {
		
	}
}