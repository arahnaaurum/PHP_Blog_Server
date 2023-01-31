<?php

namespace App\Blog\Http;

use App\Exceptions\HttpException;
use JsonException;

class Request
{
    public function __construct(
    // суперглобальная переменная $_GET
        private array $get,
    // суперглобальная переменная $_SERVER
        private array $server,
    // Cвойство для хранения тела запроса
        private string $body
    ) {
    }

    // Метод для получения метода запроса

    public function method(): string
    {
    // В $_SERVER HTTP-метод хранится под ключом REQUEST_METHOD
        if (!array_key_exists('REQUEST_METHOD', $this->server)) {
            throw new HttpException('Cannot get method from the request');
        }
        return $this->server['REQUEST_METHOD'];
    }

    // Метод для получения пути запроса
    public function path(): string
    {
        // В $_SERVER значение URI хранится под ключом REQUEST_URI
        if (!array_key_exists('REQUEST_URI', $this->server)) {
            throw new HttpException('Cannot get path from the request');
        }

        // Используем встроенную в PHP функцию parse_url
        $components = parse_url($this->server['REQUEST_URI']);
        if (!is_array($components) || !array_key_exists('path', $components)) {
            throw new HttpException('Cannot get path from the request');
        }
        return $components['path'];
    }

    // Метод для получения значения определённого параметра строки запроса
    public function query(string $param): string
    {
        if (!array_key_exists($param, $this->get)) {
            throw new HttpException(
                "No such query param in the request: $param"
            );
        }
        $value = trim($this->get[$param]);
        if (empty($value)) {
            throw new HttpException(
                "Empty query param in the request: $param"
            );
        }
        return $value;
    }

    // Метод для получения значения определённого заголовка
    public function header(string $header): string
    {
    // В $_SERVER имена заголовков имеют префикс 'HTTP_', а знаки подчёркивания заменены на минусы
        $headerName = strtoupper("http_". str_replace('-', '_', $header));
        if (!array_key_exists($headerName, $this->server)) {
            throw new HttpException("No such header in the request: $header");
        }
        $value = trim($this->server[$headerName]);
        if (empty($value)) {
            throw new HttpException("Empty header in the request: $header");
        }
        return $value;
    }

    // Метод для получения массива, сформированного из json-форматированного тела запроса
    public function jsonBody(): array {
        // Пытаемся декодировать json
        try {
            $data = json_decode(
                $this->body,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw new HttpException('Cannot decode json body');
        }

        if (!is_array($data)) {
            throw new HttpException("Not an array/object in json body");
        }

        return $data;
    }

    // Метод для получения отдельного поля из json-форматированного тела запроса
    public function jsonBodyField(string $field): mixed
    {
        $data = $this->jsonBody();
        if(!array_key_exists($field, $data)) {
            throw new HttpException("No such field: $field");
        }

        if (empty($data[$field])) {
            throw new HttpException("Empty field: $field");
        }

        return $data[$field];
    }
}