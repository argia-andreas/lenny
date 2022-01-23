<?php

namespace App\Exceptions;

use Exception;

class InvalidAuthException extends Exception
{
    public static function create()
    {
        return new self('No Linear token, run `lenny install`.');
    }
}
