<?php

/**
 * Dframe/FileStorage
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/fileStorage/blob/master/LICENSE (MIT)
 */

namespace Dframe\FileStorage;

use Dframe\Config;
use Dframe\Router;
use Dframe\Router\Response;
use League\Flysystem\MountManager;

/**
 * Image Class
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Image
{
    /**
     * @var string
     */
    public $stylist = 'original';

    /**
     * @var array
     */
    public $stylists = [
        'original' => \Dframe\FileStorage\Stylist\SimpleStylist::class
    ];

    /**
     * @var
     */
    public $size;

    /**
     * @var bool
     */
    protected $defaultImage;

    /**
     * @var
     */
    protected $storage;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var mixed|null
     */
    protected $cache;

    /**
     * @var MountManager
     */
    protected $manager;

    /**
     * Image constructor.
     *
     * @param                             $config
     * @param                             $image
     * @param bool                        $default
     * @param \Dframe\FileStorage\Storage $storage
     */
    public function __construct($config, $image, $default = false, $storage)
    {
        if (is_null($config)) {
            $configFileStorage = Config::load('fileStorage');
            $adapters = $configFileStorage->get('adapters', []);
            $cache = $configFileStorage->get('cache', ['life' => 600]);
        } else {
            $adapters = $config['adapters'];
            $cache = $config['cache'] ?? ['life' => 600];
        }

        $this->cache = $cache;
        $this->manager = new MountManager($adapters);
        $this->router = new Router();
        $this->orginalImage = $image;
        $this->defaultImage = $default;
        $this->storage = $storage;
    }

    /**
     * @param bool $stylist
     *
     * @return $this
     */
    public function stylist($stylist = false)
    {
        $this->stylist = $stylist;

        return $this;
    }

    /**
     * @param $size
     *
     * @return $this
     */
    public function size($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @param string $adapter
     *
     * @return string
     */
    public function display($adapter = 'local')
    {
        $get = $this->cache($adapter, $this->orginalImage);

        return $this->router->makeUrl('filestorage/images/:params?params=' . $get['cache']);
    }

    /**
     * @param      $adapter
     * @param      $originalImage
     * @param bool $default
     *
     * @return mixed
     */
    public function cache($adapter, $originalImage, $default = false)
    {
        $output = [];
        $output['stylist'] = $this->stylist;
        $output['size'] = $this->size;

        /**
         * Get extension
         */
        $ext = pathinfo($originalImage, PATHINFO_EXTENSION);

        $stylist = $output['stylist'];

        if (isset($output['size']) and !empty($output['size'])) {
            $stylist .= '-' . $this->size;
        }

        /**
         * Create Static cache path based on $originalImage
         */
        $cachePath = [];
        $cachePath[0] = substr(md5($originalImage), 0, 6);
        $cachePath[1] = substr(md5($originalImage), 6, 6);
        $cachePath[2] = substr(md5($stylist . '+' . $originalImage), 0, 6);
        $cachePath[3] = $stylist;

        $basename = basename($originalImage, '.' . $ext);
        if (!empty($basename)) {
            $basename = $basename . '-';
        }
        $cache = $basename . $cachePath[0] . '-' . $cachePath[1] . '-' . $cachePath[2] . '-' . $cachePath[3] . '.' . $ext;

        $cacheAdapter = 'cache://' . $cache;
        $sourceAdapter = $adapter . '://' . $originalImage;

        $has = $this->manager->has($cacheAdapter);
        if ($has == false or ($has == true and $this->manager->getTimestamp($cacheAdapter) < strtotime("-" . $this->cache['life'] . " seconds"))) {
            if ($has == true) { // zrobić update zamiast delete
                $this->manager->delete($cacheAdapter);
            }

            if ($this->manager->has($sourceAdapter)) {
                $mimetype = $this->manager->getMimetype($sourceAdapter);

                $readStream = $this->manager->readStream($sourceAdapter);

                if (!empty($output)) {
                    $getStylist = $this->getStylist($output['stylist']);
                    $readStream = $getStylist->stylize($readStream, null, $getStylist, $output);
                }

                if (!empty($this->storage)) {
                    if (!empty($this->storage->getDriver())) {
                        $this->storage->getDriver()
                            ->cache($adapter, $originalImage, $cache, $mimetype, $readStream);
                    }
                    $this->manager->putStream($cacheAdapter, $readStream);
                } else {
                    return false;
                }
            } elseif (!empty($this->defaultImage)) {
                if (!empty($this->storage->getDriver())) {
                    $get = $this->storage->getDriver()
                        ->get($adapter, $originalImage, true);
                    if ($get['return'] == true) {
                        foreach ($get['cache'] as $key => $value) {
                            if ($this->manager->has('cache://' . $value['file_cache_path'])) {
                                $this->manager->delete('cache://' . $value['file_cache_path']);
                            }
                        }
                        //$this->storage->getDriver()->drop($originalImage);
                    }
                }

                if ($default == false) {
                    return $this->cache($adapter, $this->defaultImage, true); //zwracać bład
                }
            }
        }

        $this->cache = $cache;

        return [
            'cache' => $cache
        ];
    }

    /**
     * Zwraca obiekt stylisty o wskazanej nazwie
     * Tylko do uzytku wewnatrz klasy!
     *
     * @param  string $stylist
     *
     * @return \Dframe\FileStorage\Stylist
     */
    protected function getStylist($stylist = 'orginal')
    {
        $className = $this->stylists[$stylist];
        if (!class_exists($className) or !method_exists($className, 'stylize')) {
            throw new \Exception('Requested stylist "' . $stylist . '" was not found or is incorrect');
        }

        return new $className();
    }

    /**
     * @param string $adapter
     *
     * @return array
     */
    public function get($adapter = 'local', $data = false)
    {
        $data = $this->cache($adapter, $this->orginalImage);
        
        if (!empty($this->storage->getDriver()) and $data === true) {
            $get = $this->storage->getDriver()
                ->get($adapter, $this->orginalImage, $data['cache']);
            if ($get['return'] === true) {
                $data['data'] = $get['cache'];
            }
        }

        return $data;
    }

    /**
     * @param        $file
     * @param string $adapter
     *
     * @return mixed
     */
    public function renderFile($file, $adapter = 'local')
    {
        $fileAdapter = $adapter . '://' . $file;
        // Retrieve a read-stream
        if (!$this->manager->has($fileAdapter)) {
            $body = "<h1>404 Not Found</h1> \n\r" . "The page that you have requested could not be found.";

            return Response::render($body)
                ->status(404);
        }

        $getMimetype = $this->manager->getMimetype($fileAdapter);
        $stream = $this->manager->readStream($fileAdapter);
        $contents = stream_get_contents($stream);
        fclose($stream);

        return Response::render($contents)
            ->headers(['Content-type' => $getMimetype]);
    }

    /**
     * @param $stylists
     */
    public function addStylist($stylists)
    {
        $this->stylists = array_merge($this->stylists, $stylists);
    }
}
