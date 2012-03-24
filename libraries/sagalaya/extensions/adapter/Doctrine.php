<?php

namespace sagalaya\extensions\adapter;

class Doctrine extends \lithium\security\auth\adapter\Form {
	
	public function check($credentials, array $options = array()) {
		$model = $this->_model;
		$query = $this->_query;
		$data = $this->_filters($credentials->data);
	
		$conditions = $this->_scope + array_diff_key($data, $this->_validators);
		
		$user = $model::$query($conditions);	
		
		if (!$user) {
			return false;
		}
		
		return $this->_validate($user, $data);
	}
	
}