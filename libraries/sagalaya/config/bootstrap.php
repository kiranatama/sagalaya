<?php

require __DIR__ . '/doctrine.php';
require __DIR__ . '/twig.php';
require __DIR__ . '/action.php';

// Load controllers subdirectories
use \lithium\core\Libraries;
use \lithium\core\ConfigException;

$directory = LITHIUM_APP_PATH . '/controllers';
$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
$paths = array();
while ($iterator->valid()) {
	if (!$iterator->isDot()) {
		$value = $iterator->getSubPath();
		if (!empty($value)) {
			$paths[] = str_replace('/', '\\', $value);
		}
	}
	$iterator->next();
}

foreach ($paths as $path) {
	try {
		Libraries::add($path, array('bootstrap' => false, 'path' => LITHIUM_APP_PATH . '/controllers' . $path));
	} catch (ConfigException $e) {
		echo $e->getMessage();
	}	
}

?>