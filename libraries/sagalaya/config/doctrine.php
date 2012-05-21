<?php 
use \lithium\core\Libraries;
use \lithium\core\ConfigException;

$libraries = array('Doctrine\Common', 'Doctrine\DBAL',
		'Doctrine\ORM', 'Doctrine\DBAL\Migrations',
		'Symfony\Component\Yaml', 'Symfony\Component\Console', 
		'Zend\Code', 'Zend\Http', 'Zend\Rest');

foreach ($libraries as $name) {
	if (!Libraries::get($name)) {
		try {
			Libraries::add($name, array('bootstrap' => false));
		} catch (ConfigException $e) {
			continue;
		}
	}
}

?>