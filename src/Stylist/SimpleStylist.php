<?php

/**
 * Dframe/FileStorage
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/fileStorage/blob/master/LICENSE (MIT)
 */

namespace Dframe\FileStorage\Stylist;

use Dframe\FileStorage\Stylist;
use Exception;

/**
 * Prosty stylista
 * Zwraca obrazek taki jakim jest
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class SimpleStylist extends Stylist
{
    /**
     * @param resource $readStream
     * @param string $extension
     * @param bool $stylistObj
     * @param bool $stylistParam
     *
     * @return bool|resource
     * @throws Exception
     */
    public function stylize($readStream, $extension, $stylistObj = false, $stylistParam = false)
    {
        return $readStream;
    }

    /**
     * @param $stylistParam
     *
     * @return string
     */
    public function identify($stylistParam)
    {
        return 'simpleStylist';
    }
}
