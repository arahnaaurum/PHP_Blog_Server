<?php

namespace App\Exceptions;

use Exception;

class CommentLikeAlreadyExistsException extends Exception
{
    protected $message = 'Like from this user to this comment already exists';
}