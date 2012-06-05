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

	protected function _validate($user, array $data) {
		foreach ($this->_validators as $field => $validator) {
			if (!isset($this->_fields[$field]) || $field === 0) {
				continue;
			}

			if (!is_callable($validator)) {
				$message = "Authentication validator for `{$field}` is not callable.";
				throw new UnexpectedValueException($message);
			}

			$field = $this->_fields[$field];
			$value = isset($data[$field]) ? $data[$field] : null;
			if (!call_user_func($validator, $value, $user->$field)) {
				return false;
			}
		}

		if (!isset($this->_validators[0])) {
			return $user;
		}
		if (!is_callable($this->_validators[0])) {
			throw new UnexpectedValueException(
					"Authentication validator is not callable.");
		}
		return call_user_func($this->_validators[0], $data, $user) ? $user
				: false;
	}

}
