<?php

namespace App\User\Entities;

use App\Date\DateTime;
use App\Traits\Active;
use App\Traits\Created;
use App\Traits\Deleted;
use App\Traits\Updated;
use App\Traits\Id;

class User
{
    use Id;
    use Active;
    use Created;
    use Updated;
    use Deleted;

    public function __construct(
        private string $email,
        private string $firstName,
        private string $lastName,
        private ?string $hashedPassword,
        private ?User $author = null
    ) {
        $this->createdAt = new DateTime();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->hashedPassword;
    }

    private static function hash(string $password, string $email): string
    {
        return hash('sha256', $password . $email);
    }

    public function checkPassword(string $password): bool
    {
        return $this->hashedPassword === self::hash($password, $this->email);
    }

    public static function createFrom (
        string $email,
        string $first_name,
        string $last_name,
        string $password,
        User $author,
    ) : self
    {
        return new self(
            $email,
            $first_name,
            $last_name,
            self::hash($password, $email),
            $author
        );
    }

    public function __toString()
    {
        return
            $this->email. ' '.
            $this->firstName. ' '.
            $this->lastName .
            ' (на сайте с ' . $this->createdAt->format('Y-m-d') . ')';
    }
}