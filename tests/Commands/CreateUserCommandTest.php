<?php

namespace Test\Commands;

use App\Blog\Arguments\Argument;
use App\Exceptions\CommandException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\ArgumentException;
use App\Blog\Commands\CreateUserCommand;
use App\Repositories\UserRepositoryInterface;
use App\User\Entities\User;
use PHPUnit\Framework\TestCase;

class CreateUserCommandTest extends TestCase
{
    public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
        $userRepository = new class implements UserRepositoryInterface
        {
            public function save(User $user): void
            {
            }
            public function get(int $id): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function getByEmail(string $email): User
            {
                return new User("mail@mail.com", "vasia", "pupkin");
            }
        };
        
        $command = new CreateUserCommand($userRepository);
        
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('User already exists: email@mail.com');
        $command->handle(new Argument(['email' => 'email@mail.com']));
    }  

    private function makeUserRepository(): UserRepositoryInterface
    {
        return new class implements UserRepositoryInterface {
            public function save(User $user): void
            {
            }
            public function get(int $id): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function getByEmail(string $email): User
            {
                throw new UserNotFoundException("Not found");
            }
        };
    }

    public function testItRequiresLastName(): void
    {
        $command = new CreateUserCommand($this->makeUserRepository());
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('No such argument: last_name');
        $command->handle(new Argument([
            'email' => 'some@mail.com',
            'first_name' => 'Ivan'
        ]));
    }

    public function testItRequiresFirstName(): void
    {
        $command = new CreateUserCommand($this->makeUserRepository());
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('No such argument: first_name');
        $command->handle(new Argument(['email' => 'some@mail.com']));
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
        
        $command = new CreateUserCommand($userRepository);
        
        $command->handle(new Argument([
            'email' => 'some@mail.com',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
            ]));
        
        $this->assertTrue($userRepository->wasCalled());
    }
}