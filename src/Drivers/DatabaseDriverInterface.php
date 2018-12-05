<?php

/**
 * Dframe/FileStorage
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/fileStorage/blob/master/LICENSE (MIT)
 */

namespace Dframe\FileStorage\Drivers;

/**
 * A contract to identify an implementation to store permissions to
 *
 * Drivers can both be persistent or static depending on their implementation.
 * A default, static ArrayDriver implementation comes with this package.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
interface DatabaseDriverInterface
{
    /**
     * @param      $adapter
     * @param      $path
     * @param bool $cache
     *
     * @return mixed
     */
    public function get($adapter, $path, $cache = false);

    /**
     * @param $adapter
     * @param $path
     * @param $mine
     * @param $stream
     *
     * @return mixed
     */
    public function put($adapter, $path, $mine, $stream);

    /**
     * @param $adapter
     * @param $originalId
     * @param $path
     * @param $mine
     * @param $stream
     *
     * @return mixed
     */
    public function cache($adapter, $originalId, $path, $mine, $stream);

    /**
     * @param $adapter
     * @param $path
     *
     * @return mixed
     */
    public function drop($adapter, $path);
}
