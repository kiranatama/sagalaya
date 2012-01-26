<?php

namespace app\extensions\util;

/**
 * 
 * @author Mukhamad Ikhsan
 *
 */
class DomBuilder {
	
	protected $tag, $properties;
	public $content; 
	protected $parent, $childs;
	
	public function __construct($tag, $properties = array()) {
		$this->tag = $tag;
		$this->properties = $properties;
		$this->childs = array();				
	}
	
	/**
	 * 
	 * @param string $tag
	 * @param array $properties
	 */
	public function addChild($tag, $properties = array()) {
		if (is_object($tag)) {
			$child = $tag;
		} else {
			$child = new DomBuilder($tag, $properties);
		}
		$this->childs[] = $child;
		return $child;
	}
	
	/**
	 * 
	 * @param string $tag
	 */
	public function get($tag) {
		foreach ($this->childs as $child) {
			if ($child->tag == $tag) {
				return $child;
			}
		}
	}
	
	/**
	 * 
	 */
	public function render() {
		$properties = '';
		if (!empty($this->properties)) {
			foreach ($this->properties as $key => $value) {
				if (!empty($value)) {
					$properties .= "$key=\"$value\"";
				}
			}
		}		
		$render = "<{$this->tag} {$properties}>";
		if (isset($this->content)) {
			$render .= $this->content;
		}
		foreach ($this->childs as $child) {
			$render .= $child->render();
		}
		$render .= "</{$this->tag}>";
		return $render;	
	}

}