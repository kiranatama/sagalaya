<?php 
use \lithium\core\Libraries;
use \lithium\core\ConfigException;

// Adding Doctrine libraries
$libraries = array('Doctrine\Common', 'Doctrine\DBAL',
		'Doctrine\ORM', 'Doctrine\DBAL\Migrations');

// Adding Symfony libraries, sagalaya dependent only
$libraries = array_merge($libraries, array(
		'Symfony\Component\Yaml', 'Symfony\Component\Console'));

// Adding Zend libraries, sagalaya dependent only
$libraries = array_merge($libraries, array('Zend\Code'));

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