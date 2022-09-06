<?php

namespace App\Date;

use App\Enums\Date;
use DateTimeImmutable;

class DateTime extends DateTimeImmutable
{
    public function __toString(): string
    {
        return $this->format(Date::DATETIME_FORMAT->value);
    }
}