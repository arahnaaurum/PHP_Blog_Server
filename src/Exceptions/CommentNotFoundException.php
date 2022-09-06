<?php

namespace App\Exceptions;

use Exception;

class CommentNotFoundException extends Exception
{
    protected $message = 'Comment not found';
}