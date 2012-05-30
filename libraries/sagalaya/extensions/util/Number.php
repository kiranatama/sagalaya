<?php

namespace sagalaya\extensions\util;

/**
 * This class is used for big number and high precision to compute,
 * relying on default data type in PHP seem unreliable, 
 * if your variable is higher than 32bit int, PHP will automatically convert to double/float.
 * 
 * The conversion make the variable is useless to compared, 
 * because when we compare this variable, PHP comparison will convert the value to string,
 * or to other unexpected behaviour.
 * 
 * So you need a reliable object to hold and operation with big number.
 */
class Number {
	
	/**
	 * this is used when div operation involved, 
	 * can't predict the point resulted by div operation	 
	 */
	const MAX_POINT_LENGTH = 10;
	
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
		$this->value = "$number";
	}
	
	public function add($number) {
		$number = "$number";
		$this->value = bcadd($this->value, $number, $this->_getMaxPoint($number));
		return clone $this;	
	}
	
	public function sub($number) {
		$number = "$number";
		$this->value = bcsub($this->value, $number, $this->_getMaxPoint($number));
		return clone $this;
	}
	
	public function mul($number) {
		$number = "$number";
		$this->value = bcmul($this->value, $number, $this->_getMaxPoint($number) * 2);
		return clone $this;
	}
	
	public function div($number) {
		$number = "$number";
		$this->value = bcdiv($this->value, $number, Number::MAX_POINT_LENGTH);
		return clone $this;
	}
	
	public function comp($number) {
		$number = "$number";
		return bccomp($this->__toString(), $number, $this->_getMaxPoint($number));
	}
	
	public function gt($number) {
		return ($this->comp($number) == 1);
	}
	
	public function lt($number) {
		return ($this->comp($number) == -1);
	}
	
	public function gte($number) {
		return ($this->gt($number) || $this->comp($number) == 0);
	}
	
	public function lte($number) {
		return ($this->lt($number) || $this->comp($number) == 0);
	}
	
	public function eq($number) {
		return ($this->comp($number) == 0);
	}
	
	public function neq($number) {
		return ($this->comp($number) != 0);
	}
	
	public function __toString() {
		if (empty($this->value)) return '0';		
		$this->value = rtrim(rtrim($this->value, '0'),'.');		
		return $this->value;			
	}
}
