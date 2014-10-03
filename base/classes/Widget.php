<?php

/**
 * Created by JetBrains PhpStorm.
 * User: lnkvisitor
 * Date: 16.02.13
 * Time: 2:07
 * To change this template use File | Settings | File Templates.
 */
abstract class Widget {
	/**
	 * @param $name
	 * @param array $params
	 * @return Widget
	 */
	static public function factory($name, array $params = array()) {
		$widget = new ReflectionClass('Widget_' . ucfirst($name));
		return $widget->newInstance($params);
	}

	protected $virtual = FALSE;
	/**
	 * @var array
	 */
	protected $_params;
	/**
	 * Widget name
	 *
	 * @required
	 * @var string
	 */
	protected $name = NULL;
	/**
	 * Widget classes(CSS)
	 *
	 * @var array
	 */
	protected $class = array();

	protected $cache = 300;
	protected $cache_utype = FALSE;

	protected $cacheKey;

	protected $attr = array();

	protected $raw = FALSE;

	protected $timestamp = TRUE;

	protected $content_type;

	protected $cache_control;

	protected $template = NULL;


	/**
	 * @param array $params
	 */
	public function __construct(array $params = array()) {
		$this->_params = $params;
		if ($this->name === NULL) {
			$this->name = strtolower(str_replace('Widget_', '', get_class($this)));
		}
	}

	protected function param($key, $default = NULL) {
		if (is_array($key)) {
			foreach ($key as $k) {
				if (isset($this->_params[$k])) {
					return $this->_params[$k];
				}
			}
			return $default;
		}
		return isset($this->_params[$key]) ? $this->_params[$key] : $default;
	}

	protected function set($key, $value) {
		$this->_params[$key] = $value;
	}

	public function render() {
		if ($this->virtual) {
			if ($this->_render() !== FALSE) {
				$this->_params['widget'] = $this->name;
				return '<!--#include virtual="' . URL::site(Route::get('widgets')->uri($this->_params)) . '" -->';
			}
			return NULL;
		}
		$s = microtime(TRUE);

		$key = get_class($this) . '.' . $this->cacheKey . '.' . I18n::lang();
		$cache = Cache::instance();
		if (($this->cache !== FALSE) && ($data = $cache->get($key))) {
			return $data . ($this->timestamp ? '<!-- ' . $this->name . ' ' . ((microtime(TRUE) - $s) * 1000) . 'ms from cache-->' : '');
		}
		else {
			$b = Profiler::start("Widget", $this->name);

			if (($content = $this->_render()) !== FALSE) {
				if ($this->template) {
					$content = Twig::factory('widget/' . str_replace('_', '/', $this->name), $this->_params)->render();
				}
				Profiler::stop($b);
				if (!$this->raw) {
					$attr = $this->attr + array(
							'widget' => $this->name,
						);
					if (isset($params['container'])) {
						$attr['id'] = $params['container'];
					}
					$class = $this->class;
					if (isset($params['class'])) {
						$class += $params['class'];
					}
					if (!empty($class)) {
						$attr['class'] = implode(' ', $class);
					}
					$content = '<div ' . HTML::attributes($attr) . '>' . $content . '</div>';
				}
				if ($this->cache !== FALSE) {
					$cache->set_with_tags($key, $content, $this->cache, array('widget', 'widget:' . $this->name));
				}
				$t = (microtime(TRUE) - $s) * 1000;
				if ($t > 100) {
					Kohana::$log->add(
						Log::WARNING, 'Widget :name rendering :ms ms uri::uri\n:params', array(
							':name'   => $this->name,
							':ms'     => round($t + 1),
							':uri'    => Request::initial()
									->uri(),
							':params' => json_encode($this->_params)
						)
					);
				}
				return $content . ($this->timestamp ? '<!--' . $this->name . ' ' . $t . 'ms-->' : '');
			}
			Profiler::delete($b);
		}
		return NULL;

	}

	/**
	 * @param Request $request
	 * @param Response $response
	 */
	public function renderEx($request, $response) {
		$s = microtime(TRUE);

		//$key   = get_class($this) . '.' . $this->cacheKey . '.' . I18n::lang();
		//$cache = Cache::instance();
		//if (($this->cache !== FALSE) && ($data = $cache->get($key))) {
//			return $data . ($this->timestamp ? '<!-- ' . $this->name . ' ' . ((microtime(TRUE) - $s) * 1000) . 'ms from cache-->' : '');
//		}
//		else {
		//$b = Profiler::start("Widget", $this->name);


		if ($this->_renderEx($request, $response) !== FALSE) {
			if ($this->template) {
				$response->body(Twig::factory('widget/' . str_replace('_', '/', $this->name), $this->_params)
					->render());
			}
			//Profiler::stop($b);
			if (!$this->raw) {
				$attr = $this->attr + array(
						'widget' => $this->name,
					);
				if (isset($params['container'])) {
					$attr['id'] = $params['container'];
				}
				$class = $this->class;
				if (isset($params['class'])) {
					$class += $params['class'];
				}
				if (!empty($class)) {
					$attr['class'] = implode(' ', $class);
				}
				$response->body('<div ' . HTML::attributes($attr) . '>' . $response->body() . '</div>');
			}
			if (Kohana::$caching && $this->cache !== FALSE && !empty($_SERVER['CACHE_KEY'])) {
				$cache_key = $_SERVER['CACHE_KEY'];
				if (!empty($this->cache_utype)) {
					$cache_key = $cache_key . '|' . Session::instance()->UType();
				}
				PageCache::set($cache_key, $response->body(), $this->cache === TRUE ? 300 : $this->cache);
			}
			$t = (microtime(TRUE) - $s) * 1000;
			if ($t > 100) {
				Kohana::$log->add(
					Log::WARNING,
					'Widget :name rendering :ms ms uri::uri',
					array(
						':name' => $this->name,
						':ms'   => round($t + 1),
						':uri'  => Request::initial()->uri(),
					)
				);
			}
			return TRUE;
			//return $content . ($this->timestamp ? '<!--' . $this->name . ' ' . $t . 'ms-->' : '');
		}
		//Profiler::delete($b);
		return FALSE;
		//
		//}
		//return NULL;
	}

	protected function clear() {
		$cache_key = $_SERVER['CACHE_KEY'];
		if (!empty($this->cache_utype)) {
			$cache_key = $cache_key . '|' . Session::instance()->UType();
		}
		PageCache::set($cache_key, FALSE, 0);
	}

	protected function _render() {


	}

	/**
	 * @param Request $request
	 * @param Response $response
	 */
	protected function _renderEx(Request $request, Response $response) {
	}


	/**
	 * Magic method, returns the output of [View::render].
	 *
	 * @return  string
	 * @uses    View::render
	 */
	public function __toString() {
		try {
			return $this->render();
		} catch (Exception $e) {
			/**
			 * Display the exception message.
			 *
			 * We use this method here because it's impossible to throw and
			 * exception from __toString() .
			 */
			$error_response = Kohana_exception::_handler($e);

			return $error_response->body();
		}
	}

	protected function getKey() {
		return 'nokey';
	}

}