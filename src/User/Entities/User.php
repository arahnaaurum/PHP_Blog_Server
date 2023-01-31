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

    public function __toString()
    {
        return
            $this->email. ' '.
            $this->firstName. ' '.
            $this->lastName .
            ' (на сайте с ' . $this->createdAt->format('Y-m-d') . ')';
    }
}