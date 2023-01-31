<?php

namespace  App\Blog\Http\Actions;

use App\Blog\Http\Actions\ActionInterface;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\ErrorResponse;
use App\Blog\Http\SuccessfulResponse;
use App\Exceptions\UserNotFoundException;
use App\Repositories\UserRepositoryInterface;
use App\Exceptions\HttpException;

class FindByEmail implements ActionInterface
{
    // Репозиторий пользователей внедряется в контракт в качестве зависимости
    public function __construct(
        private UserRepositoryInterface $userRepository
    ){
    }
    //имплементируем фунцкию из контракта
    public function handle (Request $request) : Response
    {
        try {
            $email = $request->query('email');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $user = $this->userRepository->getByEmail($email);
        } catch (UserNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        return new SuccessfulResponse([
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName()
        ]);
    }
}