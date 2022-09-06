<?php

namespace App\Traits;

use App\Date\DateTime;

trait Updated
{
    private ?DateTime $updatedAt = null;

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt = null): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}