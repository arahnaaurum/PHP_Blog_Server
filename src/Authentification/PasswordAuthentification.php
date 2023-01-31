<?php

namespace App\Authentification;

use App\Blog\Http\Request;
use App\User\Entities\User;
use App\Repositories\UserRepositoryInterface;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\HttpException;
use App\Exceptions\AuthException;

class PasswordAuthentification implements PasswordAuthentificationInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function user(Request $request): User
    {
        // проверка по емэйлу
        try {
            $email = $request->jsonBodyField('auth_email');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }
        try {
            $user = $this->userRepository->getByEmail($email);
        } catch (UserNotFoundException $e) {
            throw new AuthException($e->getMessage());
        }

        // проверка по паролю
        try {
            $password = $request->jsonBodyField('auth_password');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }

        //  хэширование и проверка пароля инкапсулированы в классе User

        if (!$user->checkPassword($password)) {
            throw new AuthException('Wrong password');
        }

        // Пользователь аутентифицирован
        return $user;
    }
}