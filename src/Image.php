<?php

/**
 * Dframe/FileStorage
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/fileStorage/blob/master/LICENSE (MIT)
 */

namespace Dframe\FileStorage;

use Dframe\FileStorage\Stylist\SimpleStylist;
use Exception;
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
        'original' => SimpleStylist::class
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
     * @var Storage
     */
    protected $storage;

    /**
     * @var mixed|null
     */
    protected $cache;

    /**
     * @var MountManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $originalImage;

    /**
     * @var string
     */
    protected $adapter;

    /**
     * Image constructor.
     *
     * @param                             $driver
     * @param                             $config
     */
    public function __construct($driver, $config)
    {
        $this->config = $config;
        $adapters = $config['adapters'];
        $cache = $config['cache'] ?? ['life' => 600];

        $this->cache = $cache;
        $this->manager = new MountManager($adapters);
        $this->driver = $driver;
    }

    /**
     * @param      $image
     * @param bool $default
     *
     * @return $this
     */
    public function setImage($adapter, $image, $default = false)
    {
        $this->adapter = $adapter;
        $this->originalImage = $image;
        $this->defaultImage = $default;

        return $this;
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
        return $this->cache($adapter, $this->originalImage);
    }

    protected function createCachePath($originalImage, $output = [])
    {
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
        $cache = $basename . '-' . $cachePath[0] . '-' . $cachePath[1] . '-' . $cachePath[2] . '-' . $cachePath[3] . '.' . $ext;

        return str_replace($basename, rtrim($originalImage, '.' . $ext), $cache);
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

        $cache = $this->createCachePath($originalImage, $output);

        $cacheAdapter = 'cache://' . $cache;
        $sourceAdapter = $adapter . '://' . $originalImage;

        $has = $this->manager->has($cacheAdapter);
        if ($has == false
            or ($has == true and $this->manager->getTimestamp($cacheAdapter) < strtotime(
                "-" . $this->cache['life'] . " seconds"
            ))) {
            /** @todo: Rewrite to update */
            if ($has == true) {
                $this->manager->delete($cacheAdapter);
            }

            if ($this->manager->has($sourceAdapter)) {
                $mimetype = $this->manager->getMimetype($sourceAdapter);
                $readStream = $this->manager->readStream($sourceAdapter);

                if (!empty($output)) {
                    $getStylist = $this->getStylist($output['stylist']);
                    $readStream = $getStylist->stylize($readStream, null, $getStylist, $output);
                }

                if (!empty($this->driver)) {
                    $this->driver->cache($adapter, $originalImage, $cache, $mimetype, $readStream);
                }

                $this->manager->putStream($cacheAdapter, $readStream);
            } elseif (!empty($this->defaultImage)) {
                if (!empty($this->driver)) {
                    $get = $this->driver->get($adapter, $originalImage, true);
                    if ($get['return'] == true) {
                        foreach ($get['cache'] as $key => $value) {
                            if ($this->manager->has('cache://' . $value['file_cache_path'])) {
                                $this->manager->delete('cache://' . $value['file_cache_path']);
                            }
                        }
                        //$this->driver->drop($originalImage);
                    }
                }

                if ($default == false) {
                    return $this->cache($adapter, $this->defaultImage, true); //zwracać bład
                }
            }
        }

        $this->cache = $cache;

        return [
            'default' => $default,
            'cache' => $cache
        ];
    }

    /**
     * Zwraca obiekt stylisty o wskazanej nazwie
     * Tylko do uzytku wewnatrz klasy!
     *
     * @param string $stylist
     *
     * @return Stylist
     */
    protected function getStylist($stylist = 'orginal')
    {
        $className = $this->stylists[$stylist];
        if (!class_exists($className) or !method_exists($className, 'stylize')) {
            throw new Exception('Requested stylist "' . $stylist . '" was not found or is incorrect');
        }

        return new $className();
    }

    /**
     * @param string $adapter
     * @param bool   $data
     *
     * @return array
     */
    public function get($data = false)
    {
        $data = $this->cache($this->adapter, $this->originalImage);

        if (!empty($this->driver) and $data === true) {
            $get = $this->driver->get($this->adapter, $this->originalImage, $data['cache']);
            if ($get['return'] === true) {
                $data['data'] = $get['cache'];
            }
        }

        return $data;
    }

    public function getUrl()
    {
        $cache = $this->cache($this->adapter, $this->originalImage);
        return $this->config['publicUrls']['cache'] . $cache['cache'];
    }

    /**
     * @param $stylists
     */
    public function addStylist($stylists)
    {
        $this->stylists = array_merge($this->stylists, $stylists);
    }
}
