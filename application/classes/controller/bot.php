<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Bot extends Controller {
	public function before() {
                parent::before();

		$authentic= Auth::instance();
                if($authentic->logged_in()) {
                        $this->user = $authentic->get_user();
			//now you have access to user information stored in the database
			View::bind_global('user',$this->user);
                }

	}

	public function action_index() {
		$this->request->redirect('');
	}

	public function action_profile($bot_id) {
		$bot = $this->getBot($bot_id);

		$view = View::factory('bot/index')
			->bind('bot',$bot);

		if(isset($this->user) && isset($_POST['s'])) {
			$posted = Model_Comment::post('bot',
				$bot,$this->user,$_POST['text']);
			$view->bind('posted',$posted);
		}

		$comments =  Model_Comment::fetch('bot',$bot,$this->request->param('page',1));

		$this->request->response = $view
			->bind('comments',$comments);
	}

	private function getBot($bot_id) {
		if(ctype_digit($bot_id)) {
			$bot = ORM::factory('bot',$bot_id);
			if($bot->loaded()) {
				return $bot;
			}
		}

		throw new Kohana_Exception('Unknown User');
	}
}
