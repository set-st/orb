<?php

class HTTP_Exception_404 extends Kohana_HTTP_Exception_404
{

    public function get_response()
    {
        $view = View::factory('pages/404');

        // Prepare the response object.
        $response = Response::factory();

        // Set the response status
        $response->status(404);

        // Set the response body
        $response->body($view->render());

        //return $response;
    }
}