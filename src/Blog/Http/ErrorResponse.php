<?php

namespace App\Blog\Http;

class ErrorResponse extends Response
{
    protected const SUCCESS = false;

    public function __construct(
        private string $reason = 'Something went wrong'
    ) {
    }

    // Реализация абстрактного метода родителя
    protected function payload(): array
    {
        return ['reason' => $this->reason];
    }
}