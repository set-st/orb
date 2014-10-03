<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller_Template {

	public function action_index()
	{
        //test view
        $article = View::factory('content/article');
        $article->content = 'qwe qwe qwe qwe';

        $this->title = 'Приветствие';
        $this->content = $article->render();
	}

} // End Welcome
