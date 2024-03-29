<?php

/**
 * Dframe/FileStorage
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/fileStorage/blob/master/LICENSE (MIT)
 */

namespace Dframe\FileStorage;

use Dframe\FileStorage\Drivers\DatabaseDriverInterface;
use Dframe\FileStorage\Exceptions\FileExistException;
use Dframe\FileStorage\Exceptions\FileNotFoundException;
use Exception;
use League\Flysystem\MountManager;

use function json_encode;

/**
 * Storage Class
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Storage
{
    /**
     * @var MountManager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * Storage constructor.
     *
     * @param DatabaseDriverInterface $driver
     * @param null $config
     */
    public function __construct($driver = null, $config = null)
    {
        $this->driver = $driver;
        $adapters = $config['adapters'] ?? [];

        $this->config = $config;
        $this->manager = new MountManager($adapters);
    }


    /**
     * @param $adapter
     * @param $image
     * @param false $default
     *
     * @return Image
     */
    public function image($adapter, $path, $default = false)
    {
        $Image = new Image($this->driver, $this->config);
        $Image->setImage($adapter, $path, $default)->addStylist($this->settings['stylists']);
        return $Image;
    }

    /**
     * @param $settings
     */
    public function settings($settings)
    {
        $this->settings['stylists'] = $settings['stylists'];
    }

    /**
     * @param $file
     *
     * @return bool|string
     */
    public function getFile($file, $storage = 'local')
    {
        $sourceAdapter = $storage . '://' . $file;
        if ($this->manager->has($sourceAdapter) !== true) {
            throw new FileNotFoundException();
        }

        return $file;
    }

    /**
     * @return Drivers\DatabaseDriverInterface|null
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return MountManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param null $key
     *
     * @return array
     */
    public function getConfig($key = null)
    {
        if (!is_null($key)) {
            return $this->config[$key] ?? null;
        }

        return $this->config;
    }

    /**
     * @param $adapter
     * @param $file
     *
     * @return true
     * @throw Exception
     */
    public function drop($adapter, $file)
    {
        $get = $this->driver->get($adapter, $file, true);
        if (empty($get['file_path'])) {
            throw new FileNotFoundException(FileNotFoundException::FILE_NOT_FOUND_MESSAGE);
        }

        /**
         * Get all cache
         */
        if (!empty($get['cache'])) {
            foreach ($get['cache'] as $key => $value) {
                if ($this->manager->has($value['file_cache_adapter'] . '://' . $value['file_cache_path'])) {
                    $this->manager->delete($value['file_cache_adapter'] . '://' . $value['file_cache_path']);
                }
            }
        }

        if ($this->manager->has($adapter . '://' . $get['file_path'])) {
            $this->manager->delete($adapter . '://' . $get['file_path']);
        }

        /**
         * bool|@throw Exception
         */
        return $this->driver->drop($adapter, $file);
    }

    /**
     * @param      $adapter
     * @param      $tmpName
     * @param      $pathImage
     * @param bool $forced
     *
     * @return array
     */
    public function put($adapter, $tmpName, $pathImage, $forced = false)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        if ($this->manager->has($adapter . '://' . $pathImage)) {
            if ($forced === false) {
                throw new FileExistException(json_encode($this->manager->getMetadata($adapter . '://' . $pathImage)));
            }

            $this->manager->delete($adapter . '://' . $pathImage);
        }

        $stream = fopen($tmpName, 'r+');
        if (!$stream) {
            throw new Exception('Failed to open uploaded file');
        }

        $this->manager->writeStream($adapter . '://' . $pathImage, $stream);
        $put = $this->driver->put($adapter, $pathImage, $mime, $stream);
        fclose($stream);

        if ($put['return'] != false) {
            return ['fileId' => $put['lastInsertId']];
        }

        $get = $this->driver->get($adapter, $pathImage);
        return ['fileId' => $get['file_id']];
    }

    /**
     * @param $file
     * @param $extensions
     *
     * @return bool
     */
    public function isAllowedFile($file, array $allowedTypes, array $allowedExtensions)
    {
        /**
         * Get $filename extension
         */
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        /**
         * Get $filename mine
         */
        $mime = $this->getFileMine($file);

        if (!in_array($mime, $allowedTypes) or !in_array($extension, $allowedExtensions)) {
            return false;
        }

        return true;
    }

    /**
     * Get $filename mine
     */
    public function getFileMine($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);  //Walidacja Mine
        finfo_close($finfo);

        return $mime;
    }
}
