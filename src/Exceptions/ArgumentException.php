<?php

namespace App\Exceptions;

use Exception;

class ArgumentException extends Exception
{
    protected $message = 'No such argument';
}