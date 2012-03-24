<?php

namespace sagalaya\extensions\command\generator;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\DocblockGenerator;
use Zend\Code\Generator\MethodGenerator;
use lithium\util\Inflector;

class Model extends AbstractGenerator {
	
	public $interfaces = array();

	public function build($model) {
		$class = new ClassGenerator($model->config->name);
		$class->setExtendedClass('\sagalaya\extensions\data\Model');
		$class->setNamespaceName('app\models');

		if (isset($model->config->resource)) {
			$this->interfaces[] = '\\Zend\\Acl\\Resource';		
			$resourceId = new MethodGenerator('getResourceId');
			$resourceId->setBody("return \$this->id;");
			$class->setMethod($resourceId);
		}

		if (isset($model->config->role)) {
			$this->interfaces[] = '\\Zend\\Acl\\Role';			
			$roleId = new MethodGenerator('getRoleId');
			$roleId->setBody("return \$this->id;");
			$class->setMethod($roleId);
		}

		$repository = ("{$model->config->repository}"=="true")?
		"(repositoryClass=\"app\\resources\\repository\\{$model->config->name}Repository\")\n":null;
		$callback = ("{$model->config->callback}"=="true")?"@HasLifecycleCallbacks\n":null;
		$table = "@Table(name=\"{$model->config->table}\")";
		$docblock = new DocblockGenerator("@Entity" . $repository . $callback . $table);
		$class->setDocblock($docblock);
		$validations = array();

		foreach ($model->fields->field as $field) {

			$type = "{$field->type}";
			$attributes = null;
			$attributes .= (isset($field->nullable))?", nullable={$field->nullable}":null;
			$attributes .= (isset($field->length))?", length={$field->length}":null;
			$attributes .= (isset($field->precision))?", precision={$field->precision}":null;
			$attributes .= (isset($field->scale))?", scale={$field->scale}":null;
			$attributes .= (isset($field->unique))?", unique={$field->unique}":null;

			$default = isset($field->default)?"$field->default":null;
			if ($type == "boolean" && $default) {
				$default = ($default == "true")?true:false;
			}

			$property = new PropertyGenerator("{$field->name}", $default, PropertyGenerator::FLAG_PROTECTED);
				
			if ($field->name == "password") {
				$setPassword = new MethodGenerator('setPassword');
				$setPassword->setParameter("password");
				$setPassword->setBody("\$this->password = \\lithium\\util\\String::hash(\$password);");
				$class->setMethod($setPassword);
			}
				
			switch ($type) {
				case "index" :
					if (isset($field->strategy)) {
						switch ("{$field->strategy}") {
							case "uuid" :
								$docblock = '@Id @Column(type="string", length=36) @GeneratedValue(strategy="UUID")';
								break;
						}
					} else {
						$docblock = '@Id @Column(type="integer") @GeneratedValue';
					}
					break;
				case "relation" :
					$relation = "{$field->relation}";
					if ($relation == "OneToMany" || $relation == "ManyToMany") {

					}
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
					$docblock = "@Column(type=\"{$type}\"{$attributes})";
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

			$beforePersist->setBody("\$this->created = new \DateTime();\n\$this->modified= new \DateTime();");
			$beforeUpdate->setBody("\$this->modified = new \DateTime();");

			$class->setMethods(array($beforePersist, $beforeUpdate));
		}

		$class->setImplementedInterfaces($this->interfaces);
		return $class;
	}

}