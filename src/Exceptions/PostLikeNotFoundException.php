<?php

namespace App\Exceptions;

use Exception;

class PostLikeNotFoundException extends Exception
{
    protected $message = 'Like not found';
}