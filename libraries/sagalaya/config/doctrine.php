<?php 
use \lithium\core\Libraries;
use \lithium\core\ConfigException;
use Doctrine\DBAL\Types\Type;

// Adding Doctrine libraries
$libraries = array('Doctrine');

// Adding Symfony libraries, sagalaya dependent only
$libraries = array_merge($libraries, array('Symfony'));

// Adding Zend libraries, sagalaya dependent only
$libraries = array_merge($libraries, array('Zend'));

foreach ($libraries as $name) {
	if (!Libraries::get($name)) {
		try {
			Libraries::add($name, array('bootstrap' => false));
		} catch (ConfigException $e) {
			continue;
		}
	}
}

// Custom type for doctrine
Type::addType('money', 'sagalaya\extensions\doctrine\type\MoneyType');

?>