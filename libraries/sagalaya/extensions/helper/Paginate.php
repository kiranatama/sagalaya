<?php

namespace sagalaya\extensions\helper;

use sagalaya\extensions\util\DomBuilder;

/**
 * 
 * @author Mukhamad Ikhsan
 *
 */
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
?>