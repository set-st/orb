<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
    'default' => array
    (
        'type'       => 'MySQLi',
        'connection' => array(
            'hostname'   => 'localhost',
            'database'   => 'carserv',
            'username'   => 'root',
            'password'   => 'carserv2365',
            'persistent' => FALSE,
        ),
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => FALSE,
    )
);
