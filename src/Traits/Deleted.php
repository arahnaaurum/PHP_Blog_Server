<?php

namespace App\Traits;

use App\Date\DateTime;

trait Deleted
{
    private ?DateTime $deletedAt = null;

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTime $deletedAt = null): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function isDeleted(): bool
    {
        return !empty($this->deletedAt);
    }
}