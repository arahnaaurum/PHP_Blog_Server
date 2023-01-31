<?php

namespace App\Blog\Http\Actions;

use App\Blog\Http\Actions\ActionInterface;
use App\Blog\Http\ErrorResponse;
use App\Exceptions\HttpException;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\SuccessfulResponse;
use App\Repositories\PostRepositoryInterface;
use App\Exceptions\PostNotFoundException;


class DeletePost implements ActionInterface
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            $postId = $request->query('id');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $this->postRepository->delete($postId);
        } catch (PostNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

     // Возвращаем успешный ответ
     return new SuccessfulResponse([
            'message' => "post with $postId is successfully deleted",
        ]);
    }
}