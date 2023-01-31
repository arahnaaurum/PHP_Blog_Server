<?php

namespace App\Blog\Http\Actions;

use App\Blog\Arguments\Argument;
use App\Authentification\AuthentificationInterface;
use App\Blog\Commands\CreateUserCommandInterface;
use App\Exceptions\UserNotFoundException;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\SuccessfulResponse;
use App\Blog\Http\ErrorResponse;
use App\Repositories\UserRepositoryInterface;
use Exception;
use Psr\Log\LoggerInterface;


class UserCreateAction implements UserCreateActionInterface
{
    public function __construct(
        private CreateUserCommandInterface $createUserCommand,
        private AuthentificationInterface $identification,
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger
    ) {
    }

    public function handle(Request $request): Response
    {
        $email = $request->jsonBodyField('email');
        try {
            $argument = new Argument([
                'email' => $request->jsonBodyField('email'),
                'first_name' => $request->jsonBodyField('first_name'),
                'last_name' => $request->jsonBodyField('last_name'),
                'password' => $request->jsonBodyField('password'),
                'author' => $this->identification->user($request)
            ]);

            $this->createUserCommand->handle($argument);
        } catch (Exception $exception)
        {
            $this->logger->error($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $user = $this->userRepository->getByEmail($email);
        } catch (UserNotFoundException $exception)
        {
            $this->logger->error($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        $this->logger->info('User created : '. $user->getId());

        return new SuccessfulResponse(
            [
                'email' => $user->getEmail(),
                'name' => $user->getFirstName() . ' ' . $user->getLastName()
            ]
        );
    }
}