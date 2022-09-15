<?php

namespace App\Container;

use App\Exceptions\ClassNotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class DIContainer implements ContainerInterface
{
    private array $resolvers = [];

    public function bind(string $abstract, mixed $concrete) : void
    {
        $this->resolvers[$abstract] = $concrete;
    }

    public function get(string $abstract): object
    {
        if (array_key_exists($abstract, $this->resolvers)) {
            $concrete = $this->resolvers[$abstract];
            //если по ключу - предопределенный объект
            if (is_object($concrete)) {
                return $concrete;
            }
            return $this->get($concrete);
        }

        if (!class_exists($abstract)) {
            throw new ClassNotFoundException("Cannot resolve type: $abstract");
        }

        $reflectionClass = new ReflectionClass($abstract);
        $constructor = $reflectionClass->getConstructor();

        if (null === $constructor) {
            return new $abstract();
        }

        $parameters = [];

        foreach ($constructor->getParameters() as $parameter) {
            $parameterClass = $parameter->getType()->getName();
            $parameters[] = $this->get($parameterClass);
        }

        return new $abstract(...$parameters);
    }
    public function has(string $id): bool
    {
        return $this->resolvers[$id] ?? false;
    }

}