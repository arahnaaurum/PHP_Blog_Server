<?php

namespace App\Blog\Http\Actions;

use App\Authentification\AuthentificationInterface;
use App\Blog\Http\ErrorResponse;
use App\Exceptions\AuthException;
use App\Exceptions\HttpException;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\SuccessfulResponse;
use App\Repositories\PostRepositoryInterface;
use App\Exceptions\PostNotFoundException;

// для запросов вида DELETE http://127.0.0.1:8000/posts/delete?id=1

class DeletePost implements DeletePostInterface
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private AuthentificationInterface $authentication,
    ) {
    }

    public function handle(Request $request): Response
    {
        //аутентификация юзера, удаляющего пост
        try {
            $user = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $userId = $user->getId();

        try {
            $postId = $request->query('id');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $post = $this->postRepository->get($postId);
        } catch (PostNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        $authorId = $post->getAuthorId();
        if ($userId!=$authorId) {
            return new ErrorResponse('Post may be deleted only by its author');
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