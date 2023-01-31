<?php

namespace  App\Blog\Http\Actions;

use App\Blog\Http\Actions\ActionInterface;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\ErrorResponse;
use App\Blog\Http\SuccessfulResponse;
use App\Date\DateTime;
use App\Exceptions\UserNotFoundException;
use App\Repositories\UserRepositoryInterface;
use App\Exceptions\HttpException;
use Psr\Log\LoggerInterface;

class FindByEmail implements ActionInterface
{
    // Репозиторий пользователей внедряется в контракт в качестве зависимости
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger
    ){
    }
    //имплементируем фунцкию из контракта
    public function handle (Request $request) : Response
    {
        $this->logger->debug('User search handler started at ' . (new DateTime())->format("d.m.y H:i:s"));
        try {
            $email = $request->query('email');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $user = $this->userRepository->getByEmail($email);
        } catch (UserNotFoundException $exception) {
            $this->logger->error("User with email:$email not found");
            return new ErrorResponse($exception->getMessage());
        }

        $this->logger->info("User found with email:$email");
        $this->logger->debug('User search handler finished at ' . (new DateTime())->format("d.m.y H:I:S"));

        return new SuccessfulResponse([
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName()
        ]);
    }
}