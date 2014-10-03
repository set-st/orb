<?php defined('SYSPATH') or die('No direct script access.');
return array
(
	'redis' => array(
		'driver'         => 'redis',
		'default_expire' => 3600,
		'server'         => array(
			'host'       => '127.0.0.1', // Redis Server
			'port'       => 6379, // Redis port number
			'persistent' => FALSE, // Persistent connection
			'timeout'    => 1,
		),
	)
);