<?php

namespace Dframe\FileStorage\Exceptions;

use Exception;

class FileToBigException extends Exception
{
    public const FILE_TO_BIG_MESSAGE = 'The uploaded file exceeds the allowed size limit. Allowed file size: %s';
}
