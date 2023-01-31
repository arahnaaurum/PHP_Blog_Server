<?php

namespace Test\Commands;

use App\Blog\Arguments\Argument;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\ArgumentException;
use App\Console\CreateUser;
use App\Repositories\UserRepositoryInterface;
use App\User\Entities\User;
use PHPUnit\Framework\TestCase;
use App\DummyLogger;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use App\Traits\ContainerTrait;

class CreateUserSymphonyConsoleTest extends TestCase
{
    use ContainerTrait;

    public function testItRequiresFirstName(): void
    {
        $command = $this->getContainer()->get(CreateUser::class);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "password, first_name, last_name").'
        );
        $command->run(
            new ArrayInput(['email' => 'some@mail.com']),
            new NullOutput());
    }

    public function testItRequiresLastName(): void
    {
        $command = $this->getContainer()->get(CreateUser::class);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "password, last_name").'
        );
        $command->run(
            new ArrayInput([
            'email' => 'some@mail.com',
            'first_name' => 'Ivan'
            ]),
            new NullOutput());
    }

    public function testItRequiresPassword(): void
    {
        $command = $this->getContainer()->get(CreateUser::class);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "password").'
        );
        $command->run(
            new ArrayInput([
            'email' => 'some@mail.com',
            'first_name' => 'Ivan',
            'last_name' => 'Ivanov'
            ]),
            new NullOutput()
        );
    }

    // Тест, проверяющий, что команда сохраняет пользователя в репозитории, вызывая метод save()
    public function testItSavesUserToRepository(): void
    {
        $userRepository = new class implements UserRepositoryInterface  //мок-объект
        {
            private bool $called = false;
            public function save(User $user): void
            {
                $this->called = true; // Если метод save был вызван, меняем на true
            }
            public function get(int $id): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function getByEmail(string $email): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function wasCalled(): bool
            {
                return $this->called;
            }
        };
        
        $command = new CreateUser($userRepository);

        $command->run(
            new ArrayInput([
            'email' => 'some@mail.com',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
            'password' => '123'
            ]),
            new NullOutput()
        );
        
        $this->assertTrue($userRepository->wasCalled());
    }
}