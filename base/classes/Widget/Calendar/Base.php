<?php defined('SYSPATH') or die('No direct script access.');

class Widget_Calendar_Base extends Widget {
    protected $virtual = false;
    protected $raw = TRUE;

    protected function _render() {
        $this->set('a', date('Y'));
        $this->set('b', date('n'));

        return 'asd';
    }

    /*
    protected function _renderEx(Request $request, Response $response) {
        $response->body($this->calendar($request->param('a'), $request->param('b')));
    }
    */

}