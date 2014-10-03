<?php

Session::$default = 'redis';
Cache::$default = 'redis';

$cdn = Kohana::$config->load('cdn');
$publ = Kohana::$config->load('public');
define('CDN', $cdn->get('hostname'));
define('PUBL', $publ->get('hostname'));

if (!empty($_SERVER['HTTP_HOST'])) {
    define('SITE', explode('.', $_SERVER['HTTP_HOST'])[0]);
}

Route::set('widgets', 'widgets/<widget>(/<a>(/<b>(/<c>)))',
    array(
        'a' => '[^/?\n]++',
        'b' => '[^/?\n]++',
        'c' => '[^/?\n]++'
    )
)->defaults(
        array(
            'controller' => 'Widget',
        )
    );