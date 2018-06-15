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

    public function get($adapter, $path);

    public function put($adapter, $path, $mine, $stream);

    public function cache($adapter, $orginalId, $path, $mine, $stream);

    public function drop($adapter, $path);

}
