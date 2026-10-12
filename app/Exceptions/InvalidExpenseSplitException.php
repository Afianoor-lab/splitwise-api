<?php

namespace App\Exceptions;

use Exception;

class InvalidExpenseSplitException extends Exception
{
    protected $code = 422;
}