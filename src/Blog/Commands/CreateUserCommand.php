<?php

namespace App\Blog\Commands;

use App\User\Entities\User;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Blog\Arguments\Argument;
use App\Exceptions\CommandException;
use App\Exceptions\UserNotFoundException;

final class CreateUserCommand
{
    public function __construct(
        private UserRepositoryInterface $userRepository)
    {
    }

    public function handle(Argument $arguments) : void
    {
        $email = $arguments->get('email');
        if ($this->userExists($email))
        {
            throw new CommandException("User already exists: $email");
        }
        $this->userRepository->save(new User
            (
                $arguments->get('email'),
                $arguments->get('first_name'),
                $arguments->get('last_name')
            )
        ); //передали значения полей для юзера
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