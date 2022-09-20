<?php

namespace Test\Container;
use App\Blog\Http\Actions\ActionInterface;
use App\Blog\Http\Actions\FindByEmail;
use App\Connection\ConnectorInterface;
use App\Connection\SqLiteConnector;
use App\Container\DIContainer;
use App\Container\SomeClassWithoutDependencies;
use App\Container\SomeClassWithParameter;
use App\DummyLogger;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Exceptions\ClassNotFoundException;
use PHPUnit\Framework\TestCase;
use PDO;
use Psr\Log\LoggerInterface;

class DIContainerTest extends TestCase
{
    public function testItThrowsAnExceptionIfCannotResolveType(): void
    {
        $container = new DIContainer();
        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage(
            'Cannot resolve type: Test\Container\SomeClass'
        );
    // Пытаемся получить объект несуществующего класса
        $container->get(SomeClass::class);
    }

    public function testItResolvesClassWithoutDependencies(): void
    {
        $container = new DIContainer();
        $object = $container->get(SomeClassWithoutDependencies::class);
        $this->assertInstanceOf(
            SomeClassWithoutDependencies::class,
            $object
        );
    }

    public function testItReturnsPredefinedObject(): void
    {
        $container = new DIContainer();
        $container->bind(
            SomeClassWithParameter::class,
            new SomeClassWithParameter(42)
        );
        $object = $container->get(SomeClassWithParameter::class);
        $this->assertInstanceOf(
            SomeClassWithParameter::class,
            $object
        );

        $this->assertSame(42, $object->value());
    }

    public function testItReturnsPDOConnection(): void
    {
        $container = new DIContainer();
        $container->bind(LoggerInterface::class, new DummyLogger());
        $container->bind(
            PDO::class,
            new PDO (databaseConfig()['sqlite']['DATABASE_URL'])
        );
        $container->bind(ConnectorInterface::class, SqLiteConnector::class);
        $container->bind(UserRepositoryInterface::class, UserRepository::class);
        $container->bind(ActionInterface::class, FindByEmail::class);

        $object = $container->get(ActionInterface::class);
        $this->assertInstanceOf(FindByEmail::class, $object);
    }
}