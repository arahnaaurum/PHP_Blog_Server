<?php

namespace  App\Blog\Http\Actions;

use App\Blog\Http\Actions\ActionInterface;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\ErrorResponse;
use App\Blog\Http\SuccessfulResponse;
use App\Exceptions\PostNotFoundException;
use App\Repositories\PostRepositoryInterface;
use App\Exceptions\HttpException;

class FindPostById implements ActionInterface
{
    // Репозиторий пользователей внедряется в контракт в качестве зависимости
    public function __construct(
        private PostRepositoryInterface $postRepository
    ){
    }
    //имплементируем фунцкию из контракта
    public function handle (Request $request) : Response
    {
        try {
            $id = $request->query('id');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $post = $this->postRepository->get($id);
        } catch (PostNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        return new SuccessfulResponse([
            'title' => $post->getTitle(),
            'text' => $post->getText()
        ]);
    }
}