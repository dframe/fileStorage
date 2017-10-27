<?php
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory as CacheStore;


$localAdapter = new Local(
    dirname(__DIR__).'/../app/View/uploads', LOCK_EX, Local::DISALLOW_LINKS, [
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
    dirname(__DIR__).'/../web', LOCK_EX, Local::DISALLOW_LINKS, [
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
    dirname(__DIR__).'/../app/View/cache', LOCK_EX, Local::DISALLOW_LINKS, [
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
        
// Create the cache store
$cacheStore = new CacheStore();
// Decorate the adapter
$adapter = new CachedAdapter($cacheAdapter, $cacheStore);
// And use that to create the file system
$cacheFilesystem = new Filesystem($adapter);


$local = new Filesystem($localAdapter);
$web = new Filesystem($webAdapter);

return array(
    'pluginsDir' => dirname(__DIR__).'/'
    'adapters' => array(
        'local' => $local,
        'cache' => $cacheFilesystem,
        'web' => $web
        )
);
