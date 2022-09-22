<?php

namespace App\Blog\Http\Actions;

use App\Authentification\PasswordAuthentificationInterface;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\ErrorResponse;
use App\Blog\Http\SuccessfulResponse;
use App\Entities\AuthToken;
use App\Exceptions\AuthException;
use App\Repositories\AuthTokenRepositoryInterface;
use DateTimeImmutable;

class LoginAction implements LoginActionInterface
{
    public function __construct (
        private PasswordAuthentificationInterface $passwordAuthentication,
        private AuthTokenRepositoryInterface      $authTokenRepository
    ){
    }

    public function handle(Request $request): Response
    {
        try {
            $user = $this->passwordAuthentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $authToken = new AuthToken(
            bin2hex(random_bytes(40)),
            $user,
            (new DateTimeImmutable())->modify('+1 day')
        );

        $this->authTokenRepository->save($authToken);

        return new SuccessfulResponse([
            'token' => $authToken->getToken(),
        ]);
    }
}