<?php

namespace app\controllers;

use app\models\User;

class UsersController extends \lithium\action\Controller
{

    public $publicActions = array('index', 'create');

    public function message($value)
    {
        \lithium\storage\Session::write('message', $value);
    }

    public function index()
    {
        $users = User::findAll();

        return compact('users');
    }

    public function create()
    {
        if ($this->request->data) {
        	$user = new User($this->request->data);

        	if($user->save()) {
        		$this->message('Successfully to create User');
        		$this->redirect('Users::index');
        	} else {
        		$this->message('Failed to create User, please check the error');
        		$errors = $user->getErrors();        		
        	}

        }

        return compact('user', 'errors');
    }

    public function edit()
    {
        if ($this->request->id) {

        	$user = User::get($this->request->id);
        	$user->properties = $this->request->data;

        	if($user->save()) {
        		$this->message('Successfully to update User');
        		$this->redirect('Users::index');
        	} else {
        		$this->message('Failed to update User, please check the error');
        		$errors = $user->getErrors();
        	}

        }

        return compact('user', 'errors');
    }

    public function view()
    {
        if ($this->request->id) {
        	$user = User::get($this->request->id);
        }

        return compact('user');
    }

    public function delete()
    {
        if ($this->request->id) {
        	$user = User::get($this->request->id);
        	$user->delete();
        	$this->message('Success to delete User');
        	$this->redirect('Users::index');
        	return true;
        }

        $this->message('User id cannot be empty');
        $this->redirect($this->request->referer());
        return false;
    }


}

?>