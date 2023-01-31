<?php

namespace App\Blog\Commands;

use App\Blog\Arguments\Argument;

interface CreatePostCommandInterface
{
    public function handle(Argument $argument): void;
}