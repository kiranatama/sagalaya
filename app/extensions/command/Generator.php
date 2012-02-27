<?php

namespace app\extensions\command;

use Zend\Dom\Query;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\DocblockGenerator;
use Zend\Code\Generator\MethodGenerator;

/**
 * generator command provide automatic generator for CRUD like model, controller, and views
 * this is also generate the Test class for generated object
 * the central focus of generator is model, that will create controller, views, and test	
 */
class Generator extends \lithium\console\Command {
	
	public function run($args = array()) {
		$blueprint = opendir(LITHIUM_APP_PATH . '/config/design');
		while (($filename = readdir($blueprint)) !== false) {
			if (!is_dir($filename)) {				
				$this->process(LITHIUM_APP_PATH . '/config/design/' . $filename);		
			}
		}
	}
	
	public function process($filename) {
		
		echo "\nprocessing : $filename";
		echo "\n------------------------------------------------------------------\n\n";
				
		$xml = new \SimpleXMLElement(file_get_contents($filename));				

		$model = $this->buildModel($xml);
		$modelTest = $this->buildModelTest($xml);
		$controller = $this->buildController($xml);
		$controllerTest = $this->buildControllerTest($xml);
		$repository = $this->buildRepository($xml);		
		
		$modelPath = LITHIUM_APP_PATH . '/models/' . $model->getName() . '.php';
		$modelTestPath = LITHIUM_APP_PATH . '/tests/cases/models/' . $modelTest->getName() . '.php';
		$controllerPath = LITHIUM_APP_PATH . '/controllers/' . $controller->getName() . '.php';
		$controllerTestPath = LITHIUM_APP_PATH . '/tests/cases/controllers/' . $controllerTest->getName() . '.php';
		$repositoryPath = LITHIUM_APP_PATH . '/resources/repository/' . $repository->getName() . '.php';

		$generateAll = false;
		
		// Write model
		if (file_exists($modelPath)) {
			$result = $this->in("File {$modelPath} is already exists, overwrite the file?",
				array('choices' => array('Y' => 'Yes', 'N' => 'No', 'A' => 'All')));
			
			if (strcasecmp($result, "Yes") == 0 || strcasecmp($result, "All") == 0) {
				file_put_contents($modelPath, "<?php\n\n{$model->generate()}\n?>");
				print "{$modelPath} has created.\n";
			}
						
			if (strcasecmp($result, "All") == 0) {
				$generateAll = true;
			}
			
		} else {
			file_put_contents($modelPath, "<?php\n\n{$model->generate()}\n?>");
			print "{$modelPath} has created.\n";
		}
		
		// Write modelTest
		if (file_exists($modelTestPath) && !$generateAll) {
			$result = $this->in("File {$modelTestPath} is already exists, overwrite the file?",
			array('choices' => array('Y' => 'Yes', 'N' => 'No')));
			if (strcasecmp($result, "Yes") == 0) {
				file_put_contents($modelTestPath, "<?php\n\n{$modelTest->generate()}\n?>");
				print "{$modelPath} has created.\n";
			}
		} else {
			file_put_contents($modelTestPath, "<?php\n\n{$modelTest->generate()}\n?>");
			print "{$modelTestPath} has created.\n";
		}
				
		// Write controller
		if (file_exists($controllerPath) && !$generateAll) {
			$result = $this->in("File {$controllerPath} is already exists, overwrite the file?",
			array('choices' => array('Y' => 'Yes', 'N' => 'No')));			
			if (strcasecmp($result, "Yes") == 0) {
				file_put_contents($controllerPath, "<?php\n\n{$controller->generate()}\n?>");
				print "{$controllerPath} has created.\n";
			} 		
		} else {
			file_put_contents($controllerPath, "<?php\n\n{$controller->generate()}\n?>");
			print "{$controllerPath} has created.\n";
		}
		
		// Write controllerTest
		if (file_exists($controllerTestPath) && !$generateAll) {
			$result = $this->in("File {$controllerTestPath} is already exists, overwrite the file?",
			array('choices' => array('Y' => 'Yes', 'N' => 'No')));
			if (strcasecmp($result, "Yes") == 0) {
				file_put_contents($controllerTestPath, "<?php\n\n{$controllerTest->generate()}\n?>");
				print "{$controllerTestPath} has created.\n";
			}
		} else {
			file_put_contents($controllerTestPath, "<?php\n\n{$controllerTest->generate()}\n?>");
			print "{$controllerTestPath} has created.\n";
		}
		
		// Write repository
		if (file_exists($repositoryPath) && !$generateAll) {
			$result = $this->in("File {$repositoryPath} is already exists, overwrite the file?",
			array('choices' => array('Y' => 'Yes', 'N' => 'No')));
			if (strcasecmp($result, "Yes") == 0) {
				file_put_contents($repositoryPath, "<?php\n\n{$repository->generate()}\n?>");
				print "{$repositoryPath} has created.\n";
			}
		} else {
			file_put_contents($repositoryPath, "<?php\n\n{$repository->generate()}\n?>");
			print "{$repositoryPath} has created.\n";
		}
	
	}
	
	public function buildModel($model) {	
		$class = new ClassGenerator($model->config->name);
		$class->setExtendedClass('\app\extensions\data\Model');
		$class->setNamespaceName('app\models');
		
		$repository = ("{$model->config->repository}"=="true")?
						"(repositoryClass=\"app\\resources\\repository\\{$model->config->name}Repository\")\n":null;
		$callback = ("{$model->config->callback}"=="true")?"@HasLifecycleCallbacks\n":null;
		$table = "@Table(name=\"{$model->config->table}\")";
		$docblock = new DocblockGenerator("@Entity" . $repository . $callback . $table);
		$class->setDocblock($docblock);
		$validations = array();		
		
		foreach ($model->fields->field as $field) {							
			
			$type = "{$field->type}";
			$nullable = (isset($field->nullable))?", nullable={$field->nullable}":null;
			$length = (isset($field->length))?", length={$field->length}":null;
			$default = (isset($field->default))?"{$field->default}":null;		
			$precision = (isset($field->precision))?", precision={$field->precision}":null;
			$scale = (isset($field->scale))?", scale={$field->scale}":null;	
			
			if ($type == "boolean" && $default) {
				$default = ($default == "true")?true:false;
			}
							
			$property = new PropertyGenerator("{$field->name}", $default, PropertyGenerator::FLAG_PROTECTED);			
			switch ($type) {
				case "index" :
					$docblock = '@Id @Column(type="integer") @GeneratedValue';
					break;
				case "relation" :
					$mappedBy = (isset($field->mappedBy))?", mappedBy=\"{$field->mappedBy}\"":"";
					$inversedBy = (isset($field->inversedBy))?", inversedBy=\"{$field->inversedBy}\"":"";
					$cascades = '';
					if (isset($field->cascades->cascade[0])) {						
						$cascades = ", cascade={";
						foreach ($field->cascades->cascade as $cascade) {							
							$cascades .= "\"{$cascade}\",";							
						}
						$cascades = substr($cascades, 0, -1) . "}";
					}
					$docblock = "@{$field->relation}(targetEntity=\"{$field->targetEntity}\"{$mappedBy}{$inversedBy}{$cascades})";
					break;
				default :
					$docblock = "@Column(type=\"{$type}\"{$nullable}{$length}{$precision}{$scale})";
			}
			
			$property->setDocblock($docblock);
			$class->setProperty($property);
		}		
		
		if ($model->validations) {
			foreach ($model->validations->validation as $validation) {
				$validations["{$validation->field}"] = array();
				foreach ($validation->rules->rule as $rule) {
					$validations["{$validation->field}"][] = array("{$rule->type}", "message" => "{$rule->message}");
				}
			}
		}
		
		if (!empty($validations)) {
			$class->setProperty(new PropertyGenerator("validations", $validations, PropertyGenerator::FLAG_PROTECTED));
		}
		
		if ($callback) {
			$beforePersist = new MethodGenerator('beforePersist');
			$beforeUpdate = new MethodGenerator('beforeUpdate');
			
			$beforePersist->setDocblock("@PrePersist");
			$beforeUpdate->setDocblock("@PreUpdate");
			
			$beforePersist->setBody("\$this->created = new \DateTime();");
			$beforeUpdate->setBody("\$this->modified = new \DateTime();");
			
			$class->setMethods(array($beforePersist, $beforeUpdate));
		}
		
		return $class;
	}
	
	public function buildModelTest($model) {
		$class = new ClassGenerator($model->config->name . 'Test');
		$class->setExtendedClass('\lithium\test\Unit');
		$class->setNamespaceName('app\tests\cases\models');
		
		return $class;
	}
	
	public function buildController($model) {
		$class = new ClassGenerator($model->config->name . 'sController');
		$class->setExtendedClass('\lithium\action\Controller');
		$class->setNamespaceName('app\controllers');		
		
		$publicActions = new PropertyGenerator('publicActions', array(), PropertyGenerator::FLAG_PUBLIC);
		$class->setProperty($publicActions);
		
		$messageMethod = new MethodGenerator('message');
		$messageMethod->setParameter('value');
		$messageMethod->setBody("\lithium\storage\Session::write('message', \$value);");
		$class->setMethod($messageMethod);
		
		foreach (array('index', 'create', 'edit', 'view', 'delete') as $action) {
			$method = new MethodGenerator($action);
			
			$single = strtolower("{$model->config->name}");
			$plurals = $single . "s";

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
					$body .= "\t\t\$this->message('Successfully to create {$model->config->name}');\n";
					$body .= "\t\t\$this->redirect('{$model->config->name}s::index');\n";
					$body .= "\t} else {\n";
					$body .= "\t\t\$this->message('Failed to create {$model->config->name}, please check the error');\n";
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
					$body .= "\t\t\$this->message('Successfully to update {$model->config->name}');\n";
					$body .= "\t\t\$this->redirect('{$model->config->name}s::index');\n";
					$body .= "\t} else {\n";
					$body .= "\t\t\$this->message('Failed to update {$model->config->name}, please check the error');\n";
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
					$body .= "\t\$this->message('Success to delete {$model->config->name}');\n";
					$body .= "\t\$this->redirect('{$model->config->name}s::index');\n";
					$body .= "\treturn true;\n";
					$body .= "}\n\n";
					$body .= "\$this->message('{$model->config->name} id cannot be empty');\n";
					$body .= "\$this->redirect(\$this->request->referer());\n";
					$body .= "return false;";
					$method->setBody($body);
					break;
			}
			
			$class->setMethod($method);
		}
		
		return $class;
	}
	
	public function buildControllerTest($model) {
		$class = new ClassGenerator($model->config->name . 'sControllerTest');
		$class->setExtendedClass('\lithium\test\Unit');
		$class->setNamespaceName('app\tests\cases\controllers');
		
		return $class;
	}
	
	public function buildRepository($model) {
		$class = new ClassGenerator($model->config->name. 'Repository');
		$class->setExtendedClass('\Doctrine\ORM\EntityRepository');
		$class->setNamespaceName('app\resources\repository');
		
		return $class;
	}
}