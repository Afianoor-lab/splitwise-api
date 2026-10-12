<?php

namespace App\Exceptions;

use Exception;

class InvalidSettlementException extends Exception
{
    protected $code = 422;
}