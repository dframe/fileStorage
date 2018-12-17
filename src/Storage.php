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
     * @var Router
     */
    protected $router;

    /**
     * @var null
     */
    protected $driver;

    /**
     * @var
     */
    protected $settings;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * Storage constructor.
     *
     * @param \Dframe\FileStorage\Drivers\DatabaseDriverInterface $driver
     * @param null                                                $config
     * @param bool                                                $router
     */
    public function __construct($driver = null, $config = null, $router = true)
    {
        $this->driver = $driver;

        if (is_null($config)) {
            $configFileStorage = Config::load('fileStorage');
            $adapters = $configFileStorage->get('adapters', []);
        } else {
            $adapters = $config['adapters'];
        }

        $this->config = $config;
        $this->manager = new MountManager($adapters);
        if ($router === true) {
            $this->router = new Router();
        }
    }

    /**
     * @param      $image
     * @param bool $default
     *
     * @return Image
     */
    public function image($image, $default = false)
    {
        $Image = new Image($this->driver, $this->config);
        $Image->setImage($image, $default)->addStylist($this->settings['stylists']);
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
    public function getFile($file)
    {
        $sourceAdapter = 'local://' . $file;
        if ($this->manager->has($sourceAdapter)) {

            // Retrieve a read-stream
            $stream = $this->manager->readStream($sourceAdapter);
            $contents = stream_get_contents($stream);
            fclose($stream);
        } else {
            return false;
        }

        return $this->router->makeUrl('filestorage/file') . '?file=' . $file;
    }

    /**
     * @param        $file
     * @param string $adapter
     *
     * @return Response
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
     * @return Drivers\DatabaseDriverInterface|null
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
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
     * @return array
     */
    public function drop($adapter, $file)
    {
        $get = $this->driver->get($adapter, $file, true);
        if ($get['return'] !== true) {
            return ['return' => false, 'response' => 'Brak pliku'];
        }

        /**
         * Get all cache
         */
        if (!empty($get['cache'])) {
            foreach ($get['cache'] as $key => $value) {
                if ($this->manager->has($adapter . '://' . $value['file_cache_path'])) {
                    $this->manager->delete($adapter . '://' . $value['file_cache_path']);
                }
            }
        }

        if ($this->manager->has($adapter . '://' . $get['file_path'])) {
            $this->manager->delete($adapter . '://' . $get['file_path']);
        }

        $drop = $this->driver->drop($adapter, $file);
        if ($drop['return'] !== true) {
            return ['return' => false, 'response' => $drop['response']];
        }

        return ['return' => true, 'response' => 'Pomyślnie usunięto'];
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
        try {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            if ($this->manager->has($adapter . '://' . $pathImage)) {
                if ($forced == false) {
                    throw new \Exception('File already Exist');
                }

                $this->manager->delete($adapter . '://' . $pathImage);
            }

            $stream = fopen($tmpName, 'r+');
            if (!$stream) {
                throw new \Exception('Failed to open uploaded file');
            }

            $this->manager->writeStream($adapter . '://' . $pathImage, $stream);
            $put = $this->driver->put($adapter, $pathImage, $mime, $stream);
            fclose($stream);
        } catch (\Exception $e) {
            return ['return' => false, 'response' => $e->getMessage()];
        }

        if ($put['return'] != false) {
            return ['return' => true, 'fileId' => $put['lastInsertId']];
        }

        $get = $this->driver->get($adapter, $pathImage);
        return ['return' => true, 'fileId' => $get['file_id']];
    }

    /**
     * @param array $filename
     * @param array $ext
     *
     * @return bool
     */
    public function isAllowedFileType($file, $extensions)
    {

        /**
         * Get $filename extension
         */
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        /**
         * Get $filename mine
         */
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);  //Walidacja Mine
        finfo_close($finfo);

        if (isset($extensions[$extension])) {

            if (!is_array($extensions[$extension])) {
                $extensions[$extension] = [$extensions[$extension]];
            }

            if (in_array($mime, $extensions[$extension])) {
                return true;
            }

        }

        return false;
    }
}
