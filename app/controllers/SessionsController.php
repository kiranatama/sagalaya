<?php

namespace app\controllers;

use lithium\security\Auth;
use lithium\storage\Session;

class SessionsController extends \lithium\action\Controller {
	
	public $publicActions = array('add', 'delete');
	public function message($value) { 
		\lithium\storage\Session::write('message', $value);
	}	
	
	public function add() {		
		if ($this->request->data) {			
			$logged = Auth::check('default', $this->request);
			
			if ($logged) {
				$this->message('You are successfully login.');
				$this->redirect('Users::index');
			} else {
				$this->message('Your username/password is not valid, please try again!');
				$this->redirect('Sessions::add');
			}			
		}		
		
	}
	
	public function delete() {
		Auth::clear('default');
		return $this->redirect('/');
	}
}

?>