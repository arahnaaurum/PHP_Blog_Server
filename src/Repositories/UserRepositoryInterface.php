<?php

namespace App\Repositories;

use App\User\Entities\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;
    public function get(int $id): User;
}