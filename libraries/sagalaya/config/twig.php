<?php
use \lithium\core\Libraries;
use \lithium\net\http\Media;

Media::type('default', null, array(
		'view' => '\lithium\template\View',
		'loader' => '\sagalaya\extensions\template\Loader',
		'renderer' => '\sagalaya\extensions\template\view\adapter\Twig',
		'paths' => array(
				'template' => '{:library}/views/{:controller}/{:template}.{:type}.twig',
				'layout' => '{:library}/views/layouts/{:layout}.{:type}.twig'
		)
));

Libraries::add('Twig', array(
		'path' => LITHIUM_LIBRARY_PATH . '/Twig',
		'prefix' => 'Twig_',
		'loader' => 'Twig_Autoloader::autoload',
));

require LITHIUM_LIBRARY_PATH . '/sagalaya/extensions/template/Loader.php';
require LITHIUM_LIBRARY_PATH . '/sagalaya/extensions/template/view/adapter/Twig.php';
require LITHIUM_LIBRARY_PATH . '/sagalaya/extensions/template/view/adapter/Template.php';

?>