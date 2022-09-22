<?php

namespace App\Repositories;

use App\Entities\AuthToken;

interface AuthTokenRepositoryInterface
{
    public function save(AuthToken $authToken): void;
    public function getToken(string $token): AuthToken;
}