<?php

namespace App\Blog\Http\Actions;

use App\Authentification\AuthentificationInterface;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\ErrorResponse;
use App\Blog\Http\SuccessfulResponse;
use App\Entities\AuthToken;
use App\Exceptions\AuthException;
use App\Exceptions\AuthTokenNotFoundException;
use App\Exceptions\AuthTokenRepositoryException;
use App\Exceptions\HttpException;
use App\Repositories\AuthTokenRepositoryInterface;
use DateTimeImmutable;
use InvalidArgumentException;

class LogoutAction implements LogoutActionInterface
{
    public const PREFIX = 'Bearer ';
    public function __construct (
        private AuthTokenRepositoryInterface $authTokenRepository
    ){
    }

    public function handle(Request $request): Response
    {
//        получаем токен авторизованного юзера из заголовка запроса
        try {
            $header = $request->header('Authorization');
        } catch (HttpException|InvalidArgumentException $exception) {
            throw new AuthException($exception->getMessage());
        }

        if (!str_starts_with($header, self::PREFIX))
        {
            throw new AuthException("Malformed token: [$header]");
        }

        $token = mb_substr($header, strlen(self::PREFIX));

        // смена времени жизни токена
        try {
            $this->authTokenRepository->changeTokenExpirationDate($token);
        } catch (AuthTokenNotFoundException $exception) {
            throw new AuthException($exception->getMessage());
        }

        return new SuccessfulResponse([
            'message' => 'you are logged out',
        ]);
    }
}