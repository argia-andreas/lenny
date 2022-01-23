<?php

namespace App\Exceptions;

use Exception;

class MissingCommandsException extends Exception
{
    public static function create()
    {
        return new self('Lenny needs git and gh command line commands installed');
    }
}
