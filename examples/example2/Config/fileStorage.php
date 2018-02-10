<?php
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory as CacheStore;

$localAdapter = new Local(
    dirname(__DIR__).'/uploads', LOCK_EX, Local::DISALLOW_LINKS, [
    'file' => [
        'public' => 0744,
        'private' => 0700,
    ],
    'dir' => [
        'public' => 0755,
        'private' => 0700,
    ]
    ]
);

$webAdapter = new Local(
    dirname(__DIR__).'/', LOCK_EX, Local::DISALLOW_LINKS, [
    'file' => [
        'public' => 0744,
        'private' => 0700,
    ],
    'dir' => [
        'public' => 0755,
        'private' => 0700,
    ]
    ]
);

$cacheAdapter = new Local(
    dirname(__DIR__).'/cache', LOCK_EX, Local::DISALLOW_LINKS, [
    'file' => [
        'public' => 0744,
        'private' => 0700,
    ],
    'dir' => [
        'public' => 0755,
        'private' => 0700,
    ]
    ]
);
        

$cache = new Filesystem($cacheAdapter);
$local = new Filesystem($localAdapter);
$web = new Filesystem($webAdapter);

return array(
    'pluginsDir' => dirname(__DIR__).'/',
    'adapters' => array(
        'local' => $local,
        'cache' => $cache,
        'web' => $web
        )
);
