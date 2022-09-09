<?php

namespace App\Exceptions;

use Exception;

class CommandException extends Exception
{
    protected $message = 'Comand failed';
}