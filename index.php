<?php

/**
 * The directory in which your modules are located.
 *
 * @link http://kohanaframework.org/guide/about.install#modules
 */
$modules = 'modules';

/**
 * The directory in which the Kohana resources are located. The system
 * directory must contain the classes/kohana.php file.
 *
 * @link http://kohanaframework.org/guide/about.install#system
 */
$system = 'system';

/**
 * The default extension of resource files. If you change this, all resources
 * must be renamed to use the new extension.
 *
 * @link http://kohanaframework.org/guide/about.install#ext
 */
define('EXT', '.php');

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 *
 * @link http://www.php.net/manual/errorfunc.configuration#ini.error-reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In a production environment, it is safe to ignore notices and strict warnings.
 * Disable them by using: E_ALL ^ E_NOTICE
 *
 * When using a legacy application with PHP >= 5.3, it is recommended to disable
 * deprecated notices. Disable with: E_ALL & ~E_DEPRECATED
 */
error_reporting(E_ALL | E_STRICT);

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of Kohana internals.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 */

// Set the full path to the docroot
define('APPPATH', dirname(realpath($_SERVER["DOCUMENT_ROOT"])) . DIRECTORY_SEPARATOR);

// Define the absolute paths for configured directories
define('DOCROOT', dirname(APPPATH) . DIRECTORY_SEPARATOR);


define('MODPATH', realpath(dirname(__FILE__) . '/modules') . DIRECTORY_SEPARATOR);

define('SYSPATH', realpath(dirname(__FILE__) . '/system') . DIRECTORY_SEPARATOR);

// Clean up the configuration vars
unset($modules, $system);

/**
 * Define the start time of the application, used for profiling.
 */
if (!defined('KOHANA_START_TIME')) {
	define('KOHANA_START_TIME', microtime(TRUE));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if (!defined('KOHANA_START_MEMORY')) {
	define('KOHANA_START_MEMORY', memory_get_usage());
}

// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH . 'classes/Kohana/Core' . EXT;

if (is_file(APPPATH . 'classes/Kohana' . EXT)) {
	// Application extends the core
	require APPPATH . 'classes/Kohana' . EXT;
}
else {
	// Load empty core extension
	require SYSPATH . 'classes/Kohana' . EXT;
}
 
/**
 * Set the default time zone.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/timezones
 */
date_default_timezone_set('Europe/Kiev');

/**
 * Set the default locale.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/function.setlocale
 */
setlocale(LC_ALL, 'ru_RU.utf8');

/**
 * Enable the Kohana auto-loader.
 *
 * @link http://kohanaframework.org/guide/using.autoloading
 * @link http://www.php.net/manual/function.spl-autoload-register
 */
spl_autoload_register(array('Kohana', 'auto_load'));
 
/**
 * Optionally, you can enable a compatibility auto-loader for use with
 * older modules that have not been updated for PSR-0.
 *
 * It is recommended to not enable this unless absolutely necessary.
 */
//spl_autoload_register(array('Kohana', 'auto_load_lowercase'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @link http://www.php.net/manual/function.spl-autoload-call
 * @link http://www.php.net/manual/var.configuration#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */ 
 
if (isset($_SERVER['KOHANA_ENV'])) {
	Kohana::$environment = constant('Kohana::' . strtoupper($_SERVER['KOHANA_ENV']));
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - integer  cache_life  lifetime, in seconds, of items cached              60
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 * - boolean  expose      set the X-Powered-By header                        FALSE
 */

Kohana::init(
	array(
		'cache_dir'  => DOCROOT . 'cache' . DIRECTORY_SEPARATOR . basename(APPPATH),
		//'base_url'   => '/',
    'errors'=>false,
		'index_file' => FALSE,
		'caching'    => TRUE
	)
); 
 
/**
 * Attach the file write to logging. Multiple writers are supported.
 */

Kohana::$log->attach(new Log_File(DOCROOT . 'logs'));


/**
 * Attach a file reader to config. Multiple readers are supported.
 */
 
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
 
 try {
echo "asd";
} catch (Exception $e) {
    echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
} 
 
Kohana::modules(
	array(
		'base'       => DOCROOT . 'base',
		//'auth'       => MODPATH . 'auth', // Basic authentication
		'cache'      => MODPATH . 'cache', // Caching with multiple backends
		// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
		'database'   => MODPATH . 'database', // Database access
		//'image'      => MODPATH . 'image', // Image manipulation
	    //'minion'     => MODPATH . 'minion', // CLI Tasks
		'orm'        => MODPATH . 'orm', // Object Relationship Mapping
		//'pagination' => MODPATH . 'pagination',
		//'mpdf'       => MODPATH . 'mpdf',
		// 'unittest'   => MODPATH.'unittest',   // Unit testing
		// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation

		//'email'      => MODPATH . 'email',
	)
);


/**
 * Set the default language
 */
//I18n::lang('ru');
I18n::lang('en-us');


// Bootstrap the application
require APPPATH . 'bootstrap' . EXT;

if (PHP_SAPI == 'cli') // Try and load minion
{
	class_exists('Minion_Task') OR die('Please enable the Minion module for CLI support.');
	set_exception_handler(array('Minion_Exception', 'handler'));
	ob_end_clean();
	Minion_Task::factory(Minion_CLI::options())->execute();
}
else {
	$_SERVER["SERVER_NAME"] = $_SERVER["HTTP_HOST"];
	/**
	 * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
	 * If no source is specified, the URI will be automatically detected.
	 */
	try {
		$response = Request::factory(TRUE, array(), FALSE)
			->execute()
			->send_headers(TRUE);
		header('X-Execute:' . (microtime(TRUE) - KOHANA_START_TIME));
		echo $response->body();
	} catch (HTTP_Exception $e) {
		header('X-Exception:' . $e->getMessage(), TRUE, $e->getCode());

	}
}
