<?php

namespace App\Blog\Commands;

use App\User\Entities\User;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Blog\Arguments\Argument;
use App\Exceptions\CommandException;
use App\Exceptions\UserNotFoundException;

use Psr\Log\LoggerInterface;

final class CreateUserCommand
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger)
    {
    }

    public function handle(Argument $arguments) : void
    {
        // Логируем информацию о том, что команда запущена
        $this->logger->info("Create user command started");
        $email = $arguments->get('email');
        if ($this->userExists($email))
        {
            $this->logger->warning("User already exists: $email");
            return;;
        }
        //передали значения полей для юзера
        $this->userRepository->save(new User
            (
                $arguments->get('email'),
                $arguments->get('first_name'),
                $arguments->get('last_name')
            )
        );

        //Логируем информацию о новом пользователе
        $this->logger->info("User created: $email");
    }
    
    private function userExists (string $email) : bool
    {
        try
        {
            $this->userRepository->getByEmail($email);//по эмейлу
        }
        catch (UserNotFoundException) {
            return false;
        }
        return true;
    }
}