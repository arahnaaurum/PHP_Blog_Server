<?php

namespace App\Blog\Http\Auth;
use App\Blog\Http\ErrorResponse;
use App\Blog\Http\Request;
use App\Exceptions\ArgumentException;
use App\Exceptions\AuthException;
use App\Exceptions\HttpException;
use App\Exceptions\UserNotFoundException;
use App\Repositories\UserRepositoryInterface;
use App\User\Entities\User;

class UserIdIdentification implements IdentificationInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function user(Request $request): User
    {
        try {
            $userId = ($request->jsonBodyField('user_id'));
        } catch (HttpException|ArgumentException $exception) {
            throw new AuthException($exception->getMessage());
        }

        try {
            return $this->userRepository->get($userId);
        } catch (UserNotFoundException $exception) {
            throw new AuthException($exception->getMessage());
        }
    }
}