<?php

namespace App\Authentification;

use App\Blog\Http\Request;
use App\Exceptions\AuthException;
use App\Exceptions\HttpException;
use App\Repositories\UserRepositoryInterface;
use App\User\Entities\User;
use InvalidArgumentException;

class JsonBodyAuthentification implements AuthentificationInterface
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {
        try {
            $email = $request->jsonBodyField('auth_user');
            return $this->userRepository->getByEmail($email);
        }catch (HttpException|InvalidArgumentException $exception)
        {
            throw new AuthException($exception->getMessage());
        }
    }
}