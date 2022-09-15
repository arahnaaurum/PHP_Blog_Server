<?php

namespace App\Exceptions;

use Exception;

class PostLikeAlreadyExistsException extends Exception
{
    protected $message = 'Like from this user to this post already exists';
}