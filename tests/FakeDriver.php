<?php

namespace Dframe\FileStorage\Tests;

use Dframe\FileStorage\Drivers\DatabaseDriverInterface;

/**
 * Class FakeDriver
 *
 * @package Dframe\FileStorage\Tests
 */
class FakeDriver implements DatabaseDriverInterface
{
    /**
     * @param      $adapter
     * @param      $path
     * @param bool $cache
     *
     * @return mixed
     */
    public function get($adapter, $path, $cache = false)
    {
        // TODO: Implement get() method.
    }

    /**
     * @param $adapter
     * @param $path
     * @param $mine
     * @param $stream
     *
     * @return mixed
     */
    public function put($adapter, $path, $mine, $stream)
    {
        // TODO: Implement put() method.
    }

    /**
     * @param $adapter
     * @param $originalId
     * @param $path
     * @param $mine
     * @param $stream
     *
     * @return mixed
     */
    public function cache($adapter, $originalId, $path, $mine, $stream)
    {
        // TODO: Implement cache() method.
    }

    /**
     * @param $adapter
     * @param $path
     *
     * @return mixed
     */
    public function drop($adapter, $path)
    {
        // TODO: Implement drop() method.
    }
}