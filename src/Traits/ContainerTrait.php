<?php
namespace App\Traits;
use App\Container\DIContainer;

trait ContainerTrait
{
    private function getContainer() : DIContainer
    {
        return require __DIR__ . '/../../public/autoload_runtime.php';
    }
}