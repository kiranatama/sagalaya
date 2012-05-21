<?php

namespace sagalaya\extensions\util;

class Number {
	
	private $value;
	
	private function _getMaxPoint($compared) {
		
		$point = 0;
		
		if (strpos($this->__toString(), '.') !== false) {		
			$vPos = strlen($this->__toString()) - strpos($this->__toString(), '.');
			$point = $vPos;
		}
		
		if (strpos($compared, '.') !== false) {
			$vCom = strlen($compared) - strpos($compared, '.');
			$point = ($vCom > $point) ? $vCom : $point;
		}
		
		return $point;
	}
	
	public function __construct($number = '0') {
		$this->value = $number;
	}
	
	public function add($number) {
		$number = "$number";
		$this->value = bcadd($this->value, $number, $this->_getMaxPoint($number));
		return $this->__toString();	
	}
	
	public function sub($number) {
		$number = "$number";
		$this->value = bcsub($this->value, $number, $this->_getMaxPoint($number));
		return $this->__toString();
	}
	
	public function mul($number) {
		$number = "$number";
		$this->value = bcmul($this->value, $number, $this->_getMaxPoint($number) * 2);
		return $this->__toString();
	}
	
	public function div($number) {
		$number = "$number";
		$this->value = bcdiv($this->value, $number, 10);
		return $this->__toString();
	}
	
	public function __toString() {
		if (empty($this->value)) return '0';		
		$this->value = rtrim(rtrim($this->value, '0'),'.');		
		return $this->value;			
	}
}
