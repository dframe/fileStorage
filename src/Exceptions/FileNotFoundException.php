<?php

namespace Dframe\FileStorage\Exceptions;

use Exception;

class FileNotFoundException extends Exception
{
    public const FILE_NOT_FOUND_MESSAGE = 'File not found.';
}
