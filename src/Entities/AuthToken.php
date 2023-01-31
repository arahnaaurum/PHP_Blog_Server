<?php
namespace App\Entities;
use App\User\Entities\User;
use DateTimeInterface;

class AuthToken
{
    public function __construct(
        private string $token,
        private User $user,
        private DateTimeInterface $expiresOn
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getExpiresOn(): DateTimeInterface
    {
        return $this->expiresOn;
    }

}