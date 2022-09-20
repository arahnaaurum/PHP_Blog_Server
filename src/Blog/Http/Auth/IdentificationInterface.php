<?php

namespace App\Blog\Http\Auth;

use App\Blog\Http\Request;
use App\User\Entities\User;

interface IdentificationInterface
{
    public function user(Request $request): User;
}