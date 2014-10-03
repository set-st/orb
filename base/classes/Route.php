<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lnkvisitor
 * Date: 15.02.13
 * Time: 2:10
 * To change this template use File | Settings | File Templates.
 */

class Route extends Kohana_Route {
	/**
	 * Generates a URI for the current route based on the parameters given.
	 *
	 *     // Using the "default" route: "users/profile/10"
	 *     $route->uri(array(
	 *         'controller' => 'users',
	 *         'action'     => 'profile',
	 *         'id'         => '10'
	 *     ));
	 *
	 * @param   array $params URI parameters
	 *
	 * @return  string
	 * @throws  Kohana_Exception
	 * @uses    Route::REGEX_Key
	 */
	public function uri(array $params = NULL) {
		$lang = !empty($params['lang']) ? $params['lang'] : I18n::lang();
		if ((!isset($this->_defaults['lang']) || $this->_defaults['lang'] !== FALSE) && $lang == 'uk') {
			$uri        = $this->_uri;
			$this->_uri = 'ua/' . $uri;
			$result     = parent::uri($params);
			$this->_uri = $uri;
		}
		else {
			$result = parent::uri($params);
		}
		return $result;
	}
}