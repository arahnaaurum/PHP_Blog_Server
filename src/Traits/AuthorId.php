<?php

namespace App\Traits;

trait AuthorId
{
    private ?int $authorId = null;

    public function getAuthorId(): ?int
    {
        return $this->authorId;
    }

    public function setAuthorId(?int $authorId): self
    {
        $this->authorId = $authorId;

        return $this;
    }
}