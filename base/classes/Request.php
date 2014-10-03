<?php defined('SYSPATH') OR die('No direct script access.');

class Request extends Kohana_Request {
	/**
	 * Creates a new request object for the given URI. New requests should be
	 * created using the [Request::instance] or [Request::factory] methods.
	 *
	 *     $request = new Request($uri);
	 *
	 * If $cache parameter is set, the response for the request will attempt to
	 * be retrieved from the cache.
	 *
	 * @param   string $uri              URI of the request
	 * @param   array $client_params    Array of params to pass to the request client
	 * @param   bool $allow_external   Allow external requests? (deprecated in 3.3)
	 * @param   array $injected_routes  An array of routes to use, for testing
	 *
	 * @return  void
	 * @throws  Request_Exception
	 * @uses    Route::all
	 * @uses    Route::matches
	 */
	public function __construct($uri, $client_params = array(), $allow_external = TRUE, $injected_routes = array()) {
		if (preg_match('#^/ua(/.*)?$#', $uri, $m)) {
			$uri = empty($m[1]) ? '/' : $m[1];
			I18n::lang('uk');
		}
        if (preg_match('#^/ru(/.*)?$#', $uri, $m)) {
            $uri = empty($m[1]) ? '/' : $m[1];
            I18n::lang('ru');
        }
		parent::__construct($uri, $client_params, $allow_external, $injected_routes);
	}
}
