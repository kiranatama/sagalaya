<?php

namespace sagalaya\extensions\adapter;

class Doctrine extends \lithium\security\auth\adapter\Form {
	
	/**
	 * Called by the `Auth` class to run an authentication check against a model class using the
	 * credientials in a data container (a `Request` object), and returns an array of user
	 * information on success, or `false` on failure.
	 *
	 * @param object $credentials A data container which wraps the authentication credentials used
	 *               to query the model (usually a `Request` object). See the documentation for this
	 *               class for further details.
	 * @param array $options Additional configuration options. Not currently implemented in this
	 *              adapter.
	 * @return array Returns an array containing user information on success, or `false` on failure.
	 */
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
	
	/**
	 * After an authentication query against the configured model class has occurred, this method
	 * iterates over the configured validators and checks each one by passing the submitted form
	 * value as the first parameter, and the corresponding database value as the second. The
	 * validator then returns a boolean to indicate success. If the validator fails, it will cause
	 * the entire authentication operation to fail. Note that any filters applied to a form field
	 * will affect the data passed to the validator.
	 *
	 * @see lithium\security\auth\adapter\Form::__construct()
	 * @see lithium\security\auth\adapter\Form::$_validators
	 * @param object $user The user object returned from the database. This object must support a
	 *               `data()` method, which returns the object's array representation, and
	 *               also returns individual field values by name.
	 * @param array $data The form data submitted in the request and passed to `Form::check()`.
	 * @return array Returns an array of authenticated user data on success, otherwise `false` if
	 *               any of the configured validators fails. See `'validators'` in the `$config`
	 *               parameter of `__construct()`.
	 */
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
			throw new UnexpectedValueException("Authentication validator is not callable.");
		}
		return call_user_func($this->_validators[0], $data, $user) ? $user : false;
	}
	
}