<?php

use \lithium\core\Libraries;
use \lithium\net\http\Media;

/**
 * Add Twig to recognized media types.
 */
Media::type('default', null, array(
    'view' => '\lithium\template\View',
    'loader' => '\extensions\template\Loader',
    'renderer' => '\extensions\template\view\adapter\Twig',
    'paths' => array(
        'template' => '{:library}/views/{:controller}/{:template}.{:type}.twig',
        'layout' => '{:library}/views/layouts/{:layout}.{:type}.twig'
    )
));
?>
