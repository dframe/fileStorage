<?php

/**
 *   Uwaga pierwszeństwo nawewnictw jest ważne jeśli
 *   jeśli jest
 *   grupa
 *   grupa/1
 *
 *   to nigdy się nie wykona grupa/1
 *
 *   Przykład poprawnego użycia
 *   grupa/1
 *   grupa
 */

return [
    'https' => false,
    'NAME_CONTROLLER' => 'page',    // Default Controller for router
    'NAME_METHOD' => 'index',       // Default Action for router
    'publicWeb' => '',              // Path for public web (web or public_html)

    'assets' => [
        'minifyCssEnabled' => true,
        'minifyJsEnabled' => true,
        'assetsDir' => 'assets',
        'assetsPath' => APP_DIR . 'View/',
        'cacheDir' => 'cache',
        'cachePath' => APP_DIR . '../web/',
        'cacheUrl' => HTTP_HOST . '/',
    ],

    'routes' => [
        'filestorage/images/:params' => [
            'filestorage/images/[params]',
            'task=page&action=file&image=[params]',
            'params' => '(.*)',
            '_params' => '[value]'
        ],

        'filestorage/file' => [
            'filestorage/file/[params]',
            'task=page&action=file',
            'params' => '(.*)',
            '_params' => [
                '[value]/',
                '[value]'
            ]
        ],

        'error/404' => [
            'error/404',
            'task=page&action=404'
        ],

        'error/500' => [
            'error/500',
            'task=page&action=500'
        ],

        'default' => [
            '[task]/[action]/[params]',
            'task=[task]&action=[action]',
            'params' => '(.*)',
            '_params' => [
                '[name]/[value]/',
                '[name]=[value]'
            ]
        ]
    ]
];
