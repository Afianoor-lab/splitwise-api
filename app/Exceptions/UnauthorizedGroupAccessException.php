<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedGroupAccessException extends Exception
{
    protected $code = 403;
}