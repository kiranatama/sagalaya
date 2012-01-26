<?php

namespace app\extensions\helper;

use app\extensions\util\DomBuilder;

class Paginate extends \lithium\template\Helper {
	
	public $options = array(
		'class' => 'pagination'
	);
	
	public function buildOptions($options) {
		foreach ($options as $key => $value) {
			$this->options[$key] = $value;
		}
	}

	public function pagination($total, $args = array(), $options = array()) {
		
		$container = new DomBuilder('ul', array('class' => $this->options['class']));

		//@TODO: implement pagination
		
		return $container->render();
	}
	
}
