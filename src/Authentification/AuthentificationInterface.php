<?php

namespace App\Authentification;

use App\Blog\Http\Request;
use App\User\Entities\User;

interface AuthentificationInterface
{
    public function user(Request $request): User;
}