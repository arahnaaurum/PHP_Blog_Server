<?php

namespace App\Blog\Http;

class SuccessfulResponse extends Response
{
    protected const SUCCESS = true;

    //массив с данными для ответа
    public function __construct(
        private array $data = []
    ) {
    }

    // Реализация абстрактного метода родителя
    protected function payload(): array
    {
        return ['data' => $this->data];
    }
}