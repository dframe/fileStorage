<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

$cache = new Filesystem(new Local(__DIR__ . '/../cache'));
$local = new Filesystem(new Local(__DIR__ . '/../uploads'));
$web = new Filesystem(new Local(__DIR__ . '/../'));

return [
    'pluginsDir' => __DIR__ . '/plugins',
    'adapters' => [
        'local' => $local,
        'cache' => $cache,
        'web' => $web
    ],
    'cache' => [
        'adapter' => 'cache',
        'life' => 600 // in seconds
    ],
    'publicUrls' => [
        'local' => ''
    ]
];
