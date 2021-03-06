<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Register extends Controller {
	public function before() {
                parent::before();

		$this->authentic = Auth::instance();
                if($this->authentic->logged_in()) {
                        $this->user = $this->authentic->get_user();
			//now you have access to user information stored in the database
			View::bind_global('user',$this->user);
                }

	}

	public function action_index() {
		if('POST' == $_SERVER['REQUEST_METHOD']) {
			$user = ORM::factory('user');
			$result = $user->validate($_POST);
			if($result===true) {
				Auth::instance()->login(Arr::get($_POST,'email'),
					Arr::get($_POST,'password')
				);
				Request::instance()->redirect('user');
			}
			else $errors = $result;
		}
		$view = View::factory('register/index');

		if(isset($errors)) {
			$view->bind('errors',$errors);
		}
		$this->request->response = $view;
	}

	public function action_verify() {
		$get = new Validate($_GET);
		$get->rules('id', array(
			'not_empty'	=> array(),
			'numeric'	=> array(),
		));
		$get->rules('key', array(
			'not_empty'	=> array(),
			'alpha_numeric'	=> array()
		));
		if($get->check()) {
			$user = ORM::factory('user')
				->where('id','=',$get['id'])
				->where('activation_key','=',$get['key'])
				->find();
			if($user->loaded()) {
				$user->activation_key = null;
				$user->activation_expire = null;
				$user->email_verified = 'True';
				$user->save();
				$this->authentic->force_login($user);
				$this->request->redirect('settings');
			}
		}
		throw new Kohana_404_Exception('Bad Request');
	}
}
