<?php

namespace App\Blog\Commands;

use App\Blog\Arguments\Argument;

interface CreateCommentCommandInterface
{
    public function handle(Argument $argument): void;
}