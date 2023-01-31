<?php

namespace App\Blog\Commands;

use App\Blog\Arguments\Argument;

interface CreateUserCommandInterface
{
    public function handle(Argument $argument): void;
}