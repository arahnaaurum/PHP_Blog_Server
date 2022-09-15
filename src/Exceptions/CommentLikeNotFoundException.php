<?php

namespace App\Exceptions;

use Exception;

class CommentLikeNotFoundException extends Exception
{
    protected $message = 'Like not found';
}