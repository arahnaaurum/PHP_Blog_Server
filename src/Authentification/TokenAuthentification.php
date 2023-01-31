<?php

namespace App\Authentification;

use App\Blog\Http\Request;
use App\Exceptions\AuthException;
use App\Exceptions\AuthTokenNotFoundException;
use App\Repositories\AuthTokenRepositoryInterface;
use App\User\Entities\User;
use DateTimeImmutable;
use App\Exceptions\HttpException;
use InvalidArgumentException;

class TokenAuthentification implements AuthentificationInterface
{
    public const PREFIX = 'Bearer ';

    public function __construct(private AuthTokenRepositoryInterface $authTokenRepository
    ) {
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {
        try {
            $header = $request->header('Authorization');
        } catch (HttpException|InvalidArgumentException $exception)
        {
            throw new AuthException($exception->getMessage());
        }

        if (!str_starts_with($header, self::PREFIX))
        {
            throw new AuthException("Malformed token: [$header]");
        }

        $token = mb_substr($header, strlen(self::PREFIX));

        try {
            $authToken = $this->authTokenRepository->getToken($token);
        } catch (AuthTokenNotFoundException $exception)
        {
            throw new AuthException("Bad token: [$token]");
        }

        if ($authToken->getExpiresOn() <= new DateTimeImmutable())
        {
            throw new AuthException(("Token expired: [$token]"));
        }

        return $authToken->getUser();
    }
}