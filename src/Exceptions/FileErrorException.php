<?php

namespace Dframe\FileStorage\Exceptions;

use Exception;

class FileErrorException extends Exception
{
    public const ERROR_CRITICAL_MESSAGE = 'There was some error with adding file. Contact with support team.';
}
